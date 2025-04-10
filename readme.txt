=== WebP Only Uploads ===
Contributors: ramildev  
Tags: webp, image compression, media optimization, image format  
Requires at least: 5.0  
Tested up to: 6.5  
Requires PHP: 7.2  
Stable tag: 1.3  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Converts uploaded images to WebP format, deletes the originals, and supports bulk conversion. Saves disk space and improves site speed.

== Description ==
This plugin:
- Automatically converts uploaded JPG/PNG images to WebP
- Compresses them with quality control
- Deletes original files to save space
- Updates paths, thumbnails and metadata
- Includes admin settings for WebP quality
- Supports bulk conversion of existing media

Perfect for developers and site owners who want a fast and simple WebP solution.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/webp-only-uploads` directory, or install via the plugin admin panel.
2. Activate the plugin through the 'Plugins' screen.
3. Go to "Settings → WebP Upload" to set the quality and run bulk conversion if needed.

== Screenshots ==
1. Settings panel for WebP quality
2. Bulk conversion button

== Changelog ==
= 1.3 =
* Added bulk conversion functionality  
= 1.2 =
* Added admin panel for quality setting  
= 1.0 =
* Initial release - auto WebP conversion + removal of originals  

== Frequently Asked Questions ==
= Will it replace existing images? =  
Only if you run bulk conversion manually from the settings panel.

= Are original images saved? =  
No. The plugin deletes them automatically after conversion.
