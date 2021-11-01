<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition;

use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\Exception;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\UnsetKeyException;
class PrototypedArrayNode extends ArrayNode
{
    protected $prototype;
    protected $keyAttribute;
    protected $removeKeyAttribute = \false;
    protected $minNumberOfElements = 0;
    protected $defaultValue = [];
    protected $defaultChildren;
    private $valuePrototypes = [];
    /**
     * @param int $number
     */
    public function setMinNumberOfElements($number)
    {
        $this->minNumberOfElements = $number;
    }
    /**
     * @param string $attribute
     * @param bool $remove
     */
    public function setKeyAttribute($attribute, $remove = \true)
    {
        $this->keyAttribute = $attribute;
        $this->removeKeyAttribute = $remove;
    }
    public function getKeyAttribute()
    {
        return $this->keyAttribute;
    }
    /**
     * @param mixed[] $value
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }
    public function hasDefaultValue()
    {
        return \true;
    }
    public function setAddChildrenIfNoneSet($children = ['defaults'])
    {
        if (null === $children) {
            $this->defaultChildren = ['defaults'];
        } else {
            $this->defaultChildren = \is_int($children) && $children > 0 ? \range(1, $children) : (array) $children;
        }
    }
    public function getDefaultValue()
    {
        if (null !== $this->defaultChildren) {
            $default = $this->prototype->hasDefaultValue() ? $this->prototype->getDefaultValue() : [];
            $defaults = [];
            foreach (\array_values($this->defaultChildren) as $i => $name) {
                $defaults[null === $this->keyAttribute ? $i : $name] = $default;
            }
            return $defaults;
        }
        return $this->defaultValue;
    }
    /**
     * @param PrototypeNodeInterface $node
     */
    public function setPrototype($node)
    {
        $this->prototype = $node;
    }
    public function getPrototype()
    {
        return $this->prototype;
    }
    /**
     * @param NodeInterface $node
     */
    public function addChild($node)
    {
        throw new Exception('A prototyped array node can not have concrete children.');
    }
    protected function finalizeValue($value)
    {
        if (\false === $value) {
            throw new UnsetKeyException(\sprintf('Unsetting key for path "%s", value: %s.', $this->getPath(), \json_encode($value)));
        }
        foreach ($value as $k => $v) {
            $prototype = $this->getPrototypeForChild($k);
            try {
                $value[$k] = $prototype->finalize($v);
            } catch (UnsetKeyException $e) {
                unset($value[$k]);
            }
        }
        if (\count($value) < $this->minNumberOfElements) {
            $ex = new InvalidConfigurationException(\sprintf('The path "%s" should have at least %d element(s) defined.', $this->getPath(), $this->minNumberOfElements));
            $ex->setPath($this->getPath());
            throw $ex;
        }
        return $value;
    }
    protected function normalizeValue($value)
    {
        if (\false === $value) {
            return $value;
        }
        $value = $this->remapXml($value);
        $isList = array_is_list($value);
        $normalized = [];
        foreach ($value as $k => $v) {
            if (null !== $this->keyAttribute && \is_array($v)) {
                if (!isset($v[$this->keyAttribute]) && \is_int($k) && $isList) {
                    $ex = new InvalidConfigurationException(\sprintf('The attribute "%s" must be set for path "%s".', $this->keyAttribute, $this->getPath()));
                    $ex->setPath($this->getPath());
                    throw $ex;
                } elseif (isset($v[$this->keyAttribute])) {
                    $k = $v[$this->keyAttribute];
                    if (\is_float($k)) {
                        $k = \var_export($k, \true);
                    }
                    if ($this->removeKeyAttribute) {
                        unset($v[$this->keyAttribute]);
                    }
                    if (\array_keys($v) === ['value']) {
                        $v = $v['value'];
                        if ($this->prototype instanceof ArrayNode && ($children = $this->prototype->getChildren()) && \array_key_exists('value', $children)) {
                            $valuePrototype = \current($this->valuePrototypes) ?: clone $children['value'];
                            $valuePrototype->parent = $this;
                            $originalClosures = $this->prototype->normalizationClosures;
                            if (\is_array($originalClosures)) {
                                $valuePrototypeClosures = $valuePrototype->normalizationClosures;
                                $valuePrototype->normalizationClosures = \is_array($valuePrototypeClosures) ? \array_merge($originalClosures, $valuePrototypeClosures) : $originalClosures;
                            }
                            $this->valuePrototypes[$k] = $valuePrototype;
                        }
                    }
                }
                if (\array_key_exists($k, $normalized)) {
                    $ex = new DuplicateKeyException(\sprintf('Duplicate key "%s" for path "%s".', $k, $this->getPath()));
                    $ex->setPath($this->getPath());
                    throw $ex;
                }
            }
            $prototype = $this->getPrototypeForChild($k);
            if (null !== $this->keyAttribute || !$isList) {
                $normalized[$k] = $prototype->normalize($v);
            } else {
                $normalized[] = $prototype->normalize($v);
            }
        }
        return $normalized;
    }
    protected function mergeValues($leftSide, $rightSide)
    {
        if (\false === $rightSide) {
            return \false;
        }
        if (\false === $leftSide || !$this->performDeepMerging) {
            return $rightSide;
        }
        $isList = array_is_list($rightSide);
        foreach ($rightSide as $k => $v) {
            if (null === $this->keyAttribute && $isList) {
                $leftSide[] = $v;
                continue;
            }
            if (!\array_key_exists($k, $leftSide)) {
                if (!$this->allowNewKeys) {
                    $ex = new InvalidConfigurationException(\sprintf('You are not allowed to define new elements for path "%s". Please define all elements for this path in one config file.', $this->getPath()));
                    $ex->setPath($this->getPath());
                    throw $ex;
                }
                $leftSide[$k] = $v;
                continue;
            }
            $prototype = $this->getPrototypeForChild($k);
            $leftSide[$k] = $prototype->merge($leftSide[$k], $v);
        }
        return $leftSide;
    }
    private function getPrototypeForChild(string $key)
    {
        $prototype = $this->valuePrototypes[$key] ?? $this->prototype;
        $prototype->setName($key);
        return $prototype;
    }
}
