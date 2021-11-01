<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Deployment;

use Staatic\WordPress\Module\Deployer\FilesystemDeployer\FilesystemDeployerModule;
use Staatic\WordPress\Setting\AbstractSetting;

final class DeploymentMethodSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_deployment_method';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    protected function template() : string
    {
        return 'select';
    }

    public function label() : string
    {
        return __('Deployment Method', 'staatic');
    }

    /**
     * @return string|null
     */
    public function extendedLabel()
    {
        return __('Deploy static site to', 'staatic');
    }

    /**
     * @return string|null
     */
    public function description()
    {
        return __('Choose how and where you want to publish the static version of your site.', 'staatic');
    }

    public function defaultValue()
    {
        return FilesystemDeployerModule::DEPLOYMENT_METHOD_NAME;
    }

    /**
     * @param mixed[] $attributes
     * @return void
     */
    public function render($attributes = [])
    {
        parent::render(\array_merge([
            'selectOptions' => $this->selectOptions()
        ], $attributes));
    }

    private function selectOptions() : array
    {
        $deploymentMethods = apply_filters('staatic_deployment_methods', [
            '' => ''
        ]);
        return $deploymentMethods;
    }
}
