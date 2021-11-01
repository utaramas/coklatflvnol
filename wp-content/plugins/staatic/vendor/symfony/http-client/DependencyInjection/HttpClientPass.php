<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\DependencyInjection;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Reference;
use Staatic\Vendor\Symfony\Component\HttpClient\TraceableHttpClient;
final class HttpClientPass implements CompilerPassInterface
{
    private $clientTag;
    public function __construct(string $clientTag = 'http_client.client')
    {
        $this->clientTag = $clientTag;
    }
    /**
     * @param ContainerBuilder $container
     */
    public function process($container)
    {
        if (!$container->hasDefinition('data_collector.http_client')) {
            return;
        }
        foreach ($container->findTaggedServiceIds($this->clientTag) as $id => $tags) {
            $container->register('.debug.' . $id, TraceableHttpClient::class)->setArguments([new Reference('.debug.' . $id . '.inner'), new Reference('debug.stopwatch', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)])->setDecoratedService($id);
            $container->getDefinition('data_collector.http_client')->addMethodCall('registerClient', [$id, new Reference('.debug.' . $id)]);
        }
    }
}
