===  AEH Speed Optimization: Browser Cache, Optimized Minify, Lazy Loading & Image Optimization ===
Contributors: passionatebrains, freemius
Donate link: http://www.addexpiresheaders.com/
Tags: cache, Optimize, performance, PageSpeed, core web vitals
Requires at least: 3.5
Tested up to: 6.9
Stable tag: 3.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

AEH Speed Optimization boosts site speed with caching, minification, lazy loading, and image optimization to improve performance and SEO.

== Description ==
AEH Speed Optimization boosts site speed with caching, minification, lazy loading, and image optimization to improve performance and SEO.

= Advantages =
1) Serves static assets with an efficient cache policy helps to leverage browser caching.

2) Reduces page loading time of website.

3) Improves user experience as page loads very quickly than before.

4) Decreases total data-size of page.

5) Larger band of predefined file types are covered so it will increase bandwidth of files which can have expiry headers.

6) You can have different expire time for cache base on type of resources.

7) Merge multiple CSS files into one helps reducing http requests and improving page load speed.

8) Async loading of processed CSS files.

9) Minify CSS files which reduce data transfer requirement hence increase page load speed.

10) Inline small footer CSS files which helps to improve page speed.

11) Escape admin users from minification to avoid page builders related issues.

12) Lazy loading support for images, iframes, and videos.

13) Widgets and shortcodes can also be lazy loaded.

14) Lazy loading fully supports responsive images for better performance across devices.

15) Custom placeholder images can be used during lazy loading.

16) Exclude critical elements from lazy loading to ensure essential visuals load immediately.

= Pro Features =
1) Ability to add expires headers to External Resources

2) Adding new file types for adding expires headers

3) Refresh cache periodically

4) Unset Entity Tags

5) HTTP(Gzip) compression

6) Prevent Specific files from caching

7) Removing version info from files

8) Inline google fonts helps to load page faster and reduce external http requests.

9) Merge multiple JS files into one helps reducing http requests and improving page load speed.

10) Minify JS files which reduce data transfer requirement hence increase page load speed.

11) Plugin offers Defer scripts option to speed up rendering process.

12) HTML minification helps to reduce overall data size of page.

13) Automatically convert images to optimized formats like WebP or AVIF for faster delivery without manual effort.

14) Smartly resize and serve images based on screen size and device type to minimize bandwidth and boost speed.

15) Support next-gen formats like WebP and AVIF for superior compression, smaller files, and sharper visuals.

16) Track image optimization stats, savings, and performance improvements directly from your WordPress dashboard.

17) Automatically deliver the perfect image size for every device, ensuring crisp visuals and smooth performance.

18) Optimize images without visible loss in clarity—maintaining pixel perfection while reducing file size.

19) Works out-of-the-box with WordPress and major themes or plugins, no setup hassles or coding required.

= Documentation =
For Plugin documentation, please refer our <a href="https://www.addexpiresheaders.com/documentation" rel="follow">plugin website</a>.

= Requirements =
1) Make sure that the "mod_expires" module is enabled on your website hosting server.

2) It is necessary to have read/write permission of .htaccess file to plugin. If not then update file permissions accordingly.

3) check status page of plugin for more info.

== Installation ==
1) Deactivate and uninstall any other expires headers plugin you may be using.

2) Login as an administrator to your WordPress Admin account. Using the “Add New” menu option under the “Plugins” section of the navigation, you can either search for: "AEH speed Optimization" or if you’ve downloaded the plugin already, click the “Upload” link, find the .zip file you download and then click “Install Now”. Or you can unzip and FTP upload the plugin to your plugins directory (wp-content/plugins/).

3) Activate the plugin through the "Plugins" menu in the WordPress administration panel.

== Usage ==

To use this plugin do the following:

1) Firstly activate Plugin.

2) Go to plugin settings page.

3) Check Files types you want to have expires headers and also add respective expires days for mime type using input box and make sure you enable respective mime type, for which group of files you want to add expires headers.

4) Once you hit "submit" button all options you selected in settings page saved database of website and accordingly .htaccess file will updated and add expires headers for respective selected files.

5) For Minification check respective settings at Minification Tab of plugin settings page.

6) For Lazy Loading check respective settings at Lazy Loading Tab of plugin settings page.

== Frequently Asked Questions ==

= Does this plugin have custom expiry time for different resources? =
Yes base on Mime Type you can have different expiry time.

= Does this plugin help in gzip compression of output html? =
No, But if you upgrade to pro version you will have facility for same.

= Can we add custom file types for adding expires headers? =
No, But with upgrade you can have facility to add custom file types.

= Can I do CSS files minification and merging using plugin?
Yeah, Plugin by default provides minification and merging of CSS files. You can enable or disable this functionality from plugin settings under Minification Tab.

== Changelog ==

= 1.0 =
Initial Version of Plugin

= 1.1 =
Added Activation and Deactivation hooks.
Added Settings link on plugins page.

= 1.2 =
Adding functionality to disable Etags.

= 2.0 =
Basic feature for adding expires headers for pre define file types
Ability to have Pro-Version

= 2.1 =
Adding functionality for caching and adding expires headers to External resources
Added Plugin compatibility status page
Added more file formats

= 2.2 =
Upgrading facility to serve static assets with an efficient cache policy.
Increasing default values for plugin cache life for various types of files.

= 2.3 =
Adding Minification Functionality to Plugin.
Adding more power to browser caching and better compression of resources.

= 2.4 =
Adding html minification and inlining google fonts functionality
Updated third party library for minification

= 2.5 =
Adding support for async loading of processed css files

= 2.6 =
Updated Freemius SDK to latest stable version

= 2.7 =
Updated Latest Freemius SDK 
Added Purge Notification

= 2.7.1 =
Added Nonce Field to all plugin forms

= 2.7.2 =
Updated Freemius SDK to latest stable version

= 2.7.3 =
Updated Freemius SDK to latest stable version

= 2.8.0 =
Updated Freemius SDK to latest stable version

= 2.8.1 =
Tested Plugin with Latest 6.4.1
Updated Freemius SDK to latest stable version

= 2.8.2 =
Tested Plugin with Latest 6.4.3
Updated Freemius SDK to latest stable version

= 2.9.0 =
Tested Plugin with Latest 6.6
Updated Freemius SDK to latest stable version

= 2.9.1 =
Updated Freemius SDK to latest stable version
some minor bug fixed

= 2.9.2 =
Updated Freemius SDK to latest stable version

= 2.10.0 =
Updated Freemius SDK to latest stable version

= 3.0.0 =
Updated Freemius SDK to latest stable Version
Added Lazy Loading functionality

= 3.1.0 =
Updated Freemius SDK to latest stable Version
Tested with Latest WordPress v6.9
Updated some code changes 

== Screenshots ==
1. Cache Settings
2. Minify Settings
3. Lazy Loading Settings
4. Image Optimization (Pro)