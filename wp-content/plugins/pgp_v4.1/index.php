<?php
/*
Plugin Name: Post Generator Pro
Plugin URI: https://www.postgeneratorpro.com
Description: convert your spintax into many unique readable posts/pages
Version: 4.1
Author: Herahadi
Author URI: https://www.facebook.com/herahadi
License: 
    Copyright 2017 herahadi (email : herahadi@gmail.com)

    This plugin is paid software; you can not redistribute it and/or modify illegally
    it.

    If you violate this provision it is considered you have owed me the price 
    of the latest version of this software multiplied by the number of days you committed, 
    and in the Hereafter I have the right to sue you.

    In bahasa Indonesia: 
    Plugin ini adalah software berbayar; Anda tidak boleh mendistribusikan ulang dan/atau merubahnya secara ilegal.

    Jika anda melanggar ketentuan ini maka dianggap anda telah berhutang ke saya sebesar 
    harga versi terakhir software ini dikalikan jumlah hari anda melakukan pelanggaran, 
    dan kelak di akhirat saya berhak untuk menuntut Anda.
    

Logs:
v4.1
- compatibility with pakarbot new ssl
v4.0
- hide script
- support sticky shortcode
v3.9.1
- Perubahan sistem autentikasi
v3.9
- Use PakarBOT membership for authentication
- Account, title, and Article now saved after posted
v3.8
- Integrated with AIO Seo Pack plugin
v3.7
- Added new feature, the ability to add your own ShortCode
- Added new feature, the ability to add delay per posts
- Changes to algorithms for faster spin & posting processing
- Update city list
- Better compatibily with another plugins/themes
v3.6
- Integrated with Yoast SEO plugin. Tested with yoast 4.9.
v3.5
- Added ability to publish articles as draft.
v3.4
- Added rich text editor for content/article.
- Minor bug fix, backdate features sometimes provide odd dates in some hosting.
v3.3
- Better memory management.
v3.2
- Added spintax tester link.
v3.1
- Added shortcode to call generated title into article. shortcode [pgp_title].
v3.0
- New engine for more robust, faster, more results, no limit.
- Remove retrieval feature.
v2.0
- Added built-in ShortCode for 780++ city in Indonesia. shortcode: [pgp_city_id_jawa],[pgp_city_id_kalimantan], etc.
- Added tags with spintax support.
- Added ability to publish articles as posts or pages.
v1.0
- Release first version.
*/
include_once('pgp.php');
?>