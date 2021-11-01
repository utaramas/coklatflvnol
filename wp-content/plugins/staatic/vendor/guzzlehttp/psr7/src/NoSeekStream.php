<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleHttp\Psr7;

use Staatic\Vendor\Psr\Http\Message\StreamInterface;
final class NoSeekStream implements StreamInterface
{
    use StreamDecoratorTrait;
    /**
     * @return void
     */
    public function seek($offset, $whence = \SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a NoSeekStream');
    }
    public function isSeekable() : bool
    {
        return \false;
    }
}
