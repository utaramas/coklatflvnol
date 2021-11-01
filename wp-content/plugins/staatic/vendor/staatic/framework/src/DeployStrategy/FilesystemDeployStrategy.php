<?php

namespace Staatic\Framework\DeployStrategy;

use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Symfony\Component\Filesystem\Exception\IOException;
use Staatic\Vendor\Symfony\Component\Filesystem\Filesystem;
use Staatic\Framework\Deployment;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\Framework\Result;
use Staatic\Framework\Util\PathHelper;
final class FilesystemDeployStrategy implements DeployStrategyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;
    /**
     * @var string
     */
    private $targetDirectory;
    /**
     * @var string|null
     */
    private $stagingDirectory;
    /**
     * @var string
     */
    private $workingDirectory;
    /**
     * @var mixed[]
     */
    private $excludePaths;
    /**
     * @var mixed[]
     */
    private $symlinks;
    /**
     * @var bool
     */
    private $copyOnWindows;
    /**
     * @var mixed[]
     */
    private $loggerContext = [];
    public function __construct(ResourceRepositoryInterface $resourceRepository, array $options = [])
    {
        $this->logger = new NullLogger();
        $this->filesystem = new Filesystem();
        $this->resourceRepository = $resourceRepository;
        if (empty($options['targetDirectory'])) {
            throw new \InvalidArgumentException('Missing required option "targetDirectory"');
        }
        $this->setTargetDirectory($options['targetDirectory']);
        if (!empty($options['stagingDirectory'])) {
            $this->setStagingDirectory($options['stagingDirectory']);
        }
        $this->workingDirectory = $this->stagingDirectory ?: $this->targetDirectory;
        $this->excludePaths = $options['excludePaths'] ?? [];
        $this->symlinks = $options['symlinks'] ?? [];
        $this->copyOnWindows = \false;
        if (\DIRECTORY_SEPARATOR === '\\') {
            $this->copyOnWindows = $options['copyOnWindows'] ?? \false;
        }
    }
    /**
     * @return void
     */
    private function setTargetDirectory(string $targetDirectory)
    {
        $targetDirectory = \rtrim($targetDirectory, '/\\');
        if (!\is_dir($targetDirectory)) {
            throw new \InvalidArgumentException(\sprintf('Target directory "%s" does not exist', $targetDirectory));
        }
        if (!\is_writable($targetDirectory)) {
            throw new \InvalidArgumentException(\sprintf('Target directory "%s" is not writable', $targetDirectory));
        }
        $this->targetDirectory = $targetDirectory;
    }
    /**
     * @return void
     */
    private function setStagingDirectory(string $stagingDirectory)
    {
        $stagingDirectory = \rtrim($stagingDirectory, '/\\');
        if (!\is_dir($stagingDirectory)) {
            throw new \InvalidArgumentException(\sprintf('Staging directory "%s" does not exist', $stagingDirectory));
        }
        if (!\is_writable($stagingDirectory)) {
            throw new \InvalidArgumentException(\sprintf('Staging directory "%s" is not writable', $stagingDirectory));
        }
        if (\realpath($stagingDirectory) === \realpath($this->targetDirectory)) {
            throw new \InvalidArgumentException(\sprintf('Staging directory "%s" cannot be the same as target directory "%s"', $stagingDirectory, $this->targetDirectory));
        }
        $this->stagingDirectory = $stagingDirectory;
    }
    /**
     * @param Deployment $deployment
     */
    public function initiate($deployment) : array
    {
        $this->loggerContext = ['deploymentId' => $deployment->id()];
        $this->logger->info('Initiating deployment', $this->loggerContext);
        $this->ensureTargetDirectoryExists();
        if ($this->stagingDirectory) {
            $this->ensureStagingDirectoryExists();
        }
        $this->clearWorkingDirectory($this->workingDirectory);
        $this->createSymlinks();
        return [];
    }
    /**
     * @param Deployment $deployment
     * @param mixed[] $results
     * @return void
     */
    public function processResults($deployment, $results)
    {
        $this->loggerContext = ['deploymentId' => $deployment->id()];
        $this->logger->info('Deploying results', $this->loggerContext);
        $numResults = 0;
        foreach ($results as $result) {
            $filePath = PathHelper::determineFilePath($result->url()->getPath());
            \assert(\substr($filePath, 0, 1) === '/');
            $targetPath = $this->workingDirectory . $filePath;
            $resource = $this->resourceRepository->find($result->resourceId());
            $resource->content()->rewind();
            $this->filesystem->dumpFile($targetPath, $resource->content());
            $this->logger->debug(\sprintf('Wrote %d bytes to "%s"', $result->size(), $targetPath), \array_merge($this->loggerContext, ['resultId' => $result->id(), 'resourceId' => $resource->id()]));
            $numResults++;
        }
        $this->logger->info(\sprintf('Deployed %d files to "%s"', $numResults, $this->workingDirectory), $this->loggerContext);
    }
    /**
     * @param Deployment $deployment
     * @return void
     */
    public function finish($deployment)
    {
        $this->loggerContext = ['deploymentId' => $deployment->id()];
        $this->logger->info('Finishing deployment', $this->loggerContext);
        if ($this->stagingDirectory) {
            $this->mirrorStagingDirectory();
        }
    }
    /**
     * @return void
     */
    private function ensureTargetDirectoryExists()
    {
        if (\is_dir($this->targetDirectory)) {
            return;
        }
        $this->logger->info(\sprintf('Target directory "%s" does not exist, attempting to create it', $this->targetDirectory), $this->loggerContext);
        if (!\mkdir($this->targetDirectory, 0777, \true)) {
            throw new \RuntimeException(\sprintf('Unable to create target directory "%s"', $this->targetDirectory));
        }
    }
    /**
     * @return void
     */
    private function ensureStagingDirectoryExists()
    {
        if (\is_dir($this->stagingDirectory)) {
            return;
        }
        $this->logger->info(\sprintf('Staging directory "%s" does not exist, attempting to create it', $this->stagingDirectory), $this->loggerContext);
        if (!\mkdir($this->stagingDirectory, 0777, \true)) {
            throw new \RuntimeException(\sprintf('Unable to create staging directory "%s"', $this->stagingDirectory));
        }
    }
    /**
     * @return void
     */
    private function clearWorkingDirectory()
    {
        $this->logger->debug(\sprintf('Clearing working directory in %s', $this->workingDirectory), $this->loggerContext);
        $deleteIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->workingDirectory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($deleteIterator as $file) {
            $this->filesystem->remove($file);
        }
    }
    /**
     * @return void
     */
    private function createSymlinks()
    {
        $this->logger->debug('Creating symlinks', $this->loggerContext);
        foreach ($this->symlinks as $originDir => $targetDir) {
            $targetDir = $this->workingDirectory . $targetDir;
            $this->logger->info(\sprintf('Symlinking "%s" to "%s"', $originDir, $targetDir), $this->loggerContext);
            $this->filesystem->symlink($originDir, $targetDir, $this->copyOnWindows);
        }
    }
    /**
     * @return void
     */
    private function mirrorStagingDirectory()
    {
        $this->logger->debug(\sprintf('Mirroring staging directory (%s) with target directory (%s)', $this->workingDirectory, $this->targetDirectory), $this->loggerContext);
        $this->mirror($this->stagingDirectory, $this->targetDirectory, $this->excludePaths);
    }
    /**
     * @return void
     */
    private function mirror(string $originDir, string $targetDir, array $excludePaths)
    {
        $excludeMap = $this->excludePathsToMap($excludePaths, $targetDir);
        if ($this->filesystem->exists($targetDir)) {
            $flags = \FilesystemIterator::SKIP_DOTS;
            $deleteIterator = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator(new \RecursiveDirectoryIterator($targetDir, $flags), function ($fileInfo, $path, $iterator) use($excludeMap) {
                return !isset($excludeMap[$path]);
            }), \RecursiveIteratorIterator::CHILD_FIRST);
            $targetDirLen = \strlen($targetDir);
            foreach ($deleteIterator as $file) {
                $origin = $originDir . \substr($file->getPathname(), $targetDirLen);
                if (!$this->filesystem->exists($origin)) {
                    $this->logger->debug(\sprintf('Removing obsolete entry "%s"', $file));
                    $this->filesystem->remove($file);
                }
                if (!$this->isSameFileType($file, $origin)) {
                    $this->logger->debug(\sprintf('Removing incorrectly typed entry "%s"', $file));
                    $this->filesystem->remove($file);
                }
            }
        }
        $originDirLen = \strlen($originDir);
        $flags = $this->copyOnWindows ? \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS : \FilesystemIterator::SKIP_DOTS;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($originDir, $flags), \RecursiveIteratorIterator::SELF_FIRST);
        $this->filesystem->mkdir($targetDir);
        $filesCreatedWhileMirroring = [];
        foreach ($iterator as $file) {
            if ($file->getPathname() === $targetDir || $file->getRealPath() === $targetDir || isset($filesCreatedWhileMirroring[$file->getRealPath()])) {
                $this->logger->warning('continue');
                continue;
            }
            $target = $targetDir . \substr($file->getPathname(), $originDirLen);
            $filesCreatedWhileMirroring[$target] = \true;
            if (!$this->copyOnWindows && \is_link($file)) {
                $this->logger->debug(\sprintf('Symlinking "%s" to "%s"', $file->getLinkTarget(), $target));
                $this->filesystem->symlink($file->getLinkTarget(), $target);
            } elseif (\is_dir($file)) {
                $this->logger->debug(\sprintf('Creating directory "%s"', $target));
                $this->filesystem->mkdir($target);
            } elseif (\is_file($file)) {
                $this->logger->debug(\sprintf('Copying file "%s" to "%s"', $file, $target));
                $this->filesystem->copy($file, $target, \true);
            } else {
                throw new IOException(\sprintf('Unable to guess "%s" file type.', $file), 0, null, $file);
            }
        }
    }
    private function excludePathsToMap(array $excludePaths, string $targetDir) : array
    {
        $excludeMap = [];
        $targetDirLen = \strlen($targetDir);
        foreach ($excludePaths as $excludePath) {
            if (\DIRECTORY_SEPARATOR === '\\') {
                $excludePath = \strtr($excludePath, '/', '\\');
            }
            $excludePath = \substr($excludePath, $targetDirLen);
            $excludeMap[$targetDir . $excludePath] = \true;
            while ($pos = \strrpos($excludePath, \DIRECTORY_SEPARATOR)) {
                $excludePath = \substr($excludePath, 0, $pos);
                $excludeMap[$targetDir . $excludePath] = \true;
            }
        }
        return $excludeMap;
    }
    private function isSameFileType(string $file, string $fileCompare) : bool
    {
        if (\is_link($file) !== \is_link($fileCompare)) {
            return \false;
        }
        if (\is_dir($file) !== \is_dir($fileCompare)) {
            return \false;
        }
        if (\is_file($file) !== \is_file($fileCompare)) {
            return \false;
        }
        return \true;
    }
}
