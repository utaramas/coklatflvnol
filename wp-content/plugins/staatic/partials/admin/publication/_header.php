<?php

namespace Staatic\Vendor;

/**
 * @var Staatic\WordPress\Service\Formatter $_formatter
 * @var Staatic\WordPress\Publication\Publication $publication
 */
$tabs = ['summary' => ['label' => __('Summary', 'staatic'), 'url' => admin_url('admin.php?page=staatic-publication&id=' . $publication->id())], 'results' => ['label' => __('Resources', 'staatic'), 'url' => admin_url('admin.php?page=staatic-publication-results&id=' . $publication->id())], 'logs' => ['label' => __('Logs', 'staatic'), 'url' => admin_url('admin.php?page=staatic-publication-logs&id=' . $publication->id())]];
?>

<?php 
if ($publication->status()->isInProgress()) {
    ?>
    <div id="staatic-publication-status-container">
        <h2><?php 
    esc_html_e('Live Publication Status', 'staatic');
    ?></h2>
        <div id="staatic-publication-status" data-id="<?php 
    echo esc_attr($publication->id());
    ?>" style="margin-bottom: 1em;"></div>
    </div>
<?php 
}
?>

<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
    <?php 
foreach ($tabs as $tabName => $tab) {
    ?>
        <a
            href="<?php 
    echo esc_url($tab['url']);
    ?>"
            class="nav-tab<?php 
    if ($currentTab === $tabName) {
        ?> nav-tab-active<?php 
    }
    ?>"
            aria-current="page"
        >
            <?php 
    echo esc_html($tab['label']);
    ?>
        </a>
    <?php 
}
?>
</nav>

<?php 
if ($publication->status()->isInProgress()) {
    ?>
    <div class="manage-menus"> <!-- class -->
        <span class="first-menu-message">
            <?php 
    esc_html_e('Publication is currently in progress. Refresh the page to see the latest information.', 'staatic');
    ?>
        </span>
    </div>
<?php 
}
