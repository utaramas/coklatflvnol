<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\EnvParameterException;
class Compiler
{
    private $passConfig;
    private $log = [];
    private $serviceReferenceGraph;
    public function __construct()
    {
        $this->passConfig = new PassConfig();
        $this->serviceReferenceGraph = new ServiceReferenceGraph();
    }
    public function getPassConfig()
    {
        return $this->passConfig;
    }
    public function getServiceReferenceGraph()
    {
        return $this->serviceReferenceGraph;
    }
    /**
     * @param CompilerPassInterface $pass
     * @param string $type
     * @param int $priority
     */
    public function addPass($pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, $priority = 0)
    {
        $this->passConfig->addPass($pass, $type, $priority);
    }
    /**
     * @param CompilerPassInterface $pass
     * @param string $message
     */
    public function log($pass, $message)
    {
        if (\false !== \strpos($message, "\n")) {
            $message = \str_replace("\n", "\n" . \get_class($pass) . ': ', \trim($message));
        }
        $this->log[] = \get_class($pass) . ': ' . $message;
    }
    public function getLog()
    {
        return $this->log;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function compile($container)
    {
        try {
            foreach ($this->passConfig->getPasses() as $pass) {
                $pass->process($container);
            }
        } catch (\Exception $e) {
            $usedEnvs = [];
            $prev = $e;
            do {
                $msg = $prev->getMessage();
                if ($msg !== ($resolvedMsg = $container->resolveEnvPlaceholders($msg, null, $usedEnvs))) {
                    $r = new \ReflectionProperty($prev, 'message');
                    $r->setAccessible(\true);
                    $r->setValue($prev, $resolvedMsg);
                }
            } while ($prev = $prev->getPrevious());
            if ($usedEnvs) {
                $e = new EnvParameterException($usedEnvs, $e);
            }
            throw $e;
        } finally {
            $this->getServiceReferenceGraph()->clear();
        }
    }
}
