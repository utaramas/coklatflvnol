<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition\Builder;

use Staatic\Vendor\Symfony\Component\Config\Definition\Exception\UnsetKeyException;
class ExprBuilder
{
    protected $node;
    public $ifPart;
    public $thenPart;
    public function __construct(NodeDefinition $node)
    {
        $this->node = $node;
    }
    /**
     * @param \Closure|null $then
     */
    public function always($then = null)
    {
        $this->ifPart = function ($v) {
            return \true;
        };
        if (null !== $then) {
            $this->thenPart = $then;
        }
        return $this;
    }
    /**
     * @param \Closure|null $closure
     */
    public function ifTrue($closure = null)
    {
        if (null === $closure) {
            $closure = function ($v) {
                return \true === $v;
            };
        }
        $this->ifPart = $closure;
        return $this;
    }
    public function ifString()
    {
        $this->ifPart = function ($v) {
            return \is_string($v);
        };
        return $this;
    }
    public function ifNull()
    {
        $this->ifPart = function ($v) {
            return null === $v;
        };
        return $this;
    }
    public function ifEmpty()
    {
        $this->ifPart = function ($v) {
            return empty($v);
        };
        return $this;
    }
    public function ifArray()
    {
        $this->ifPart = function ($v) {
            return \is_array($v);
        };
        return $this;
    }
    /**
     * @param mixed[] $array
     */
    public function ifInArray($array)
    {
        $this->ifPart = function ($v) use($array) {
            return \in_array($v, $array, \true);
        };
        return $this;
    }
    /**
     * @param mixed[] $array
     */
    public function ifNotInArray($array)
    {
        $this->ifPart = function ($v) use($array) {
            return !\in_array($v, $array, \true);
        };
        return $this;
    }
    public function castToArray()
    {
        $this->ifPart = function ($v) {
            return !\is_array($v);
        };
        $this->thenPart = function ($v) {
            return [$v];
        };
        return $this;
    }
    /**
     * @param \Closure $closure
     */
    public function then($closure)
    {
        $this->thenPart = $closure;
        return $this;
    }
    public function thenEmptyArray()
    {
        $this->thenPart = function ($v) {
            return [];
        };
        return $this;
    }
    /**
     * @param string $message
     */
    public function thenInvalid($message)
    {
        $this->thenPart = function ($v) use($message) {
            throw new \InvalidArgumentException(\sprintf($message, \json_encode($v)));
        };
        return $this;
    }
    public function thenUnset()
    {
        $this->thenPart = function ($v) {
            throw new UnsetKeyException('Unsetting key.');
        };
        return $this;
    }
    public function end()
    {
        if (null === $this->ifPart) {
            throw new \RuntimeException('You must specify an if part.');
        }
        if (null === $this->thenPart) {
            throw new \RuntimeException('You must specify a then part.');
        }
        return $this->node;
    }
    /**
     * @param mixed[] $expressions
     */
    public static function buildExpressions($expressions)
    {
        foreach ($expressions as $k => $expr) {
            if ($expr instanceof self) {
                $if = $expr->ifPart;
                $then = $expr->thenPart;
                $expressions[$k] = function ($v) use($if, $then) {
                    return $if($v) ? $then($v) : $v;
                };
            }
        }
        return $expressions;
    }
}
