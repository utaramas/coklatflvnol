<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Exception;

use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
final class EventSourceException extends \RuntimeException implements DecodingExceptionInterface
{
}
