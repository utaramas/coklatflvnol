<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
class Definition
{
    private $class;
    private $file;
    private $factory;
    private $shared = \true;
    private $deprecation = [];
    private $properties = [];
    private $calls = [];
    private $instanceof = [];
    private $autoconfigured = \false;
    private $configurator;
    private $tags = [];
    private $public = \false;
    private $synthetic = \false;
    private $abstract = \false;
    private $lazy = \false;
    private $decoratedService;
    private $autowired = \false;
    private $changes = [];
    private $bindings = [];
    private $errors = [];
    protected $arguments = [];
    private static $defaultDeprecationTemplate = 'The "%service_id%" service is deprecated. You should stop using it, as it will be removed in the future.';
    public $innerServiceId;
    public $decorationOnInvalid;
    public function __construct(string $class = null, array $arguments = [])
    {
        if (null !== $class) {
            $this->setClass($class);
        }
        $this->arguments = $arguments;
    }
    public function getChanges()
    {
        return $this->changes;
    }
    /**
     * @param mixed[] $changes
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;
        return $this;
    }
    public function setFactory($factory)
    {
        $this->changes['factory'] = \true;
        if (\is_string($factory) && \false !== \strpos($factory, '::')) {
            $factory = \explode('::', $factory, 2);
        } elseif ($factory instanceof Reference) {
            $factory = [$factory, '__invoke'];
        }
        $this->factory = $factory;
        return $this;
    }
    public function getFactory()
    {
        return $this->factory;
    }
    /**
     * @param string|null $id
     * @param string|null $renamedId
     * @param int $priority
     * @param int $invalidBehavior
     */
    public function setDecoratedService($id, $renamedId = null, $priority = 0, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if ($renamedId && $id === $renamedId) {
            throw new InvalidArgumentException(\sprintf('The decorated service inner name for "%s" must be different than the service name itself.', $id));
        }
        $this->changes['decorated_service'] = \true;
        if (null === $id) {
            $this->decoratedService = null;
        } else {
            $this->decoratedService = [$id, $renamedId, (int) $priority];
            if (ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE !== $invalidBehavior) {
                $this->decoratedService[] = $invalidBehavior;
            }
        }
        return $this;
    }
    public function getDecoratedService()
    {
        return $this->decoratedService;
    }
    /**
     * @param string|null $class
     */
    public function setClass($class)
    {
        $this->changes['class'] = \true;
        $this->class = $class;
        return $this;
    }
    public function getClass()
    {
        return $this->class;
    }
    /**
     * @param mixed[] $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
    /**
     * @param mixed[] $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }
    public function getProperties()
    {
        return $this->properties;
    }
    /**
     * @param string $name
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
    public function addArgument($argument)
    {
        $this->arguments[] = $argument;
        return $this;
    }
    public function replaceArgument($index, $argument)
    {
        if (0 === \count($this->arguments)) {
            throw new OutOfBoundsException('Cannot replace arguments if none have been configured yet.');
        }
        if (\is_int($index) && ($index < 0 || $index > \count($this->arguments) - 1)) {
            throw new OutOfBoundsException(\sprintf('The index "%d" is not in the range [0, %d].', $index, \count($this->arguments) - 1));
        }
        if (!\array_key_exists($index, $this->arguments)) {
            throw new OutOfBoundsException(\sprintf('The argument "%s" doesn\'t exist.', $index));
        }
        $this->arguments[$index] = $argument;
        return $this;
    }
    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;
        return $this;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function getArgument($index)
    {
        if (!\array_key_exists($index, $this->arguments)) {
            throw new OutOfBoundsException(\sprintf('The argument "%s" doesn\'t exist.', $index));
        }
        return $this->arguments[$index];
    }
    /**
     * @param mixed[] $calls
     */
    public function setMethodCalls($calls = [])
    {
        $this->calls = [];
        foreach ($calls as $call) {
            $this->addMethodCall($call[0], $call[1], $call[2] ?? \false);
        }
        return $this;
    }
    /**
     * @param string $method
     * @param mixed[] $arguments
     * @param bool $returnsClone
     */
    public function addMethodCall($method, $arguments = [], $returnsClone = \false)
    {
        if (empty($method)) {
            throw new InvalidArgumentException('Method name cannot be empty.');
        }
        $this->calls[] = $returnsClone ? [$method, $arguments, \true] : [$method, $arguments];
        return $this;
    }
    /**
     * @param string $method
     */
    public function removeMethodCall($method)
    {
        foreach ($this->calls as $i => $call) {
            if ($call[0] === $method) {
                unset($this->calls[$i]);
            }
        }
        return $this;
    }
    /**
     * @param string $method
     */
    public function hasMethodCall($method)
    {
        foreach ($this->calls as $call) {
            if ($call[0] === $method) {
                return \true;
            }
        }
        return \false;
    }
    public function getMethodCalls()
    {
        return $this->calls;
    }
    /**
     * @param mixed[] $instanceof
     */
    public function setInstanceofConditionals($instanceof)
    {
        $this->instanceof = $instanceof;
        return $this;
    }
    public function getInstanceofConditionals()
    {
        return $this->instanceof;
    }
    /**
     * @param bool $autoconfigured
     */
    public function setAutoconfigured($autoconfigured)
    {
        $this->changes['autoconfigured'] = \true;
        $this->autoconfigured = $autoconfigured;
        return $this;
    }
    public function isAutoconfigured()
    {
        return $this->autoconfigured;
    }
    /**
     * @param mixed[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }
    public function getTags()
    {
        return $this->tags;
    }
    /**
     * @param string $name
     */
    public function getTag($name)
    {
        return $this->tags[$name] ?? [];
    }
    /**
     * @param string $name
     * @param mixed[] $attributes
     */
    public function addTag($name, $attributes = [])
    {
        $this->tags[$name][] = $attributes;
        return $this;
    }
    /**
     * @param string $name
     */
    public function hasTag($name)
    {
        return isset($this->tags[$name]);
    }
    /**
     * @param string $name
     */
    public function clearTag($name)
    {
        unset($this->tags[$name]);
        return $this;
    }
    public function clearTags()
    {
        $this->tags = [];
        return $this;
    }
    /**
     * @param string|null $file
     */
    public function setFile($file)
    {
        $this->changes['file'] = \true;
        $this->file = $file;
        return $this;
    }
    public function getFile()
    {
        return $this->file;
    }
    /**
     * @param bool $shared
     */
    public function setShared($shared)
    {
        $this->changes['shared'] = \true;
        $this->shared = $shared;
        return $this;
    }
    public function isShared()
    {
        return $this->shared;
    }
    /**
     * @param bool $boolean
     */
    public function setPublic($boolean)
    {
        $this->changes['public'] = \true;
        $this->public = $boolean;
        return $this;
    }
    public function isPublic()
    {
        return $this->public;
    }
    /**
     * @param bool $boolean
     */
    public function setPrivate($boolean)
    {
        trigger_deprecation('symfony/dependency-injection', '5.2', 'The "%s()" method is deprecated, use "setPublic()" instead.', __METHOD__);
        return $this->setPublic(!$boolean);
    }
    public function isPrivate()
    {
        return !$this->public;
    }
    /**
     * @param bool $lazy
     */
    public function setLazy($lazy)
    {
        $this->changes['lazy'] = \true;
        $this->lazy = $lazy;
        return $this;
    }
    public function isLazy()
    {
        return $this->lazy;
    }
    /**
     * @param bool $boolean
     */
    public function setSynthetic($boolean)
    {
        $this->synthetic = $boolean;
        if (!isset($this->changes['public'])) {
            $this->setPublic(\true);
        }
        return $this;
    }
    public function isSynthetic()
    {
        return $this->synthetic;
    }
    /**
     * @param bool $boolean
     */
    public function setAbstract($boolean)
    {
        $this->abstract = $boolean;
        return $this;
    }
    public function isAbstract()
    {
        return $this->abstract;
    }
    public function setDeprecated()
    {
        $args = \func_get_args();
        if (\func_num_args() < 3) {
            trigger_deprecation('symfony/dependency-injection', '5.1', 'The signature of method "%s()" requires 3 arguments: "string $package, string $version, string $message", not defining them is deprecated.', __METHOD__);
            $status = $args[0] ?? \true;
            if (!$status) {
                trigger_deprecation('symfony/dependency-injection', '5.1', 'Passing a null message to un-deprecate a node is deprecated.');
            }
            $message = (string) ($args[1] ?? null);
            $package = $version = '';
        } else {
            $status = \true;
            $package = (string) $args[0];
            $version = (string) $args[1];
            $message = (string) $args[2];
        }
        if ('' !== $message) {
            if (\preg_match('#[\\r\\n]|\\*/#', $message)) {
                throw new InvalidArgumentException('Invalid characters found in deprecation template.');
            }
            if (\false === \strpos($message, '%service_id%')) {
                throw new InvalidArgumentException('The deprecation template must contain the "%service_id%" placeholder.');
            }
        }
        $this->changes['deprecated'] = \true;
        $this->deprecation = $status ? ['package' => $package, 'version' => $version, 'message' => $message ?: self::$defaultDeprecationTemplate] : [];
        return $this;
    }
    public function isDeprecated()
    {
        return (bool) $this->deprecation;
    }
    /**
     * @param string $id
     */
    public function getDeprecationMessage($id)
    {
        trigger_deprecation('symfony/dependency-injection', '5.1', 'The "%s()" method is deprecated, use "getDeprecation()" instead.', __METHOD__);
        return $this->getDeprecation($id)['message'];
    }
    /**
     * @param string $id
     */
    public function getDeprecation($id) : array
    {
        return ['package' => $this->deprecation['package'], 'version' => $this->deprecation['version'], 'message' => \str_replace('%service_id%', $id, $this->deprecation['message'])];
    }
    public function setConfigurator($configurator)
    {
        $this->changes['configurator'] = \true;
        if (\is_string($configurator) && \false !== \strpos($configurator, '::')) {
            $configurator = \explode('::', $configurator, 2);
        } elseif ($configurator instanceof Reference) {
            $configurator = [$configurator, '__invoke'];
        }
        $this->configurator = $configurator;
        return $this;
    }
    public function getConfigurator()
    {
        return $this->configurator;
    }
    public function isAutowired()
    {
        return $this->autowired;
    }
    /**
     * @param bool $autowired
     */
    public function setAutowired($autowired)
    {
        $this->changes['autowired'] = \true;
        $this->autowired = $autowired;
        return $this;
    }
    public function getBindings()
    {
        return $this->bindings;
    }
    /**
     * @param mixed[] $bindings
     */
    public function setBindings($bindings)
    {
        foreach ($bindings as $key => $binding) {
            if (0 < \strpos($key, '$') && $key !== ($k = \preg_replace('/[ \\t]*\\$/', ' $', $key))) {
                unset($bindings[$key]);
                $bindings[$key = $k] = $binding;
            }
            if (!$binding instanceof BoundArgument) {
                $bindings[$key] = new BoundArgument($binding);
            }
        }
        $this->bindings = $bindings;
        return $this;
    }
    public function addError($error)
    {
        if ($error instanceof self) {
            $this->errors = \array_merge($this->errors, $error->errors);
        } else {
            $this->errors[] = $error;
        }
        return $this;
    }
    public function getErrors()
    {
        foreach ($this->errors as $i => $error) {
            if ($error instanceof \Closure) {
                $this->errors[$i] = (string) $error();
            } elseif (!\is_string($error)) {
                $this->errors[$i] = (string) $error;
            }
        }
        return $this->errors;
    }
    public function hasErrors() : bool
    {
        return (bool) $this->errors;
    }
}
