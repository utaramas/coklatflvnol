<?php

namespace Staatic\Framework;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class Build
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string|null
     */
    private $parentId;
    /**
     * @var UriInterface
     */
    private $entryUrl;
    /**
     * @var UriInterface
     */
    private $destinationUrl;
    /**
     * @var \DateTimeInterface
     */
    private $dateCreated;
    /**
     * @var \DateTimeInterface|null
     */
    private $dateCrawlStarted;
    /**
     * @var \DateTimeInterface|null
     */
    private $dateCrawlFinished;
    /**
     * @var int
     */
    private $numUrlsCrawlable;
    /**
     * @var int
     */
    private $numUrlsCrawled;
    /**
     * @param string|null $parentId
     * @param \DateTimeInterface|null $dateCreated
     * @param \DateTimeInterface|null $dateCrawlStarted
     * @param \DateTimeInterface|null $dateCrawlFinished
     */
    public function __construct(string $id, UriInterface $entryUrl, UriInterface $destinationUrl, $parentId = null, $dateCreated = null, $dateCrawlStarted = null, $dateCrawlFinished = null, int $numUrlsCrawlable = 0, int $numUrlsCrawled = 0)
    {
        $this->id = $id;
        $this->entryUrl = $entryUrl;
        $this->destinationUrl = $destinationUrl;
        $this->parentId = $parentId;
        $this->dateCreated = $dateCreated ?: new \DateTimeImmutable();
        $this->dateCrawlStarted = $dateCrawlStarted;
        $this->dateCrawlFinished = $dateCrawlFinished;
        $this->numUrlsCrawlable = $numUrlsCrawlable;
        $this->numUrlsCrawled = $numUrlsCrawled;
    }
    public function __toString()
    {
        return (string) $this->id;
    }
    public function id() : string
    {
        return $this->id;
    }
    /**
     * @return string|null
     */
    public function parentId()
    {
        return $this->parentId;
    }
    public function entryUrl() : UriInterface
    {
        return $this->entryUrl;
    }
    public function destinationUrl() : UriInterface
    {
        return $this->destinationUrl;
    }
    public function dateCreated() : \DateTimeInterface
    {
        return $this->dateCreated;
    }
    /**
     * @return \DateTimeInterface|null
     */
    public function dateCrawlStarted()
    {
        return $this->dateCrawlStarted;
    }
    /**
     * @return \DateTimeInterface|null
     */
    public function dateCrawlFinished()
    {
        return $this->dateCrawlFinished;
    }
    public function isFinishedCrawling() : bool
    {
        return (bool) $this->dateCrawlFinished;
    }
    public function numUrlsCrawlable() : int
    {
        return $this->numUrlsCrawlable;
    }
    public function numUrlsCrawled() : int
    {
        return $this->numUrlsCrawled;
    }
    /**
     * @return void
     */
    public function crawlStarted()
    {
        $this->dateCrawlStarted = new \DateTimeImmutable();
    }
    /**
     * @return void
     */
    public function crawlFinished()
    {
        $this->dateCrawlFinished = new \DateTimeImmutable();
    }
    /**
     * @param int $numUrlsCrawlable
     * @return void
     */
    public function queuedUrls($numUrlsCrawlable)
    {
        $this->numUrlsCrawlable = $numUrlsCrawlable;
    }
    /**
     * @param int $numUrlsCrawlable
     * @return void
     */
    public function crawledUrl($numUrlsCrawlable)
    {
        $this->numUrlsCrawlable = $numUrlsCrawlable;
        $this->numUrlsCrawled++;
    }
    /**
     * @param int $numUrlsCrawlable
     * @param int $numCrawled
     * @return void
     */
    public function crawledUrls($numUrlsCrawlable, $numCrawled)
    {
        $this->numUrlsCrawlable = $numUrlsCrawlable;
        $this->numUrlsCrawled += $numCrawled;
    }
}
