<?php

namespace Staatic\Framework;

use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\StreamInterface;
final class Resource
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var StreamInterface
     */
    private $content;
    /**
     * @var string
     */
    private $md5;
    /**
     * @var string
     */
    private $sha1;
    /**
     * @var int
     */
    private $size;
    private function __construct(string $id, StreamInterface $content, string $md5, string $sha1, int $size)
    {
        if (\preg_match('~[^a-z0-9._ -]~i', $id) === 1) {
            throw new \InvalidArgumentException(\sprintf('Resource id "%s" contains invalid characters (supported: a-z0-9._ -)', $id));
        }
        $this->id = $id;
        $this->content = $content;
        $this->md5 = $md5;
        $this->sha1 = $sha1;
        $this->size = $size;
    }
    public static function create(string $id, $content) : self
    {
        $content = Utils::streamFor($content);
        list($md5, $sha1, $size) = self::calculateHashesAndSize($content);
        return new self($id, $content, $md5, $sha1, $size);
    }
    public function id() : string
    {
        return $this->id;
    }
    public function content() : StreamInterface
    {
        return $this->content;
    }
    public function md5() : string
    {
        return $this->md5;
    }
    public function sha1() : string
    {
        return $this->sha1;
    }
    public function size() : int
    {
        return $this->size;
    }
    /**
     * @param string|null $md5
     * @param string|null $sha1
     * @param int|null $size
     * @return void
     */
    public function replace(StreamInterface $content, $md5 = null, $sha1 = null, $size = null)
    {
        if (!$md5 || !$sha1 || !$size) {
            list($calculatedMd5, $calculatedSha1, $calculatedSize) = self::calculateHashesAndSize($content);
            $md5 = $md5 ?: $calculatedMd5;
            $sha1 = $sha1 ?: $calculatedSha1;
            $size = $size ?: $calculatedSize;
        }
        $this->content = $content;
        $this->md5 = $md5;
        $this->sha1 = $sha1;
        $this->size = $size;
    }
    private static function calculateHashesAndSize(StreamInterface $content) : array
    {
        $md5Context = \hash_init('md5');
        $sha1Context = \hash_init('sha1');
        $size = 0;
        while (!$content->eof()) {
            $buffer = $content->read(4096);
            \hash_update($md5Context, $buffer);
            \hash_update($sha1Context, $buffer);
            $size += \strlen($buffer);
        }
        $content->rewind();
        return [\hash_final($md5Context), \hash_final($sha1Context), $size];
    }
}
