=== Staatic ===
Contributors: staatic
Tags: performance, seo, security, optimization, static site, fast, speed, secure, cache, caching, optimize, cdn
Stable tag: 1.0.4
Tested up to: 5.8.1
Requires at least: 5.0
Requires PHP: 7.0
License: BSD-3-Clause

Staatic allows you to generate and deploy an optimized static version of your WordPress site.

== Description ==

Staatic allows you to generate and deploy an optimized static version of your WordPress site, improving performance, SEO and security all at the same time.

Features of Staatic include:

* Powerful Crawler to transform your WordPress site quickly.
* Supports multiple deployment methods, e.g. Netlify, AWS (Amazon Web Services) S3 + CloudFront, or even your local server (dedicated or shared hosting).
* Very flexible out of the box (allows for additional urls, paths, redirects, exclude rules).
* Supports HTTP redirects, custom “404 not found” page and other HTTP headers.
* CLI command to publish from the command line.
* Compatible with WordPress MultiSite installations.
* Compatible with HTTP basic auth protected WordPress installations.

Depending on the chosen deployment method, additional features may be available.

== Installation ==

Installing Staatic is simple!

### Install from within WordPress

1. Visit the plugins page within your WordPress Admin dashboard and select ‘Add New’;
2. Search for ‘Staatic’;
3. Activate ‘Staatic’ from your Plugins page;
4. Go to ‘After activation’ below.

### Install manually

1. Upload the ‘staatic’ folder to the `/wp-content/plugins/` directory;
2. Activate the ‘Staatic’ plugin through the ‘Plugins’ menu in WordPress;
3. Go to ‘After activation’ below.

### After activation

1. Click on the ‘Staatic’ menu item on the left side navigation menu;
2. On the settings page, provide the relevant Build & Deployment settings;
3. Start publishing to your static site!

== Frequently Asked Questions ==

= How will Staatic improve the performance of my site? =

Staatic will convert your dynamic WordPress site into a static site consisting of HTML assets, images, scripts and other assets. By removing WordPress (and even PHP) from the equation, requested pages from your site can be served instantly, instead of having to be generated on the fly.

= Why not use a caching plugin? =

Caching plugins are great to improve the performance of your site as well, however they (usually) don’t remove WordPress itself from the stack, which adds additional latency.

Also by using Staatic, you are free to host your site anywhere. You could for example choose a very fast cloud provider or content delivery network, providing even more performance.

= Will the appearance of my site change? =

No. At least, it should not. If the static version of your site does differ, it is probably because of invalid HTML in your original WordPress site, which could not be converted correctly. In that case you can verify the validity of your HTML using a validator service like [W3C Markup Validation Service](https://validator.w3.org/).

= How will Staatic improve the security of my site? =

Since your site is converted into static HTML pages, the attack surface is greatly reduced. That means less need to worry about keeping WordPress, plugins and themes up-to-date.

= Is Staatic compatible with all plugins? =

Unfortunately not. Because your site is converted into a static site, dynamic server side functions are not available. Plugins that require this, for example to process forms, retrieve data externally etc., do not work out of the box, or are not supported at all.

You will need to make modifications to make such features work, or you can choose Staatic Premium which adds such functionality automatically. For more information, please visit [staatic.com](https://staatic.com/wordpress/).

= Will it work on shared or (heavily) restricted servers? =

Staatic has been optimized to work in most environments. The major requirements are that the plugin is able to write to the work directory and connect to your WordPress installation.

= Where can I get help? =

If you have any questions or problems, please have a look at our [documentation](https://staatic.com/wordpress/documentation/) and [FAQ](https://staatic.com/wordpress/faq/) first.

If you cannot find an answer there, feel free to open a topic on our [Support Forums](https://wordpress.org/support/plugin/staatic/).

Want to get in touch directly? Please feel free to [contact us](https://staatic.com/wordpress/contact/). We will get back to you as soon as possible

== Screenshots ==

1. Use your WordPress installation as a private staging environment and make all of the modifications you need. Then publish these changes to your highly optimized and consumer facing static site with the click of a button.
2. Monitor the status of your publications while they happen and review details of past publications to easily troubleshoot any issues.
3. Configure and fine tune the way Staatic processes your site to suit your specific needs.

== Changelog ==

= 1.0.4 =
* Improvement: adds publish button to publication overview.
* Improvement: optimizes AWS deployment method URL/path conversion.
* Fix: ensures http basic auth parameters are passed when publishing.

= 1.0.3 =
* Improvement: various performance improvements.
* Fix: ensures database migrations are executed after plugin upgrade.
* Fix: correctly cleans up expired deployment results.
* Fix: improves loading of plugin settings.

= 1.0.2 =
* Improvement: makes plugin source code more readable.

= 1.0.1 =
* Fix: fixes a bug where uninstalling the plugin would fail.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
This version fixes a bug where uninstalling the plugin would fail.

== Staatic Premium ==

In order to support ongoing development of Staatic, please consider going Premium. In addition to helping the authors maintain Staatic, Staatic Premium adds additional functionality.

For more information visit [Staatic](https://staatic.com/wordpress/).
