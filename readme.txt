=== LDW Watermark ===
Contributors: Lake District Walks
Tags: watermark, dynamic, htaccess
Requires at least: 4.3.1
Tested up to: 4.4.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Watermark your images on the fly - Without altering the originals!

== Description ==
This plugin will add a non-destructive transparent watermark to images in your uploads directory.
It does this by adding the watermark on the fly via an .htaccess file, which it creates.
Your original images are **never** altered :)

If the plugin is disabled your images will no longer be watermarked.

Features:

* Adds watermark text, with a transparent background, to the bottom of JPG images in your uploads directory.
* You choose the text.
* You choose which image sizes are watermarked.

== Installation ==
The easiest way to enjoy LDW Watermark is to login to your WordPress dashboard, go to Plugins > Add New,
search for LDW Watermark and click 'Install Now'.

You can also download the zip file from this page and upload it from the Plugins > Add New > Upload page.

== Frequently Asked Questions ==
= Usage =
1. Dashboard > Settings > LDW Watermark.
1. Configure to your requirements.
1. Click 'Save Changes'.

= Upload Folder Is Not Writeable =
The plugin needs to create some directories and files in your WordPress uploads directory.
That directory is not currently writeable by the plugin.
The solution to that is outside the scope of this FAQ, but essentially, you need to make it writeable!

= .htaccess File Is Not Writeable =
As above; the plugin needs to create or overwrite the .htaccess file in your WordPress uploads folder.

== Changelog ==
= 1.1.0 =
* Add background transparency.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.1.0 =
* Should upgrade.

= 1.0.0 =
* No upgrade available.
