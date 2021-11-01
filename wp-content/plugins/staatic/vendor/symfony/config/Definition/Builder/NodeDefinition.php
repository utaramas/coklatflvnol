<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition\Builder;

use Staatic\Vendor\Symfony\Component\Config\Definition\BaseNode;
use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Staatic\Vendor\Symfony\Component\Config\Definition\NodeInterface;
abstract class NodeDefinition implements NodeParentInterface
{
    protected $name;
    protected $normalization;
    protected $validation;
    protected $defaultValue;
    protected $default = \false;
    protected $required = \false;
    protected $deprecation = [];
    protected $merge;
    protected $allowEmptyValue = \true;
    protected $nullEquivalent;
    protected $trueEquivalent = \true;
    protected $falseEquivalent = \false;
    protected $pathSeparator = BaseNode::DEFAULT_PATH_SEPARATOR;
    protected $parent;
    protected $attributes = [];
    /**
     * @param string|null $name
     */
    public function __construct($name, NodeParentInterface $parent = null)
    {
        $this->parent = $parent;
        $this->name = $name;
    }
    /**
     * @param NodeParentInterface $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }
    /**
     * @param string $info
     */
    public function info($info)
    {
        return $this->attribute('info', $info);
    }
    public function example($example)
    {
        return $this->attribute('example', $example);
    }
    /**
     * @param string $key
     */
    public function attribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    public function end()
    {
        return $this->parent;
    }
    /**
     * @param bool $forceRootNode
     */
    public function getNode($forceRootNode = \false)
    {
        if ($forceRootNode) {
            $this->parent = null;
        }
        if (null !== $this->normalization) {
            $this->normalization->before = ExprBuilder::buildExpressions($this->normalization->before);
        }
        if (null !== $this->validation) {
            $this->validation->rules = ExprBuilder::buildExpressions($this->validation->rules);
        }
        $node = $this->createNode();
        if ($node instanceof BaseNode) {
            $node->setAttributes($this->attributes);
        }
        return $node;
    }
    public function defaultValue($value)
    {
        $this->default = \true;
        $this->defaultValue = $value;
        return $this;
    }
    public function isRequired()
    {
        $this->required = \true;
        return $this;
    }
    public function setDeprecated()
    {
        $args = \func_get_args();
        if (\func_num_args() < 2) {
            trigger_deprecation('symfony/config', '5.1', 'The signature of method "%s()" requires 3 arguments: "string $package, string $version, string $message", not defining them is deprecated.', __METHOD__);
            $message = $args[0] ?? 'The child node "%node%" at path "%path%" is deprecated.';
            $package = $version = '';
        } else {
            $package = (string) $args[0];
            $version = (string) $args[1];
            $message = (string) ($args[2] ?? 'The child node "%node%" at path "%path%" is deprecated.');
        }
        $this->deprecation = ['package' => $package, 'version' => $version, 'message' => $message];
        return $this;
    }
    public function treatNullLike($value)
    {
        $this->nullEquivalent = $value;
        return $this;
    }
    public function treatTrueLike($value)
    {
        $this->trueEquivalent = $value;
        return $this;
    }
    public function treatFalseLike($value)
    {
        $this->falseEquivalent = $value;
        return $this;
    }
    public function defaultNull()
    {
        return $this->defaultValue(null);
    }
    public function defaultTrue()
    {
        return $this->defaultValue(\true);
    }
    public function defaultFalse()
    {
        return $this->defaultValue(\false);
    }
    public function beforeNormalization()
    {
        return $this->normalization()->before();
    }
    public function cannotBeEmpty()
    {
        $this->allowEmptyValue = \false;
        return $this;
    }
    public function validate()
    {
        return $this->validation()->rule();
    }
    /**
     * @param bool $deny
     */
    public function cannotBeOverwritten($deny = \true)
    {
        $this->merge()->denyOverwrite($deny);
        return $this;
    }
    protected function validation()
    {
        if (null === $this->validation) {
            $this->validation = new ValidationBuilder($this);
        }
        return $this->validation;
    }
    protected function merge()
    {
        if (null === $this->merge) {
            $this->merge = new MergeBuilder($this);
        }
        return $this->merge;
    }
    protected function normalization()
    {
        if (null === $this->normalization) {
            $this->normalization = new NormalizationBuilder($this);
        }
        return $this->normalization;
    }
    protected abstract function createNode();
    /**
     * @param string $separator
     */
    public function setPathSeparator($separator)
    {
        if ($this instanceof ParentNodeDefinitionInterface) {
            foreach ($this->getChildNodeDefinitions() as $child) {
                $child->setPathSeparator($separator);
            }
        }
        $this->pathSeparator = $separator;
        return $this;
    }
}
