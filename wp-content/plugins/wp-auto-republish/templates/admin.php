<?php

/**
 * The Main dashboard file.
 *
 * @since      1.1.0
 * @package    WP Auto Republish
 * @subpackage Templates
 * @author     Sayan Datta <hello@sayandatta.in>
 */
?>

<div class="wrap">
    <div class="head-wrap">
        <h1 class="title"><?php 
echo  $this->name ;
?><span class="title-count"><?php 
echo  $this->version ;
?></span></h1>
        <div><?php 
_e( 'This plugin helps to revive old posts by resetting the publish date to the current date.', 'wp-auto-republish' );
?></div>
        <div class="top-sharebar">
            <a class="share-btn rate-btn" href="https://wordpress.org/support/plugin/wp-auto-republish/reviews/#new-post" target="_blank" title="Please rate 5 stars if you like <?php 
echo  $this->name ;
?>"><span class="dashicons dashicons-star-filled"></span> Rate 5 stars</a>
            <a class="share-btn twitter" href="https://twitter.com/intent/tweet?text=Check%20out%20WP%20Auto%20Republish,%20a%20%23WordPress%20%23plugin%20that%20revive%20your%20old%20posts%20by%20resetting%20the%20published%20date%20to%20the%20current%20date%20https%3A//wordpress.org/plugins/wp-auto-republish/%20via%20%40im_sayaan" target="_blank"><span class="dashicons dashicons-twitter"></span> <?php 
_e( 'Share on Twitter', 'wp-auto-republish' );
?></a>
            <a class="share-btn facebook" href="https://www.facebook.com/sharer/sharer.php?u=https://wordpress.org/plugins/wp-auto-republish/" target="_blank"><span class="dashicons dashicons-facebook"></span> <?php 
_e( 'Share on Facebook', 'wp-auto-republish' );
?></a>
        </div>
    </div>
    <div id="nav-container" class="nav-tab-wrapper" style="border-bottggom: none;">
        <a href="#general" class="nav-tab nav-tab-active" id="general"><span class="dashicons dashicons-admin-generic"></span> <?php 
_e( 'General', 'wp-auto-republish' );
?></a>
        <a href="#post" class="nav-tab" id="post"><span class="dashicons dashicons-admin-post"></span> <?php 
_e( 'Post Options', 'wp-auto-republish' );
?></a>
        <?php 
?>
        <a href="#tools" class="nav-tab" id="tools"><span class="dashicons dashicons-admin-tools"></span> <?php 
_e( 'Tools', 'wp-auto-republish' );
?></a>
        <a href="#help" class="nav-tab" id="help"><span class="dashicons dashicons-editor-help"></span> <?php 
_e( 'Help', 'wp-auto-republish' );
?></a>
        <?php 
?>
            <a href="<?php 
echo  wpar_load_fs_sdk()->get_upgrade_url() ;
?>" target="_blank" class="nav-tab" id="upgrade"><span class="dashicons dashicons-arrow-up-alt"></span> <?php 
_e( 'Upgrade', 'wp-auto-republish' );
?></a>
        <?php 
?>
    </div>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content" class="wpar-metabox">
                <form id="wpar-settings-form" method="post" action="options.php" stylce="padding-left: 8px;">
                <?php 
settings_fields( 'wpar_plugin_settings_fields' );
?>
                    <div id="wpar-configure" class="postbox wpar-general">
                        <h3 class="hndle" style="cursor:default;">
                            <span class="wpar-heading">
                                <?php 
_e( 'Configure Settings', 'wp-auto-republish' );
?>
                            </span>
                        </h3>
                        <div class="inside">
                            <?php 
do_settings_sections( 'wpar_plugin_default_option' );
?>
                            <p><?php 
submit_button(
    __( 'Save Settings', 'wp-auto-republish' ),
    'primary wpar-save',
    '',
    false
);
?></p>
                        </div>
                    </div>
                    <div id="wpar-display" class="postbox wpar-general">
                        <h3 class="hndle" style="cursor:default;">
                            <span class="wpar-heading">
                                <?php 
_e( 'Display Settings', 'wp-auto-republish' );
?>
                            </span>
                        </h3>
                        <div class="inside">
                            <?php 
