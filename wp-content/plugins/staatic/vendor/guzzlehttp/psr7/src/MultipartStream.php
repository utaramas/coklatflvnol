<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleHttp\Psr7;

use Staatic\Vendor\Psr\Http\Message\StreamInterface;
final class MultipartStream implements StreamInterface
{
    use StreamDecoratorTrait;
    private $boundary;
    public function __construct(array $elements = [], string $boundary = null)
    {
        $this->boundary = $boundary ?: \sha1(\uniqid('', \true));
        $this->stream = $this->createStream($elements);
    }
    public function getBoundary() : string
    {
        return $this->boundary;
    }
    public function isWritable() : bool
    {
        return \false;
    }
    private function getHeaders(array $headers) : string
    {
        $str = '';
        foreach ($headers as $key => $value) {
            $str .= "{$key}: {$value}\r\n";
        }
        return "--{$this->boundary}\r\n" . \trim($str) . "\r\n\r\n";
    }
    /**
     * @param mixed[] $elements
     */
    protected function createStream($elements = []) : StreamInterface
    {
        $stream = new AppendStream();
        foreach ($elements as $element) {
            $this->addElement($stream, $element);
        }
        $stream->addStream(Utils::streamFor("--{$this->boundary}--\r\n"));
        return $stream;
    }
    /**
     * @return void
     */
    private function addElement(AppendStream $stream, array $element)
    {
        foreach (['contents', 'name'] as $key) {
            if (!\array_key_exists($key, $element)) {
                throw new \InvalidArgumentException("A '{$key}' key is required");
            }
        }
        $element['contents'] = Utils::streamFor($element['contents']);
        if (empty($element['filename'])) {
            $uri = $element['contents']->getMetadata('uri');
            if (\substr($uri, 0, 6) !== 'php://') {
                $element['filename'] = $uri;
            }
        }
        list($body, $headers) = $this->createElement($element['name'], $element['contents'], $element['filename'] ?? null, $element['headers'] ?? []);
        $stream->addStream(Utils::streamFor($this->getHeaders($headers)));
        $stream->addStream($body);
        $stream->addStream(Utils::streamFor("\r\n"));
    }
    /**
     * @param string|null $filename
     */
    private function createElement(string $name, StreamInterface $stream, $filename, array $headers) : array
    {
        $disposition = $this->getHeader($headers, 'content-disposition');
        if (!$disposition) {
            $headers['Content-Disposition'] = $filename === '0' || $filename ? \sprintf('form-data; name="%s"; filename="%s"', $name, \basename($filename)) : "form-data; name=\"{$name}\"";
        }
        $length = $this->getHeader($headers, 'content-length');
        if (!$length) {
            if ($length = $stream->getSize()) {
                $headers['Content-Length'] = (string) $length;
            }
        }
        $type = $this->getHeader($headers, 'content-type');
        if (!$type && ($filename === '0' || $filename)) {
            if ($type = MimeType::fromFilename($filename)) {
                $headers['Content-Type'] = $type;
            }
        }
        return [$stream, $headers];
    }
    private function getHeader(array $headers, string $key)
    {
        $lowercaseHeader = \strtolower($key);
        foreach ($headers as $k => $v) {
            if (\strtolower($k) === $lowercaseHeader) {
                return $v;
            }
        }
        return null;
    }
}
