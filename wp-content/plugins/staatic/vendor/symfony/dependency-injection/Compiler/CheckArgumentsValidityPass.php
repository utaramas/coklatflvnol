<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Definition;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\RuntimeException;
class CheckArgumentsValidityPass extends AbstractRecursivePass
{
    private $throwExceptions;
    public function __construct(bool $throwExceptions = \true)
    {
        $this->throwExceptions = $throwExceptions;
    }
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        if (!$value instanceof Definition) {
            return parent::processValue($value, $isRoot);
        }
        $i = 0;
        foreach ($value->getArguments() as $k => $v) {
            if ($k !== $i++) {
                if (!\is_int($k)) {
                    $msg = \sprintf('Invalid constructor argument for service "%s": integer expected but found string "%s". Check your service definition.', $this->currentId, $k);
                    $value->addError($msg);
                    if ($this->throwExceptions) {
                        throw new RuntimeException($msg);
                    }
                    break;
                }
                $msg = \sprintf('Invalid constructor argument %d for service "%s": argument %d must be defined before. Check your service definition.', 1 + $k, $this->currentId, $i);
                $value->addError($msg);
                if ($this->throwExceptions) {
                    throw new RuntimeException($msg);
                }
            }
        }
        foreach ($value->getMethodCalls() as $methodCall) {
            $i = 0;
            foreach ($methodCall[1] as $k => $v) {
                if ($k !== $i++) {
                    if (!\is_int($k)) {
                        $msg = \sprintf('Invalid argument for method call "%s" of service "%s": integer expected but found string "%s". Check your service definition.', $methodCall[0], $this->currentId, $k);
                        $value->addError($msg);
                        if ($this->throwExceptions) {
                            throw new RuntimeException($msg);
                        }
                        break;
                    }
                    $msg = \sprintf('Invalid argument %d for method call "%s" of service "%s": argument %d must be defined before. Check your service definition.', 1 + $k, $methodCall[0], $this->currentId, $i);
                    $value->addError($msg);
                    if ($this->throwExceptions) {
                        throw new RuntimeException($msg);
                    }
                }
            }
        }
        return null;
    }
}
