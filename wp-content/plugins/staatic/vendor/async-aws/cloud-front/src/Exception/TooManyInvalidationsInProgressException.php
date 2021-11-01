<?php

namespace Staatic\Vendor\AsyncAws\CloudFront\Exception;

use Staatic\Vendor\AsyncAws\Core\Exception\Http\ClientException;
use Staatic\Vendor\Symfony\Contracts\HttpClient\ResponseInterface;
final class TooManyInvalidationsInProgressException extends ClientException
{
    /**
     * @param ResponseInterface $response
     * @return void
     */
    protected function populateResult($response)
    {
        $data = new \SimpleXMLElement($response->getContent(\false));
        if (0 < $data->Error->count()) {
            $data = $data->Error;
        }
        if (null !== ($v = ($v = $data->message) ? (string) $v : null)) {
            $this->message = $v;
        }
    }
}
