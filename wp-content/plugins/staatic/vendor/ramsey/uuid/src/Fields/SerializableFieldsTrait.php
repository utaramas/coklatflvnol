<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Fields;

use function base64_decode;
use function strlen;
trait SerializableFieldsTrait
{
    public abstract function __construct(string $bytes);
    public abstract function getBytes() : string;
    public function serialize() : string
    {
        return $this->getBytes();
    }
    /**
     * @return void
     */
    public function unserialize($serialized)
    {
        if (strlen($serialized) === 16) {
            $this->__construct($serialized);
        } else {
            $this->__construct(base64_decode($serialized));
        }
    }
}