do_settings_sections( 'wpar_plugin_republish_info_option' );
?>
                            <p><?php 
submit_button(
    __( 'Save Settings', 'wp-auto-republish' ),
    'primary wpar-save',
    '',
    false
);
?></p>
                        </div>
                    </div>
                    <div id="wpar-query" class="postbox wpar-post" style="display: none;">
                        <h3 class="hndle" style="cursor:default;">
                            <span class="wpar-heading">
                                <?php 
_e( 'Old Posts Settings', 'wp-auto-republish' );
?>
                            </span>
                        </h3>
                        <div class="inside">
                            <?php 
do_settings_sections( 'wpar_plugin_post_query_option' );
?>
                            <p><?php 
submit_button(
    __( 'Save Settings', 'wp-auto-republish' ),
    'primary wpar-save',
    '',
    false
);
?></p>
                        </div>
                    </div>
                    <div id="wpar-post-types" class="postbox wpar-post" style="display: none;">
                        <h3 class="hndle" style="cursor:default;">
                            <span class="wpar-heading">
                                <?php 
_e( 'Post Types Settings', 'wp-auto-republish' );
?>
                            </span>
                        </h3>
                        <div class="inside">
                            <?php 
do_settings_sections( 'wpar_plugin_post_type_option' );
?>
                            <p><?php 
submit_button(
    __( 'Save Settings', 'wp-auto-republish' ),
    'primary wpar-save',
    '',
    false
);
?></p>
                        </div>
                    </div>
                    <?php 
?>
                    <div id="wpar-misc" class="postbox wpar-tools" style="display: none;">
                        <h3 class="hndle" style="cursor:default;">
                            <span class="wpar-heading">
                                <?php 
_e( 'Misc. Options', 'wp-auto-republish' );
?>
                            </span>
                        </h3>
                        <div class="inside">
                            <?php 
do_settings_sections( 'wpar_plugin_tools_option' );
?>
                            <p><?php 
submit_button(
    __( 'Save Settings', 'wp-auto-republish' ),
    'primary wpar-save',
    '',
    false
);
?></p>
                        </div>
                    </div>
                </form>
                <?php 
?>
                <div id="wpar-tools" class="postbox wpar-tools" style="display: none;">
				    <h3 class="hndle" style="cursor:default;">
                        <span class="wpar-heading">
                            <?php 
_e( 'Plugin Tools', 'wp-auto-republish' );
?>
                        </span>
                    </h3>
				    <div class="inside wpar-inside" style="padding: 10px 20px;">
                        <div>
                            <span><strong><?php 
_e( 'Export Settings', 'wp-auto-republish' );
?></strong></span>
		    	        	<p><?php 
_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'wp-auto-republish' );
?></p>
		    	        	<form method="post">
		    	        		<p><input type="hidden" name="wpar_export_action" value="wpar_export_settings" /></p>
		    	        		<p>
		    	        			<?php 
wp_nonce_field( 'wpar_export_nonce', 'wpar_export_nonce' );
?>
		    	        			<?php 
submit_button(
    __( 'Export Settings', 'wp-auto-republish' ),
    'secondary',
    'wpar-export',
    false
);
?>
                                    <input type="button" class="button wpar-copy" value="<?php 
_e( 'Copy', 'wp-auto-republish' );
?>" style="margin-left: -1px;">
                                    <span class="wpar-copied" style="padding-left: 6px;display: none;color: #068611;"><?php 
_e( 'Copied!', 'wp-auto-republish' );
?></span>
                                </p>
		    	        	</form>
                        </div><hr>
                        <div>
                            <span><strong><?php 
_e( 'Import Settings', 'wp-auto-republish' );
?></strong></span>
		    	        	<p><?php 
_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'wp-auto-republish' );
?></p>
		    	        	<form method="post" enctype="multipart/form-data">
		    	        		<p><input type="file" name="import_file" accept=".json"/></p>
		    	        		<p>
		    	        			<input type="hidden" name="wpar_import_action" value="wpar_import_settings" />
		    	        			<?php 
