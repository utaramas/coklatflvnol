<?php

namespace Staatic\Vendor\AsyncAws\Core\Credentials;

use Staatic\Vendor\AsyncAws\Core\Result;
trait DateFromResult
{
    /**
     * @return \DateTimeImmutable|null
     */
    private function getDateFromResult(Result $result)
    {
        if (null !== ($response = $result->info()['response'] ?? null) && null !== ($date = $response->getHeaders(\false)['date'][0] ?? null)) {
            return new \DateTimeImmutable($date);
        }
        return null;
    }
}
