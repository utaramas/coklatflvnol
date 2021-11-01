<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Compiler;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
class PassConfig
{
    const TYPE_AFTER_REMOVING = 'afterRemoving';
    const TYPE_BEFORE_OPTIMIZATION = 'beforeOptimization';
    const TYPE_BEFORE_REMOVING = 'beforeRemoving';
    const TYPE_OPTIMIZE = 'optimization';
    const TYPE_REMOVE = 'removing';
    private $mergePass;
    private $afterRemovingPasses = [];
    private $beforeOptimizationPasses = [];
    private $beforeRemovingPasses = [];
    private $optimizationPasses;
    private $removingPasses;
    public function __construct()
    {
        $this->mergePass = new MergeExtensionConfigurationPass();
        $this->beforeOptimizationPasses = [100 => [new ResolveClassPass(), new ResolveInstanceofConditionalsPass(), new RegisterEnvVarProcessorsPass()], -1000 => [new ExtensionCompilerPass()]];
        $this->optimizationPasses = [[new AutoAliasServicePass(), new ValidateEnvPlaceholdersPass(), new ResolveDecoratorStackPass(), new ResolveChildDefinitionsPass(), new RegisterServiceSubscribersPass(), new ResolveParameterPlaceHoldersPass(\false, \false), new ResolveFactoryClassPass(), new ResolveNamedArgumentsPass(), new AutowireRequiredMethodsPass(), new AutowireRequiredPropertiesPass(), new ResolveBindingsPass(), new ServiceLocatorTagPass(), new DecoratorServicePass(), new CheckDefinitionValidityPass(), new AutowirePass(\false), new ResolveTaggedIteratorArgumentPass(), new ResolveServiceSubscribersPass(), new ResolveReferencesToAliasesPass(), new ResolveInvalidReferencesPass(), new AnalyzeServiceReferencesPass(\true), new CheckCircularReferencesPass(), new CheckReferenceValidityPass(), new CheckArgumentsValidityPass(\false)]];
        $this->removingPasses = [[new RemovePrivateAliasesPass(), new ReplaceAliasByActualDefinitionPass(), new RemoveAbstractDefinitionsPass(), new RemoveUnusedDefinitionsPass(), new AnalyzeServiceReferencesPass(), new CheckExceptionOnInvalidReferenceBehaviorPass(), new InlineServiceDefinitionsPass(new AnalyzeServiceReferencesPass()), new AnalyzeServiceReferencesPass(), new DefinitionErrorExceptionPass()]];
        $this->afterRemovingPasses = [[new ResolveHotPathPass(), new ResolveNoPreloadPass(), new AliasDeprecatedPublicServicesPass()]];
    }
    public function getPasses()
    {
        return \array_merge([$this->mergePass], $this->getBeforeOptimizationPasses(), $this->getOptimizationPasses(), $this->getBeforeRemovingPasses(), $this->getRemovingPasses(), $this->getAfterRemovingPasses());
    }
    /**
     * @param CompilerPassInterface $pass
     * @param string $type
     * @param int $priority
     */
    public function addPass($pass, $type = self::TYPE_BEFORE_OPTIMIZATION, $priority = 0)
    {
        $property = $type . 'Passes';
        if (!isset($this->{$property})) {
            throw new InvalidArgumentException(\sprintf('Invalid type "%s".', $type));
        }
        $passes =& $this->{$property};
        if (!isset($passes[$priority])) {
            $passes[$priority] = [];
        }
        $passes[$priority][] = $pass;
    }
    public function getAfterRemovingPasses()
    {
        return $this->sortPasses($this->afterRemovingPasses);
    }
    public function getBeforeOptimizationPasses()
    {
        return $this->sortPasses($this->beforeOptimizationPasses);
    }
    public function getBeforeRemovingPasses()
    {
        return $this->sortPasses($this->beforeRemovingPasses);
    }
    public function getOptimizationPasses()
    {
        return $this->sortPasses($this->optimizationPasses);
    }
    public function getRemovingPasses()
    {
        return $this->sortPasses($this->removingPasses);
    }
    public function getMergePass()
    {
        return $this->mergePass;
    }
    /**
     * @param CompilerPassInterface $pass
     */
    public function setMergePass($pass)
    {
        $this->mergePass = $pass;
    }
    /**
     * @param mixed[] $passes
     */
    public function setAfterRemovingPasses($passes)
    {
        $this->afterRemovingPasses = [$passes];
    }
    /**
     * @param mixed[] $passes
     */
    public function setBeforeOptimizationPasses($passes)
    {
        $this->beforeOptimizationPasses = [$passes];
    }
    /**
     * @param mixed[] $passes
     */
    public function setBeforeRemovingPasses($passes)
    {
        $this->beforeRemovingPasses = [$passes];
    }
    /**
     * @param mixed[] $passes
     */
    public function setOptimizationPasses($passes)
    {
        $this->optimizationPasses = [$passes];
    }
    /**
     * @param mixed[] $passes
     */
    public function setRemovingPasses($passes)
    {
        $this->removingPasses = [$passes];
    }
    private function sortPasses(array $passes) : array
    {
        if (0 === \count($passes)) {
            return [];
        }
        \krsort($passes);
        return \array_merge(...$passes);
    }
}
