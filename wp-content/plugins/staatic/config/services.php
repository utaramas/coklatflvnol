<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Setting\SettingInterface;
use Staatic\WordPress\Module\ModuleCollection;
use Staatic\WordPress\Activator;
use Staatic\WordPress\Deactivator;
use Staatic\WordPress\Uninstaller;
use Staatic\WordPress\DependencyInjection\WpdbWrapper;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Factory\PartialRendererFactory;
use Staatic\Vendor\Psr\SimpleCache\CacheInterface;
use Staatic\WordPress\Cache\TransientCache;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\WordPress\Logging\Logger;
use Staatic\WordPress\Factory\LoggerFactory;
use Staatic\Vendor\GuzzleHttp\ClientInterface;
use Staatic\WordPress\Factory\HttpClientFactory;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;
use Staatic\Crawler\UrlTransformer\UrlTransformerInterface;
use Staatic\WordPress\Factory\UrlTransformerFactory;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\WordPress\Factory\FilesystemResourceRepositoryFactory;
use Staatic\Framework\BuildRepository\BuildRepositoryInterface;
use Staatic\WordPress\Bridge\BuildRepository;
use Staatic\Crawler\CrawlQueue\CrawlQueueInterface;
use Staatic\WordPress\Bridge\CrawlQueue;
use Staatic\Framework\DeploymentRepository\DeploymentRepositoryInterface;
use Staatic\WordPress\Bridge\DeploymentRepository;
use Staatic\Crawler\KnownUrlsContainer\KnownUrlsContainerInterface;
use Staatic\WordPress\Bridge\KnownUrlsContainer;
use Staatic\Framework\ResultRepository\ResultRepositoryInterface;
use Staatic\WordPress\Bridge\ResultRepository;
use Staatic\WordPress\Service\AdminNavigation;
use Staatic\WordPress\Logging\LogEntryRepository;
use Staatic\WordPress\Publication\PublicationManager;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Publication\PublicationTaskProvider;
use Staatic\WordPress\Service\Scheduler;
use Staatic\WordPress\Service\Settings;
use Staatic\WordPress\Publication\Task\SetupTask;
use Staatic\WordPress\Publication\Task\InitializeCrawlerTask;
use Staatic\WordPress\Publication\Task\CrawlTask;
use Staatic\WordPress\Publication\Task\FinishCrawlerTask;
use Staatic\WordPress\Publication\Task\PostProcessTask;
use Staatic\WordPress\Publication\Task\InitiateDeploymentTask;
use Staatic\WordPress\Publication\Task\DeployTask;
use Staatic\WordPress\Publication\Task\FinishDeploymentTask;
use Staatic\WordPress\Publication\Task\FinishTask;

