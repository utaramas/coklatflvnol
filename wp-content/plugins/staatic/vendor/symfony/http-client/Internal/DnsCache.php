<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Internal;

final class DnsCache
{
    public $hostnames = [];
    public $removals = [];
    public $evictions = [];
}
