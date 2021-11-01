<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Exception;

use Staatic\Vendor\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
final class ClientException extends \RuntimeException implements ClientExceptionInterface
{
    use HttpExceptionTrait;
}
