<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

use Staatic\WordPress\Setting\ComposedSettingInterface;
use Staatic\WordPress\Setting\SettingInterface;
use Staatic\WordPress\SettingGroup\SettingGroupInterface;

final class Settings
{
    /** @var SettingGroupInterface[] */
    private $groups = [];

    /** @var SettingInterface[] */
    private $settings = [];

    /**
     * @var mixed[]
     */
    private $settingsToGroups = [];

    /**
     * @return void
     */
    public function addGroup(SettingGroupInterface $group)
    {
        // Allow replace, so no checking whether group exists...
        $this->groups[$group->name()] = $group;
        \uasort($this->groups, function (SettingGroupInterface $a, SettingGroupInterface $b) {
            return $a->position() <=> $b->position();
        });
    }

    /**
     * @return void
     */
    public function addSetting(string $groupName, SettingInterface $setting)
    {
        $this->settings[$setting->name()] = $setting;
        $this->settingsToGroups[$setting->name()] = $groupName;
    }

    /**
     * @return void
     */
    public function registerSettings()
    {
        foreach ($this->settings as $settingName => $setting) {
            $groupName = $this->settingsToGroups[$settingName];
            if ($setting instanceof ComposedSettingInterface) {
                $settings = $setting->settings();
            } else {
                $settings = [$setting];
            }
            foreach ($settings as $innerSetting) {
                register_setting($groupName, $innerSetting->name(), [
                    'type' => $innerSetting->type(),
                    'description' => $innerSetting->description(),
                    'sanitize_callback' => [$innerSetting, 'sanitizeValue'],
                    'default' => $innerSetting->defaultValue()
                ]);
                if (\method_exists($innerSetting, 'onUpdate')) {
                    add_action('add_option_' . $innerSetting->name(), function ($option, $value) use ($innerSetting) {
                        $innerSetting->onUpdate($value, null);
                    }, 10, 2);
                    add_action('update_option_' . $innerSetting->name(), function ($oldValue, $value, $option) use (
                        $innerSetting
                    ) {
                        $innerSetting->onUpdate($value, $oldValue);
                    }, 10, 3);
                }
            }
        }
    }

    /**
     * @return SettingGroupInterface[]
     */
    public function groups() : array
    {
        return $this->groups;
    }

    public function group(string $name) : SettingGroupInterface
    {
        if (!isset($this->groups[$name])) {
            throw new \InvalidArgumentException(\sprintf('Setting group "%s" does not exist', $name));
        }
        return $this->groups[$name];
    }

    /**
     * @return SettingInterface[]
     * @param string|null $groupName
     */
    public function settings($groupName = null) : array
    {
        $settings = $this->settings;
        if ($groupName) {
            $settings = \array_filter($settings, function (SettingInterface $setting) use ($groupName) {
                return $this->settingsToGroups[$setting->name()] === $groupName;
            });
        }
        return $settings;
    }

    /**
     * @return void
     */
    public function settingsApiInit()
    {
        foreach ($this->groups as $groupName => $group) {
            $groupPageId = \sprintf('%s-settings-page', $groupName);
            $groupSectionId = \sprintf('%s-settings-section', $groupName);
            add_settings_section(
                $groupSectionId,
                '',
                // $groupLabel,
                [$group, 'render'],
                $groupPageId
            );
            foreach ($this->settings($groupName) as $setting) {
                add_settings_field(
                    $setting->name(),
                    $setting->label(),
                    [$setting, 'render'],
                    $groupPageId,
                    $groupSectionId,
                    [
                    'class' => \sprintf('%s %s', $groupName, $setting->name())
                
                ]);
            }
        }
    }

    public function renderErrors() : string
    {
        \ob_start();
        settings_errors('staatic-settings');
        $errors = \ob_get_clean();
        return $errors;
    }

    public function renderHiddenFields(string $groupName) : string
    {
        \ob_start();
        settings_fields($groupName);
        $hiddenFields = \ob_get_clean();
        return $hiddenFields;
    }

    public function renderSettings(string $groupName) : string
    {
        $groupPageId = \sprintf('%s-settings-page', $groupName);
        \ob_start();
        do_settings_sections($groupPageId);
        $settings = \ob_get_clean();
        return $settings;
    }

    public function hasSettings(string $groupName) : bool
    {
        $groupsWithSettings = \array_unique(\array_values($this->settingsToGroups));
        return \in_array($groupName, $groupsWithSettings);
    }
}
