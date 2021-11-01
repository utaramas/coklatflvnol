<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\ParameterBag;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\RuntimeException;
class EnvPlaceholderParameterBag extends ParameterBag
{
    private $envPlaceholderUniquePrefix;
    private $envPlaceholders = [];
    private $unusedEnvPlaceholders = [];
    private $providedTypes = [];
    private static $counter = 0;
    /**
     * @param string $name
     */
    public function get($name)
    {
        if (0 === \strpos($name, 'env(') && ')' === \substr($name, -1) && 'env()' !== $name) {
            $env = \substr($name, 4, -1);
            if (isset($this->envPlaceholders[$env])) {
                foreach ($this->envPlaceholders[$env] as $placeholder) {
                    return $placeholder;
                }
            }
            if (isset($this->unusedEnvPlaceholders[$env])) {
                foreach ($this->unusedEnvPlaceholders[$env] as $placeholder) {
                    return $placeholder;
                }
            }
            if (!\preg_match('/^(?:[-.\\w]*+:)*+\\w++$/', $env)) {
                throw new InvalidArgumentException(\sprintf('Invalid %s name: only "word" characters are allowed.', $name));
            }
            if ($this->has($name) && null !== ($defaultValue = parent::get($name)) && !\is_string($defaultValue)) {
                throw new RuntimeException(\sprintf('The default value of an env() parameter must be a string or null, but "%s" given to "%s".', \get_debug_type($defaultValue), $name));
            }
            $uniqueName = \md5($name . '_' . self::$counter++);
            $placeholder = \sprintf('%s_%s_%s', $this->getEnvPlaceholderUniquePrefix(), \strtr($env, ':-.', '___'), $uniqueName);
            $this->envPlaceholders[$env][$placeholder] = $placeholder;
            return $placeholder;
        }
        return parent::get($name);
    }
    public function getEnvPlaceholderUniquePrefix() : string
    {
        if (null === $this->envPlaceholderUniquePrefix) {
            $reproducibleEntropy = \unserialize(\serialize($this->parameters));
            \array_walk_recursive($reproducibleEntropy, function (&$v) {
                $v = null;
            });
            $this->envPlaceholderUniquePrefix = 'env_' . \substr(\md5(\serialize($reproducibleEntropy)), -16);
        }
        return $this->envPlaceholderUniquePrefix;
    }
    public function getEnvPlaceholders()
    {
        return $this->envPlaceholders;
    }
    public function getUnusedEnvPlaceholders() : array
    {
        return $this->unusedEnvPlaceholders;
    }
    public function clearUnusedEnvPlaceholders()
    {
        $this->unusedEnvPlaceholders = [];
    }
    /**
     * @param $this $bag
     */
    public function mergeEnvPlaceholders($bag)
    {
        if ($newPlaceholders = $bag->getEnvPlaceholders()) {
            $this->envPlaceholders += $newPlaceholders;
            foreach ($newPlaceholders as $env => $placeholders) {
                $this->envPlaceholders[$env] += $placeholders;
            }
        }
        if ($newUnusedPlaceholders = $bag->getUnusedEnvPlaceholders()) {
            $this->unusedEnvPlaceholders += $newUnusedPlaceholders;
            foreach ($newUnusedPlaceholders as $env => $placeholders) {
                $this->unusedEnvPlaceholders[$env] += $placeholders;
            }
        }
    }
    /**
     * @param mixed[] $providedTypes
     */
    public function setProvidedTypes($providedTypes)
    {
        $this->providedTypes = $providedTypes;
    }
    public function getProvidedTypes()
    {
        return $this->providedTypes;
    }
    public function resolve()
    {
        if ($this->resolved) {
            return;
        }
        parent::resolve();
        foreach ($this->envPlaceholders as $env => $placeholders) {
            if ($this->has($name = "env({$env})") && null !== ($default = $this->parameters[$name]) && !\is_string($default)) {
                throw new RuntimeException(\sprintf('The default value of env parameter "%s" must be a string or null, "%s" given.', $env, \get_debug_type($default)));
            }
        }
    }
}