wp_nonce_field( 'wpar_import_nonce', 'wpar_import_nonce' );
?>
		    	        			<?php 
submit_button(
    __( 'Import Settings', 'wp-auto-republish' ),
    'secondary',
    'wpar-import',
    false
);
?>
                                    <input type="button" class="button wpar-paste" value="<?php 
_e( 'Paste', 'wp-auto-republish' );
?>">
                                </p>
		    	        	</form>
                        </div><hr>
                        <div>
                            <span><strong><?php 
_e( 'Reset Settings', 'wp-auto-republish' );
?></strong></span>
		    	        	<p style="color: #ff0000;"><strong><?php 
_e( 'WARNING:', 'wp-auto-republish' );
?> </strong><?php 
_e( 'Resetting will delete all custom options to the default settings of the plugin in your database.', 'wp-auto-republish' );
?></p>
		    	        	<input type="button" class="button button-primary wpar-reset" data-action="wpar_process_delete_plugin_data" data-reload="true" data-notice="<?php 
_e( 'It will delete all the data relating to this plugin settings. You have to re-configure this plugin again. Do you want to still continue?', 'wp-auto-republish' );
?>" data-success="<?php 
_e( 'Success! Plugin Settings reset successfully.', 'wp-auto-republish' );
?>" value="<?php 
_e( 'Reset Settings', 'wp-auto-republish' );
?>">
                            <?php 
?></p>
                        </div>
                    </div>
                </div>
                <div id="wpar-help" class="postbox wpar-help" style="display: none;">
                        <h3 class="hndle" style="cursor:default;">
                            <span class="wpar-heading">
                                <?php 
_e( 'Plugin Help', 'wp-auto-republish' );
?>
                            </span>
                        </h3>
                        <div class="inside">
                            <h2><?php 
_e( 'Do you need help with this plugin? Here are some FAQ for you:', 'wp-auto-republish' );
?></h2>
                            <ol class="help-faq">
                                <li><?php 
printf( __( 'How this %s plugin works?', 'wp-auto-republish' ), $this->name );
?></li>
                                <p><?php 
_e( 'This plugin is mainly based on WordPress Cron system to republish your old evergreen posts. It will generate republish events when plugin is instructed to republish a post. It is designed in a way to easily work with any server enviroment. If still it not works, please contact your hosting provider to increase server resources.', 'wp-auto-republish' );
?></p>
                            
                                <li><?php 
_e( 'WordPress Cron is disabled on my website. What can I do?', 'wp-auto-republish' );
?></li>
                                <p><?php 
printf( __( 'This plugin is heavily based on WP Cron. If it is disabled on your website which is required by %1$s plugin, please enable native WP Cron or follow this <a href="%2$s" target="_blank">tutorial</a> to enable server level PHP Cron instead with an interval of less than Republish Interval option.', 'wp-auto-republish' ), $this->name, 'https://www.siteground.com/tutorials/wordpress/real-cron-job/' );
?></p>
                                     
                                <?php 

if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON !== false ) {
    ?>
                                    <div class="update-message notice inline notice-warning notice-alt">
                                        <p class="cron-warning"><?php 
    _e( 'Native WordPress Cron is currentlly disabled on your website. Please enable it or follow the upper mentioned tutorial.', 'wp-auto-republish' );
    ?></p>
                                    </div>
                                <?php 
}

?>

                                <li><?php 
_e( 'WordPress Cron is also enabled but still the plugin is not working properly. What to do next?', 'wp-auto-republish' );
?></li>
                                <p><?php 
printf( __( 'Please install WP Crontrol plugin and go to Tools > Cron Events. It should show a error notice there. You have to enable the WordPress alternate cron method, to get it properly woking. Just paste this line %1$s after the lines containing your database credentials in %2$s file. After that you can delete the WP Crontorl plugin.', 'wp-auto-republish' ), '<code>define(&#39;ALTERNATE_WP_CRON&#39;, true);</code>', 'wp-config.php' );
?></p>
                            
                                <li><?php 
_e( 'Plugin sometimes fails or misses to republish a particular post at a specified time. What is the reason?', 'wp-auto-republish' );
?></li>
                                <p><?php 
