<?php

namespace Staatic\Vendor;

/**
 * @var \Staatic\WordPress\Service\Formatter $_formatter
 * @var string|null $publicationId
 */
?>

<div id="staatic-publication-status-widget">
    <?php 
if ($publicationId) {
    ?>
        <div id="staatic-publication-status" data-id="<?php 
    echo esc_attr($publicationId);
    ?>"></div>
        <p style="margin-bottom: 0">
            <a href="<?php 
    echo admin_url(\sprintf('admin.php?page=staatic-publication&id=%s', $publicationId));
    ?>">
                <?php 
    esc_html_e('Publication Details', 'staatic');
    ?>
            </a>
        </p>
    <?php 
} else {
    ?>
        <p><?php 
    esc_html_e('Nothing has been published yet.', 'staatic');
    ?></p>
    <?php 
}
?>
</div>
<?php 