return function (ContainerConfigurator $configurator) {
    $configurator->parameters()->set('staatic.hook.cleanup', 'staatic_cleanup')->set(
        'staatic.hook.publish',
        'staatic_publish'
    )->set(
        'staatic.schedule.cleanup',
        'twicedaily'
    );
    $services = $configurator->services()->defaults()->autowire()->bind('$pluginVersion', '%staatic.version%')->bind(
        '$cleanupHook',
        '%staatic.hook.cleanup%'
    )->bind(
        '$publishHook',
        '%staatic.hook.publish%'
    )->bind(
        '$cleanupSchedule',
        '%staatic.schedule.cleanup%'
    )->bind(
        '$settingLocator',
        tagged_locator('app.setting')
    )->bind(
        '$publicationTasks',
        tagged_iterator('app.publicationTask')
    );
    $services->instanceof(ModuleInterface::class)->tag('app.module');
    $services->instanceof(SettingInterface::class)->tag('app.setting');
    $services->load('Staatic\\WordPress\\', '../src/*');
    $services->set(ModuleCollection::class)->args([tagged_iterator('app.module')])->public();
    $services->set(Activator::class)->public();
    $services->set(Deactivator::class)->public();
    $services->set(Uninstaller::class)->public();
    $services->set(\wpdb::class, \wpdb::class)->factory([service(WpdbWrapper::class), 'get']);
    $services->set(PartialRenderer::class)->factory(service(PartialRendererFactory::class));
    // Allow these components to be lazy loaded using tiny proxy
    // https://olvlvl.com/2018-10-dependency-injection-proxy
    // https://github.com/olvlvl/symfony-dependency-injection-proxy
    $services->set(CacheInterface::class, TransientCache::class)->lazy(CacheInterface::class);
    $services->set(LoggerInterface::class, Logger::class)->factory(service(LoggerFactory::class))->lazy(
        \Staatic\WordPress\Logging\LoggerInterface::class
    );
    $services->set(ClientInterface::class)->factory([service(HttpClientFactory::class), 'createClient'])->lazy();
    $services->set('internal_http_client', ClientInterface::class)->factory(
        [service(HttpClientFactory::class), 'createInternalClient']
    )->lazy();
    $services->alias(ClientInterface::class . ' $internalHttpClient', 'internal_http_client');
    $services->set(HttpClientInterface::class)->factory(
        [service(HttpClientFactory::class), 'createSymfonyClient']
    )->lazy();
    $services->set(UrlTransformerInterface::class)->factory(service(UrlTransformerFactory::class))->lazy();
    $services->set(ResourceRepositoryInterface::class)->factory(
        service(FilesystemResourceRepositoryFactory::class)
    )->lazy();
    $services->set(BuildRepositoryInterface::class, BuildRepository::class)->lazy(BuildRepositoryInterface::class);
    $services->set(CrawlQueueInterface::class, CrawlQueue::class)->lazy(CrawlQueueInterface::class);
    $services->set(DeploymentRepositoryInterface::class, DeploymentRepository::class)->lazy(
        DeploymentRepositoryInterface::class
    );
    $services->set(KnownUrlsContainerInterface::class, KnownUrlsContainer::class)->lazy(
        KnownUrlsContainerInterface::class
    );
    $services->set(ResultRepositoryInterface::class, ResultRepository::class)->lazy(ResultRepositoryInterface::class);
    $servicesMap = [
        'staatic.admin_navigation' => AdminNavigation::class,
        'staatic.build_repository' => BuildRepositoryInterface::class,
        'staatic.cache' => CacheInterface::class,
        'staatic.crawl_queue' => CrawlQueueInterface::class,
        'staatic.deployment_repository' => DeploymentRepositoryInterface::class,
        'staatic.http_client' => ClientInterface::class,
        'staatic.known_urls_container' => KnownUrlsContainerInterface::class,
        'staatic.log_entry_repository' => LogEntryRepository::class,
        'staatic.logger' => LoggerInterface::class,
        'staatic.publication_manager' => PublicationManager::class,
        'staatic.publication_repository' => PublicationRepository::class,
        'staatic.publication_task_provider' => PublicationTaskProvider::class,
        'staatic.resource_repository' => ResourceRepositoryInterface::class,
        'staatic.result_repository' => ResultRepositoryInterface::class,
        'staatic.scheduler' => Scheduler::class,
        'staatic.settings' => Settings::class,
        'staatic.url_transformer' => UrlTransformerInterface::class
    ];
    foreach ($servicesMap as $alias => $service) {
        $services->alias($alias, $service)->public();
    }
    foreach ([
        SetupTask::class => 100,
        InitializeCrawlerTask::class => 80,
        CrawlTask::class => 60,
        FinishCrawlerTask::class => 40,
        PostProcessTask::class => 20,
        InitiateDeploymentTask::class => 0,
        DeployTask::class => -40,
        FinishDeploymentTask::class => -80,
        FinishTask::class => -120
    ] as $task => $priority) {
        $services->set($task)->tag('app.publicationTask', [
            'priority' => $priority
        ]);
    }
};