printf( __( 'This plugin is based on WP Cron which depends on the traffic volume of your website. If you have low traffic, there may be chances to miss any republish job. To avoid this, please disable native WP Cron and follow this <a href="%s" target="_blank">tutorial</a> to enable server level PHP Cron instead with an interval of less than Republish Interval option.', 'wp-auto-republish' ), 'https://www.siteground.com/tutorials/wordpress/real-cron-job/' );
?></p>
                            
                                <li><?php 
_e( 'Doesn’t changing the timestamp affect permalinks that include dates using this plugin?', 'wp-auto-republish' );
?></li>
                                <p><?php 
printf( __( 'If your permalinks structure contains date, please use %1$s instead of %2$s respectively if you are using premium version. If you are using free version then please disable this plugin or upgrade to Premium version to avoid SEO issues.', 'wp-auto-republish' ), '<code>%wpar_year%</code>, <code>%wpar_monthnum%</code>, <code>%wpar_day%</code>, <code>%wpar_hour%</code>, <code>%wpar_minute%</code>, <code>%wpar_second%</code>', '<code>%year%</code>, <code>%monthnum%</code>, <code>%day%</code>, <code>%hour%</code>, <code>%minute%</code>, <code>%second%</code>' );
?></p>

                                <?php 
?>
                            
                                <li><?php 
_e( 'I have some custom taxonomies associated with posts or pages or any custom post types but they are not showing on settings dropdown. OR, Somehow custom post types republishing are now stopped suddenly. What to do next?', 'wp-auto-republish' );
?></li>
                                <p><?php 
_e( 'Free version of this plugin has some limitation. You can republish a particular post of a custom post type upto 3 times. After that plugin doesn\'t republish those posts anymore. You have to use Premium version of this plugin to use it more than 3 times for custom post types. Also, Post and Page do not have such limitations in the free version. Taxonomies, other than Category and Post Tags, are available only on premium version.', 'wp-auto-republish' );
?></p>
                            
                                <li><?php 
_e( 'I am using GoDaddy managed hosting and plugin is not working properly. What to do next?', 'wp-auto-republish' );
?></li>
                                <p><?php 
_e( 'As GoDaddy Managed Hosting does not allow to use server level cron, you have to use WordPress default cron or alternate cron method, to get it properly woking. Just follow the FAQ no. 3. Otherwise you can use other external cron services to solve this issue.', 'wp-auto-republish' );
?></p>
                            
                                <li><?php 
_e( 'I have just installed this plugin and followed all previous guides but still it is not working properly. What to do?', 'wp-auto-republish' );
?></li>
                                <p><?php 
printf( __( 'At first, properly configure plugin settings. You can know more details about every settings hovering the mouse over the question mark icon next to the settings option. After that, Please wait some time to allow plugin to run republish process with an interval configured by you from plugin settings. If still not working, go to Tools > Plugins Tools > Import Settings > Copy and then open Pastebin.com or GitHub Gist and create a paste or gist with the copied data and send me the link using Contact page or open a support on WordPress.org forums (only for free version users). Here are some common <a href="%s" target="_blank">cron problems</a> related to WordPress.', 'wp-auto-republish' ), 'https://github.com/johnbillion/wp-crontrol/wiki/Cron-events-that-have-missed-their-schedule' );
?></p>
                            
                                <li><?php 
_e( 'Plugin is showing a warning notice to disable the plugin after activation. What is the reason?', 'wp-auto-republish' );
?></li>
                                <p><?php 
printf( __( 'Currently you are using original post published information in your post permalinks (Settings > Permalinks). But this plugin reassign a current date to republish a post. So, the permalink will be changed after republish. It may cause SEO issues. It will be safe not to use free version of this plugin in such situation. But Premium version you can use %1$s instead of %2$s to solve this issue.', 'wp-auto-republish' ), '<code>%wpar_year%</code>, <code>%wpar_monthnum%</code>, <code>%wpar_day%</code>, <code>%wpar_hour%</code>, <code>%wpar_minute%</code>, <code>%wpar_second%</code>', '<code>%year%</code>, <code>%monthnum%</code>, <code>%day%</code>, <code>%hour%</code>, <code>%minute%</code>, <code>%second%</code>' );
?></p>
                            
                                <li><?php 
