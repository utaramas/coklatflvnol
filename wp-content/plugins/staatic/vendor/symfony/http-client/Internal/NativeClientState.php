<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Internal;

final class NativeClientState extends ClientState
{
    public $id;
    public $maxHostConnections = \PHP_INT_MAX;
    public $responseCount = 0;
    public $dnsCache = [];
    public $sleep = \false;
    public $hosts = [];
    public function __construct()
    {
        $this->id = \random_int(\PHP_INT_MIN, \PHP_INT_MAX);
    }
}
