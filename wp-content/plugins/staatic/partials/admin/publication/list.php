<?php

namespace Staatic\Vendor;

/**
 * @var Staatic\WordPress\ListTable\WpListTable $listTable
 */
global $title;
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php 
echo esc_html($title);
?></h1>
    <a href="<?php 
echo wp_nonce_url(admin_url('admin.php?page=staatic-publish'), 'staatic-publish');
?>" class="page-title-action"><?php 
_e('Publish now', 'staatic');
?></a>

    <hr class="wp-header-end">

    <?php 
$listTable->views();
?>

    <form id="builds-filter" method="get">
        <input type="hidden" name="page" value="staatic-publications">
        <input type="hidden" name="curview" value="<?php 
echo esc_html($listTable->get_view());
?>">

        <?php 
$listTable->search_box(__('Search publications', 'staatic'), 'publication');
?>
        <?php 
$listTable->display();
?>
    </form>

    <br class="clear">
</div>
<?php 