_e( 'Plugin is showing a PHP fatal error after enabling the Premium version. How to fix this?', 'wp-auto-republish' );
?></li>
                                <p><?php 
_e( 'Please deactivate the free version of this plugin first from plugins page, then activate the premium version. It should work as expected.', 'wp-auto-republish' );
?></p>
                            </ol>
                        </div>
                    </div>
                <?php 
?>
                    <div class="coffee-box">
                        <div class="coffee-amt-wrap">
                            <p><select class="coffee-amt">
                                <option value="5usd">$5</option>
                                <option value="6usd">$6</option>
                                <option value="7usd">$7</option>
                                <option value="8usd">$8</option>
                                <option value="9usd">$9</option>
                                <option value="10usd" selected="selected">$10</option>
                                <option value="11usd">$11</option>
                                <option value="12usd">$12</option>
                                <option value="13usd">$13</option>
                                <option value="14usd">$14</option>
                                <option value="15usd">$15</option>
                                <option value=""><?php 
_e( 'Custom', 'wp-auto-republish' );
?></option>
                            </select></p>
                            <a class="button button-primary buy-coffee-btn" style="margin-left: 2px;" href="https://www.paypal.me/iamsayan/10usd" data-link="https://www.paypal.me/iamsayan/" target="_blank"><?php 
_e( 'Buy me a coffee!', 'wp-auto-republish' );
?></a>
                        </div>
                        <span class="coffee-heading">
                            <?php 
_e( 'Buy me a coffee!', 'wp-auto-republish' );
?>
                        </span>
                        <p style="text-align: justify;">
                            <?php 
