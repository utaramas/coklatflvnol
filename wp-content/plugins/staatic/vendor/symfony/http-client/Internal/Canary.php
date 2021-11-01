<?php

namespace Staatic\Vendor\Symfony\Component\HttpClient\Internal;

final class Canary
{
    private $canceller;
    public function __construct(\Closure $canceller)
    {
        $this->canceller = $canceller;
    }
    public function cancel()
    {
        if (($canceller = $this->canceller) instanceof \Closure) {
            $this->canceller = null;
            $canceller();
        }
    }
    public function __destruct()
    {
        $this->cancel();
    }
}
