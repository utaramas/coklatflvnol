<?php

namespace Staatic\Vendor;

/**
 * @var \Staatic\WordPress\Service\Formatter $_formatter
 *
 * @var array $groups
 * @var string|null $currentGroupName
 *
 * @var string $errors
 * @var string $hiddenFields
 * @var string $settings
 * @var bool $hasSettings
 */
?>

<?php 
echo $errors;
?>

<div class="wrap" id="staatic-settings">
    <h1 class="wp-heading-inline"><?php 
esc_html_e('Staatic Settings', 'staatic');
?></h1>
    <hr class="wp-header-end">

    <h2 class="nav-tab-wrapper">
        <?php 
foreach ($groups as $name => $group) {
    ?>
        <a
            class="nav-tab<?php 
    echo $name === $currentGroupName ? ' nav-tab-active' : '';
    echo $name === 'staatic-premium' ? ' staatic-premium' : '';
    ?>"
            href="<?php 
    echo admin_url('admin.php?page=staatic-settings&group=' . $name);
    ?>"
        >
            <?php 
    echo esc_html($group->label());
    ?>
        </a>
        <?php 
}
?>
    </h2>

    <form method="post" action="<?php 
echo admin_url('options.php');
?>">
        <?php 
echo $hiddenFields;
?>
        <?php 
echo $settings;
?>
        <?php 
if ($hasSettings) {
    ?>
            <?php 
    submit_button();
    ?>
        <?php 
}
?>
    </form>

</div>
<?php 
