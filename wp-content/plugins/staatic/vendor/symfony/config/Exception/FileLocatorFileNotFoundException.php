<?php

namespace Staatic\Vendor\Symfony\Component\Config\Exception;

class FileLocatorFileNotFoundException extends \InvalidArgumentException
{
    private $paths;
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, array $paths = [])
    {
        parent::__construct($message, $code, $previous);
        $this->paths = $paths;
    }
    public function getPaths()
    {
        return $this->paths;
    }
}
