<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition\Exception;

class InvalidConfigurationException extends Exception
{
    private $path;
    private $containsHints = \false;
    public function setPath($path)
    {
        $this->path = $path;
    }
    public function getPath()
    {
        return $this->path;
    }
    /**
     * @param string $hint
     */
    public function addHint($hint)
    {
        if (!$this->containsHints) {
            $this->message .= "\nHint: " . $hint;
            $this->containsHints = \true;
        } else {
            $this->message .= ', ' . $hint;
        }
    }
}
