<?php

namespace Staatic\Vendor\AsyncAws\Core\Signer;

use Staatic\Vendor\AsyncAws\Core\Request;
class SigningContext
{
    private $request;
    private $now;
    private $credentialString;
    private $signingKey;
    private $signature = '';
    public function __construct(Request $request, \DateTimeImmutable $now, string $credentialString, string $signingKey)
    {
        $this->request = $request;
        $this->now = $now;
        $this->credentialString = $credentialString;
        $this->signingKey = $signingKey;
    }
    public function getRequest() : Request
    {
        return $this->request;
    }
    public function getNow() : \DateTimeImmutable
    {
        return $this->now;
    }
    public function getCredentialString() : string
    {
        return $this->credentialString;
    }
    public function getSigningKey() : string
    {
        return $this->signingKey;
    }
    public function getSignature() : string
    {
        return $this->signature;
    }
    /**
     * @param string $signature
     * @return void
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }
}
