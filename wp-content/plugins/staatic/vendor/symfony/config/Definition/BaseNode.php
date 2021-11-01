<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition;

use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\Exception;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\UnsetKeyException;
abstract class BaseNode implements NodeInterface
{
    const DEFAULT_PATH_SEPARATOR = '.';
    private static $placeholderUniquePrefixes = [];
    private static $placeholders = [];
    protected $name;
    protected $parent;
    protected $normalizationClosures = [];
    protected $finalValidationClosures = [];
    protected $allowOverwrite = \true;
    protected $required = \false;
    protected $deprecation = [];
    protected $equivalentValues = [];
    protected $attributes = [];
    protected $pathSeparator;
    private $handlingPlaceholder;
    /**
     * @param string|null $name
     */
    public function __construct($name, NodeInterface $parent = null, string $pathSeparator = self::DEFAULT_PATH_SEPARATOR)
    {
        if (\false !== \strpos($name = (string) $name, $pathSeparator)) {
            throw new \InvalidArgumentException('The name must not contain ".' . $pathSeparator . '".');
        }
        $this->name = $name;
        $this->parent = $parent;
        $this->pathSeparator = $pathSeparator;
    }
    /**
     * @param string $placeholder
     * @param mixed[] $values
     * @return void
     */
    public static function setPlaceholder($placeholder, $values)
    {
        if (!$values) {
            throw new \InvalidArgumentException('At least one value must be provided.');
        }
        self::$placeholders[$placeholder] = $values;
    }
    /**
     * @param string $prefix
     * @return void
     */
    public static function setPlaceholderUniquePrefix($prefix)
    {
        self::$placeholderUniquePrefixes[] = $prefix;
    }
    /**
     * @return void
     */
    public static function resetPlaceholders()
    {
        self::$placeholderUniquePrefixes = [];
        self::$placeholders = [];
    }
    /**
     * @param string $key
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }
    /**
     * @param string $key
     */
    public function getAttribute($key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
    /**
     * @param string $key
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    /**
     * @param mixed[] $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
    /**
     * @param string $key
     */
    public function removeAttribute($key)
    {
        unset($this->attributes[$key]);
    }
    /**
     * @param string $info
     */
    public function setInfo($info)
    {
        $this->setAttribute('info', $info);
    }
    public function getInfo()
    {
        return $this->getAttribute('info');
    }
    public function setExample($example)
    {
        $this->setAttribute('example', $example);
    }
    public function getExample()
    {
        return $this->getAttribute('example');
    }
    public function addEquivalentValue($originalValue, $equivalentValue)
    {
        $this->equivalentValues[] = [$originalValue, $equivalentValue];
    }
    /**
     * @param bool $boolean
     */
    public function setRequired($boolean)
    {
        $this->required = $boolean;
    }
    /**
     * @param string|null $package
     */
    public function setDeprecated($package)
    {
        $args = \func_get_args();
        if (\func_num_args() < 2) {
            trigger_deprecation('symfony/config', '5.1', 'The signature of method "%s()" requires 3 arguments: "string $package, string $version, string $message", not defining them is deprecated.', __METHOD__);
            if (!isset($args[0])) {
                trigger_deprecation('symfony/config', '5.1', 'Passing a null message to un-deprecate a node is deprecated.');
                $this->deprecation = [];
                return;
            }
            $message = (string) $args[0];
            $package = $version = '';
        } else {
            $package = (string) $args[0];
            $version = (string) $args[1];
            $message = (string) ($args[2] ?? 'The child node "%node%" at path "%path%" is deprecated.');
        }
        $this->deprecation = ['package' => $package, 'version' => $version, 'message' => $message];
    }
    /**
     * @param bool $allow
     */
    public function setAllowOverwrite($allow)
    {
        $this->allowOverwrite = $allow;
    }
    /**
     * @param mixed[] $closures
     */
    public function setNormalizationClosures($closures)
    {
        $this->normalizationClosures = $closures;
    }
    /**
     * @param mixed[] $closures
     */
    public function setFinalValidationClosures($closures)
    {
        $this->finalValidationClosures = $closures;
    }
    public function isRequired()
    {
        return $this->required;
    }
    public function isDeprecated()
    {
        return (bool) $this->deprecation;
    }
    /**
     * @param string $node
     * @param string $path
     */
    public function getDeprecationMessage($node, $path)
    {
        trigger_deprecation('symfony/config', '5.1', 'The "%s()" method is deprecated, use "getDeprecation()" instead.', __METHOD__);
        return $this->getDeprecation($node, $path)['message'];
    }
    /**
     * @param string $node
     * @param string $path
     */
    public function getDeprecation($node, $path) : array
    {
        return ['package' => $this->deprecation['package'] ?? '', 'version' => $this->deprecation['version'] ?? '', 'message' => \strtr($this->deprecation['message'] ?? '', ['%node%' => $node, '%path%' => $path])];
    }
    public function getName()
    {
        return $this->name;
    }
    public function getPath()
    {
        if (null !== $this->parent) {
            return $this->parent->getPath() . $this->pathSeparator . $this->name;
        }
        return $this->name;
    }
    public final function merge($leftSide, $rightSide)
    {
        if (!$this->allowOverwrite) {
            throw new ForbiddenOverwriteException(\sprintf('Configuration path "%s" cannot be overwritten. You have to define all options for this path, and any of its sub-paths in one configuration section.', $this->getPath()));
        }
        if ($leftSide !== ($leftPlaceholders = self::resolvePlaceholderValue($leftSide))) {
            foreach ($leftPlaceholders as $leftPlaceholder) {
                $this->handlingPlaceholder = $leftSide;
                try {
                    $this->merge($leftPlaceholder, $rightSide);
                } finally {
                    $this->handlingPlaceholder = null;
                }
            }
            return $rightSide;
        }
        if ($rightSide !== ($rightPlaceholders = self::resolvePlaceholderValue($rightSide))) {
            foreach ($rightPlaceholders as $rightPlaceholder) {
                $this->handlingPlaceholder = $rightSide;
                try {
                    $this->merge($leftSide, $rightPlaceholder);
                } finally {
                    $this->handlingPlaceholder = null;
                }
            }
            return $rightSide;
        }
        $this->doValidateType($leftSide);
        $this->doValidateType($rightSide);
        return $this->mergeValues($leftSide, $rightSide);
    }
    public final function normalize($value)
    {
        $value = $this->preNormalize($value);
        foreach ($this->normalizationClosures as $closure) {
            $value = $closure($value);
        }
        if ($value !== ($placeholders = self::resolvePlaceholderValue($value))) {
            foreach ($placeholders as $placeholder) {
                $this->handlingPlaceholder = $value;
                try {
                    $this->normalize($placeholder);
                } finally {
                    $this->handlingPlaceholder = null;
                }
            }
            return $value;
        }
        foreach ($this->equivalentValues as $data) {
            if ($data[0] === $value) {
                $value = $data[1];
            }
        }
        $this->doValidateType($value);
        return $this->normalizeValue($value);
    }
    protected function preNormalize($value)
    {
        return $value;
    }
    public function getParent()
    {
        return $this->parent;
    }
    public final function finalize($value)
    {
        if ($value !== ($placeholders = self::resolvePlaceholderValue($value))) {
            foreach ($placeholders as $placeholder) {
                $this->handlingPlaceholder = $value;
                try {
                    $this->finalize($placeholder);
                } finally {
                    $this->handlingPlaceholder = null;
                }
            }
            return $value;
        }
        $this->doValidateType($value);
        $value = $this->finalizeValue($value);
        foreach ($this->finalValidationClosures as $closure) {
            try {
                $value = $closure($value);
            } catch (Exception $e) {
                if ($e instanceof UnsetKeyException && null !== $this->handlingPlaceholder) {
                    continue;
                }
                throw $e;
            } catch (\Exception $e) {
                throw new InvalidConfigurationException(\sprintf('Invalid configuration for path "%s": ', $this->getPath()) . $e->getMessage(), $e->getCode(), $e);
            }
        }
        return $value;
    }
    protected abstract function validateType($value);
    protected abstract function normalizeValue($value);
    protected abstract function mergeValues($leftSide, $rightSide);
    protected abstract function finalizeValue($value);
    protected function allowPlaceholders() : bool
    {
        return \true;
    }
    protected function isHandlingPlaceholder() : bool
    {
        return null !== $this->handlingPlaceholder;
    }
    protected function getValidPlaceholderTypes() : array
    {
        return [];
    }
    private static function resolvePlaceholderValue($value)
    {
        if (\is_string($value)) {
            if (isset(self::$placeholders[$value])) {
                return self::$placeholders[$value];
            }
            foreach (self::$placeholderUniquePrefixes as $placeholderUniquePrefix) {
                if (0 === \strpos($value, $placeholderUniquePrefix)) {
                    return [];
                }
            }
        }
        return $value;
    }
    /**
     * @return void
     */
    private function doValidateType($value)
    {
        if (null !== $this->handlingPlaceholder && !$this->allowPlaceholders()) {
            $e = new InvalidTypeException(\sprintf('A dynamic value is not compatible with a "%s" node type at path "%s".', static::class, $this->getPath()));
            $e->setPath($this->getPath());
            throw $e;
        }
        if (null === $this->handlingPlaceholder || null === $value) {
            $this->validateType($value);
            return;
        }
        $knownTypes = \array_keys(self::$placeholders[$this->handlingPlaceholder]);
        $validTypes = $this->getValidPlaceholderTypes();
        if ($validTypes && \array_diff($knownTypes, $validTypes)) {
            $e = new InvalidTypeException(\sprintf('Invalid type for path "%s". Expected %s, but got %s.', $this->getPath(), 1 === \count($validTypes) ? '"' . \reset($validTypes) . '"' : 'one of "' . \implode('", "', $validTypes) . '"', 1 === \count($knownTypes) ? '"' . \reset($knownTypes) . '"' : 'one of "' . \implode('", "', $knownTypes) . '"'));
            if ($hint = $this->getInfo()) {
                $e->addHint($hint);
            }
            $e->setPath($this->getPath());
            throw $e;
        }
        $this->validateType($value);
    }
}
