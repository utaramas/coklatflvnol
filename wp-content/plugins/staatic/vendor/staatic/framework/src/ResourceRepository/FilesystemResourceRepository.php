<?php

namespace Staatic\Framework\ResourceRepository;

use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Framework\Resource;
final class FilesystemResourceRepository implements ResourceRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var string
     */
    private $targetDirectory;
    public function __construct(string $targetDirectory)
    {
        $this->logger = new NullLogger();
        $this->setTargetDirectory($targetDirectory);
    }
    /**
     * @return void
     */
    private function setTargetDirectory(string $targetDirectory)
    {
        $targetDirectory = \rtrim($targetDirectory, '/');
        if (!\is_dir($targetDirectory)) {
            throw new \RuntimeException(\sprintf('Target directory does not exist in %s', $targetDirectory));
        }
        $this->targetDirectory = $targetDirectory;
    }
    /**
     * @param Resource $resource
     * @return void
     */
    public function write($resource)
    {
        $this->logger->debug(\sprintf('Adding resource #%s', $resource->id()), ['resourceId' => $resource->id()]);
        $sourceStream = $resource->content();
        $targetFile = \sprintf('%s/%s', $this->targetDirectory, $resource->id());
        $targetStream = Utils::streamFor(\fopen($targetFile, 'w+'));
        while (!$sourceStream->eof()) {
            $buffer = $sourceStream->read(4096);
            $targetStream->write($buffer);
        }
        $md5 = \md5_file($targetFile);
        $sha1 = \sha1_file($targetFile);
        $size = \filesize($targetFile);
        $resource->replace($targetStream, $md5, $sha1, $size);
        $this->logger->debug(\sprintf('Copied resource #%s content to filesystem (%d bytes)', $resource->id(), $size), ['resourceId' => $resource->id()]);
    }
    /**
     * @param string $resourceId
     * @return Resource|null
     */
    public function find($resourceId)
    {
        $targetFile = \sprintf('%s/%s', $this->targetDirectory, $resourceId);
        if (!\is_readable($targetFile)) {
            throw new \RuntimeException(\sprintf('Unable to load resource #%s from filesystem', $resourceId));
        }
        $targetStream = Utils::streamFor(\fopen($targetFile, 'r+'));
        return Resource::create($resourceId, $targetStream);
    }
}
