<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Internal;

final class CurlClientState extends ClientState
{
    public $handle;
    public $pushedResponses = [];
    public $dnsCache;
    public $pauseExpiries = [];
    public $execCounter = \PHP_INT_MIN;
    public function __construct()
    {
        $this->handle = \curl_multi_init();
        $this->dnsCache = new DnsCache();
    }
}
