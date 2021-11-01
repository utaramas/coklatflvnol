<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Cli;

use function WP_CLI\Utils\get_flag_value;
use function WP_CLI\Utils\make_progress_bar;
use Staatic\Vendor\Psr\Log\LoggerInterface;
use Staatic\WordPress\Logging\Contextable;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Publication\PublicationManagerInterface;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Publication\PublicationTaskProvider;
use Staatic\WordPress\Publication\Task\CrawlTask;
use Staatic\WordPress\Publication\Task\DeployTask;
use Staatic\WordPress\Service\Formatter;

class PublishCommand
{
    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var PublicationRepository
     */
    protected $publicationRepository;

    /**
     * @var PublicationManagerInterface
     */
    protected $publicationManager;

    /**
     * @var PublicationTaskProvider
     */
    protected $taskProvider;

    public function __construct(
        \wpdb $wpdb,
        LoggerInterface $logger,
        Formatter $formatter,
        PublicationRepository $publicationRepository,
        PublicationManagerInterface $publicationManager,
        PublicationTaskProvider $taskProvider
    )
    {
        $this->wpdb = $wpdb;
        $this->logger = $logger;
        $this->formatter = $formatter;
        $this->publicationRepository = $publicationRepository;
        $this->publicationManager = $publicationManager;
        $this->taskProvider = $taskProvider;
    }

    /**
     * Initiates background process to publish static site.
     *
     * ## OPTIONS
     *
     * [--[no-]verbose]
     * : Whether or not to output log entries during publication
     * ---
     * default: false
     * ---
     *
     * ## EXAMPLES
     *
     *     wp staatic publish
     *
     * @when after_wp_load
     * @return void
     */
    public function publish($args, $assoc_args)
    {
        $verbose = get_flag_value($assoc_args, 'verbose', \false);
        if ($verbose && \method_exists($this->logger, 'enablePrintLogs')) {
            $this->logger->enablePrintLogs();
        }
        if ($this->publicationManager->isPublicationInProgress()) {
            \WP_CLI::error(__('Unable to publish; another publication is pending', 'staatic'));
        }
        $publication = $this->createPublication();
        if ($this->publicationManager->claimPublication($publication)) {
            $this->startPublication($publication);
        } else {
            $this->publicationManager->cancelPublication($publication);
            throw new \RuntimeException(__('Unable to claim publication; another publication is pending', 'staatic'));
        }
    }

    protected function createPublication() : Publication
    {
        return $this->publicationManager->createPublication();
    }

    /**
     * @param Publication $publication
     * @return void
     */
    protected function startPublication($publication)
    {
        $this->logger->notice(__('Starting publication', 'staatic'), [
            'publicationId' => $publication->id()
        ]);
        $publication->markInProgress();
        $task = $this->taskProvider->firstTask();
        do {
            \WP_CLI::line($task->description());
            $taskName = \get_class($task);
            if ($this->logger instanceof Contextable) {
                $this->logger->changeContext([
                    'publicationId' => $publication->id(),
                    'task' => $taskName
                ]);
            }
            $publication->setCurrentTask($taskName);
            $this->publicationRepository->update($publication);
            $this->logger->info($task->description());
            do_action('staatic_publication_before_task', [
                'publicationId' => $publication->id(),
                'task' => $taskName
            ]);
            if ($taskName === CrawlTask::class) {
                $progress = make_progress_bar(__('Crawling...', 'staatic'), 0);
                $ticks = 0;
            } elseif ($taskName === DeployTask::class) {
                $progress = make_progress_bar(__('Deploying...', 'staatic'), 0);
                $ticks = 0;
            }
            do {
                if ($this->isPublicationCancelled($publication)) {
                    $publication->markCanceled();
                    $this->publicationRepository->update($publication);
                    $this->logger->warning(__('Publication has been cancelled', 'staatic'));
                    \WP_CLI::error(__('Publication was cancelled', 'staatic'));
                    return;
                }
                try {
                    $taskFinished = $task->execute($publication);
                    $this->publicationRepository->update($publication);
                } catch (\Error $error) {
                    $publication->markFailed();
                    $this->publicationRepository->update($publication);
                    update_option('staatic_current_publication_id', '');
                    $this->logger->critical(\sprintf(
                        /* translators: %1$s: Publication task, %2$s: Error message. */
                        __('PHP error during task %1$s: %2$s', 'staatic'),
                        $taskName,
                        (string) $error
                    ));
                    \WP_CLI::error(\sprintf(
                        /* translators: %1$s: Publication task, %2$s: Error type, %3$s: Error message. */
                        __('Publication failed during task %1$s with error %2$s: %3$s', 'staatic'),
                        $taskName,
                        \get_class($error),
                        $error->getMessage()
                    ));
                } catch (\Exception $exception) {
                    $publication->markFailed();
                    $this->publicationRepository->update($publication);
                    update_option('staatic_current_publication_id', '');
                    $this->logger->error(\sprintf(
                        /* translators: %1$s: Publication task, %2$s: Exception message. */
                        __('Error during task %1$s: %2$s', 'staatic'),
                        $taskName,
                        (string) $exception
                    ));
                    \WP_CLI::error(\sprintf(
                        /* translators: %1$s: Publication task, %2$s: Exception type, %3$s: Exception message. */
                        __('Publication failed during task %1$s with exception %2$s: %3$s', 'staatic'),
                        $taskName,
                        \get_class($exception),
                        $exception->getMessage()
                    ));
                }
                if ($taskName === CrawlTask::class) {
                    $addTicks = $publication->build()->numUrlsCrawled() - $ticks;
                    if ($addTicks) {
                        $progress->setTotal($publication->build()->numUrlsCrawlable());
                        $progress->tick($addTicks);
                        $ticks += $addTicks;
                    }
                } elseif ($taskName === DeployTask::class) {
                    $addTicks = $publication->deployment()->numResultsDeployed() - $ticks;
                    if ($addTicks) {
                        $progress->setTotal($publication->deployment()->numResultsDeployable());
                        $progress->tick($addTicks);
                        $ticks += $addTicks;
                    }
                }
            } while (!$taskFinished);
            do_action('staatic_publication_after_task', [
                'publicationId' => $publication->id(),
                'task' => $taskName
            ]);
            if ($taskName === CrawlTask::class || $taskName === DeployTask::class) {
                $progress->finish();
            }
        } while ($publication->status()->isInProgress() && ($task = $this->taskProvider->nextTask($task)));
        $this->logger->notice(__('Finished publication', 'staatic'), [
            'publicationId' => $publication->id()
        ]);
        \WP_CLI::success(\sprintf(
            /* translators: %s: Date interval time taken. */
            __('Publication finished in %s!', 'staatic'),
            $this->formatter->difference($publication->dateCreated(), $publication->dateFinished())
        ));
    }

    /**
     * @param Publication $publication
     */
    protected function isPublicationCancelled($publication) : bool
    {
        $currentPublicationId = $this->wpdb->get_var(
            $this->wpdb->prepare("SELECT option_value FROM {$this->wpdb->prefix}options WHERE option_name = %s", 'staatic_current_publication_id')
        );
        return $currentPublicationId !== $publication->id();
    }
}
