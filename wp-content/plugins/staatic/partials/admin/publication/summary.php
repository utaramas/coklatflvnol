<?php

namespace Staatic\Vendor;

/**
 * @var Staatic\WordPress\Service\Formatter $_formatter
 * @var Staatic\WordPress\Publication\Publication $publication
 * @var Staatic\WordPress\Logging\LogEntry[]|array $logEntries
 * @var array $resultsPerStatusCategory
 */
$numFailedResults = $resultsPerStatusCategory[4] + $resultsPerStatusCategory[5];
$build = $publication->build();
$deployment = $publication->deployment();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php 
echo esc_html(\sprintf(
    /* translators: %s: Publication creation date. */
    __('Publication "%s"', 'staatic'),
    $_formatter->date($publication->dateCreated())
));
?></h1>
    <hr class="wp-header-end">

    <?php 
$currentTab = 'summary';
$this->render('admin/publication/_header.php', \compact('publication', 'currentTab'));
?>

    <h2><?php 
esc_html_e('Publication Summary', 'staatic');
?></h2>

    <table class="form-table staatic-dense" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><?php 
esc_html_e('Published By', 'staatic');
?></th>
                <td><?php 
echo $publication->userId() ? esc_html($publication->publisher()->data->display_name) : '<em>' . esc_html__('system', 'staatic') . '</em>';
?></td>
            </tr>
            <tr>
                <th scope="row"><?php 
esc_html_e('Status', 'staatic');
?></th>
                <td><?php 
echo esc_html($publication->status()->label());
?></td>
            </tr>
            <tr>
                <th scope="row"><?php 
esc_html_e('Time Taken', 'staatic');
?></th>
                <td>
                    <?php 
echo esc_html($_formatter->difference($publication->dateCreated(), $publication->dateFinished()));
?>
                    <?php 
if ($build->dateCrawlFinished()) {
    ?>
                        <small class="text-muted">
                            <?php 
    echo esc_html(\sprintf(
        /* translators: %1$s: Date interval for crawling, %2$s: Date interval for deployment. */
        __('(%1$s for crawling, %2$s for deployment)', 'staatic'),
        $_formatter->difference($build->dateCrawlStarted(), $build->dateCrawlFinished()),
        $_formatter->difference($deployment->dateStarted(), $deployment->dateFinished())
    ));
    ?>
                        </small>
                    <?php 
}
?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php 
esc_html_e('Resources', 'staatic');
?></th>
                <td>
                    <a href="<?php 
echo admin_url('admin.php?page=staatic-publication-results&id=' . $publication->id());
?>">
                        <?php 
echo esc_html(\sprintf(
    /* translators: %s: Number of resources. */
    __('%s resources', 'staatic'),
    $_formatter->number(\array_sum($resultsPerStatusCategory))
));
?></a>
                    <small class="text-muted">
                        <?php 
echo wp_kses_post(\sprintf(
    /* translators: %1$s: Number of resources, %2$s: Redirect/redirects, %3$s: Number of failed resources, %4$s: Failure/failures. */
    __('(%1$s %2$s, %3$s %4$s)', 'staatic'),
    $_formatter->number($resultsPerStatusCategory[3]),
    $resultsPerStatusCategory[3] === 1 ? __('redirect', 'staatic') : __('redirects', 'staatic'),
    \sprintf($numFailedResults > 0 ? '<b>%s</b>' : '%s', $_formatter->number($numFailedResults)),
    $numFailedResults === 1 ? __('failure', 'staatic') : __('failures', 'staatic')
));
?>
                    </small>
                </td>
            </tr>
        </tbody>
    </table>

    <h2><?php 
esc_html_e('Log Summary', 'staatic');
?></h2>

    <?php 
if (\count($logEntries) > 0) {
    ?>
        <ul class="staatic-log-summary">
            <?php 
    foreach ($logEntries as $logEntry) {
        ?>
                <?php 
        $source = $logEntry->context() && isset($logEntry->context()['source']) ? $logEntry->context()['source'] : null;
        ?>
                <li class="staatic-log-level-<?php 
        echo esc_attr($logEntry->level());
        ?>">
                    <?php 
        echo esc_html($_formatter->date($logEntry->date()));
        ?> -
                    <?php 
        echo $source ? esc_html($source) . ': ' : '';
        ?>
                    <?php 
        echo esc_html($logEntry->message());
        ?>
                </li>
            <?php 
    }
    ?>
        </ul>
    <?php 
} else {
    ?>
        <p><?php 
    esc_html_e('No logs for this publication (yet)', 'staatic');
    ?></p>
    <?php 
}
?>

    <p>
        <a href="<?php 
echo admin_url('admin.php?page=staatic-publication-logs&id=' . $publication->id());
?>">
            <?php 
esc_html_e('View all logs', 'staatic');
?>
        </a>
    </p>

    <br class="clear">
</div>
<?php 