printf( __( 'Thank you for using %s. If you found the plugin useful buy me a coffee! Your donation will motivate and make me happy for all the efforts. You can donate via PayPal.', 'wp-auto-republish' ), '<strong>' . $this->name . ' v' . $this->version . '</strong>' );
?></strong>
                        </p>
                        <p style="text-align: justify;font-size: 12px;font-style: italic;">
                            Developed with <span style="color:#e25555;">♥</span> by <a href="https://www.sayandatta.in" target="_blank" style="font-weight: 500;">Sayan Datta</a> | 
                            <a href="https://www.sayandatta.in/contact/" style="font-weight: 500;">Hire Me</a> | 
                            <a href="https://github.com/iamsayan/wp-auto-republish" target="_blank" style="font-weight: 500;">GitHub</a> | <a href="https://wordpress.org/support/plugin/wp-auto-republish" target="_blank" style="font-weight: 500;">Support</a> | 
                            <a href="https://wordpress.org/support/plugin/wp-auto-republish/reviews/#new-post" target="_blank" style="font-weight: 500;">Rate it</a> (<span style="color:#ffa000;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>) on WordPress.org, if you like this plugin.
                        </p>
                    </div>
                <?php 
?>
            </div>
           
            <div id="postbox-container-1" class="postbox-container">
            <?php 
?> 
            <?php 
?>
                <div class="postbox">
                    <h3 class="hndle" style="cursor:default;text-align: center;"><?php 
_e( 'Upgrade to Premium Now!', 'wp-auto-republish' );
?></h3>
                    <div class="inside">
                        <div class="misc-pub-section" style="text-align:center;">
                            <i><?php 
_e( 'Upgrade to the premium version and get the following features', 'wp-auto-republish' );
?></i>:<br>
				            <ul>
				            	<li>• <?php 
_e( 'Custom Post types & Taxonomies', 'wp-auto-republish' );
?></li>
				            	<li>• <?php 
_e( 'Individual & Scheduled Republishing', 'wp-auto-republish' );
?></li>
				            	<li>• <?php 
_e( 'Date Time Range Based Republishing', 'wp-auto-republish' );
?></li>
				            	<li>• <?php 
_e( 'Custom Post Republish Interval', 'wp-auto-republish' );
?></li>
				            	<li>• <?php 
_e( 'Custom Title for each Republish Event', 'wp-auto-republish' );
?></li>
                                <li>• <?php 
_e( 'Automatic Cache Plugin Purge Support', 'wp-auto-republish' );
?></li>
                                <li>• <?php 
_e( 'Can use New Dates in Post Permalinks', 'wp-auto-republish' );
?></li>
                                <li>• <?php 
_e( 'Change Post Status after Republish', 'wp-auto-republish' );
?></li>
                                <li>• <?php 
_e( 'One Click Instant Republish & Clone', 'wp-auto-republish' );
?></li>
                                <li>• <?php 
_e( 'Email Notification upon Republishing', 'wp-auto-republish' );
?></li>
                                <li>• <?php 
_e( 'Priority Email Support & many more..', 'wp-auto-republish' );
?></li>
				            </ul>
				            <?php 

if ( wpar_load_fs_sdk()->is_not_paying() && !wpar_load_fs_sdk()->is_trial() && !wpar_load_fs_sdk()->is_trial_utilized() ) {
    ?>
                                <a class="button button-primary" href="<?php 
    echo  wpar_load_fs_sdk()->get_trial_url() ;
    ?>"><?php 
    _e( 'Start Trial', 'wp-auto-republish' );
    ?></a>&nbsp;
                            <?php 
}

?>
                            <a class="button button-primary" href="<?php 
echo  wpar_load_fs_sdk()->get_upgrade_url() ;
?>"><?php 
_e( 'Upgrade Now', 'wp-auto-republish' );
?></a>
                        </div>
                    </div>
                </div>
            <?php 
?> 
                <div class="postbox">
                    <h3 class="hndle" style="cursor:default;text-align: center;"><?php 
_e( 'My Other Plugins!', 'wp-auto-republish' );
?></h3>
                    <div class="inside">
                        <div class="misc-pub-section">
                            <div style="text-align: center;">
                                <span class="dashicons dashicons-clock" style="font-size: 16px;vertical-align: middle;"></span>
                                <strong><a href="https://wordpress.org/plugins/wp-last-modified-info/" target="_blank">WP Last Modified Info</a></strong>
                            </div>
                            <div style="text-align: center;">
                                <?php 
_e( 'Display last update date and time on pages and posts very easily with \'dateModified\' Schema Markup.', 'wp-auto-republish' );
?>
                            </div>
                        </div>
                        <hr>
                        <div class="misc-pub-section">
                            <div style="text-align: center;">
                                <span class="dashicons dashicons-admin-comments" style="font-size: 16px;vertical-align: middle;"></span>
                                <strong><a href="https://wordpress.org/plugins/ultimate-facebook-comments/" target="_blank">Ultimate Social Comments</a></strong>
                            </div>
                            <div style="text-align: center;">
                                <?php 
_e( 'Ultimate Facebook Comments Solution with instant email notification for any WordPress Website. Everything is customizable.', 'wp-auto-republish' );
?>
                            </div>
                        </div>
                        <hr>
                        <div class="misc-pub-section">
                            <div style="text-align: center;">
                                <span class="dashicons dashicons-admin-links" style="font-size: 16px;vertical-align: middle;"></span>
                                <strong><a href="https://wordpress.org/plugins/change-wp-page-permalinks/" target="_blank">WP Page Permalink Extension</a></strong>
                            </div>
                            <div style="text-align: center;">
                                <?php 
_e( 'Add any page extension like .html, .php, .aspx, .htm, .asp, .shtml only to wordpress pages very easily (tested on Yoast SEO, All in One SEO Pack, Rank Math, SEOPresss and Others).', 'wp-auto-republish' );
?>
                            </div>
                        </div>
                        <hr>
                        <div class="misc-pub-section">
                            <div style="text-align: center;">
                                <span class="dashicons dashicons-megaphone" style="font-size: 16px;vertical-align: middle;"></span>
                                <strong><a href="https://wordpress.org/plugins/simple-posts-ticker/" target="_blank">Simple Posts Ticker</a></strong>
                            </div>
                            <div style="text-align: center;">
                                <?php 
_e( 'Simple Posts Ticker is a small tool that shows your most recent posts in a marquee style.', 'wp-auto-republish' );
?>
                            </div>
                        </div>
                    </div>
                </div>
            </diV>
        </div>
    </div>
</div>