=== atec Stats ===
Contributors: DocJoJo
Donate link: https://www.paypal.com/paypalme/atecsystems/5eur
Tags: Lightweight, beautiful and GDPR compliant WP statistics, including countries map.
Requires at least: 5.2
Tested up to: 6.6.3
Requires PHP: 7.4
Stable tag: 1.0.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, resource saving and GDPR compliant WP statistics.

== Description ==

This plugin observes incoming traffic and logs page views and visitors.

Lightweight (200KB) and resource-efficient.
Backend CPU footprint: <1 ms.
Frontend CPU footprint:  <1 ms.

- Requires jQuery in the frontend to bypass page cache plugins.
- No configuration required.
- IP addresses are obfuscated after processing, so it is very GDPR safe.
- Optimized DB design and data types guarantee for the lowest storage usage (32 bytes per IP log entry, so roughly 1GB per 31 million distinctiv visits).
- Advanced minimal logging only adds an average of 1 ms to page load time.
- Super fast Internal IP2GEO location database for IP resolution (2.5MB).

== 3rd party as a service ==

Once, when activating the plugin, an integrity check is requested from our server (https://atecplugins.com/).
Privacy policy: https://atecplugins.com/privacy-policy/

The country map requires access to google's chart API at https://www.gstatic.com.
No account required.
Privacy policy: https://policies.google.com/privacy

== 3rd party data ==

This product includes IP2GEO™ location data created by IP2Location, available from https://www.ip2location.com.
Country flags by "Free Country Flags in SVG" @ https://flagicons.lipis.dev/

The file IP2LOCATION-LITE-DB1.BIN.zip is around 800 KB in size and therefore not included in the plugin. 
It is downloaded from https://atecplugins.com/ and stored in the uploads/atec-stats folder on plugin activation.
Privacy policy: https://atecplugins.com/privacy-policy/

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory or through the `Plugins` menu.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Click "atec Stats" link in admin menu bar.

== Frequently Asked Questions ==

== Screenshots ==

1. Statistics - per month.
2. Statistics - per year.
3. Statistics - map.
4. Statistics - URLs and REFERER.

== Upgrade Notice ==

= 1.0.12 [2024.10.22] =
* removed google script

= 1.0.11 [2024.10.20] =
* review

= 1.0.10 [2024.10.19] =
* pre-review

= 1.0.9 [2024.08.30] =
* IP2Location country flags

= 1.0.8 [2024.07.21] =
* countries overview
* svg deleted > 200k, jquery log

= 1.0.6 [2024.07.20] =
* new cleanup, PNG flags

= 1.0.4 [2024.07.07] =
* new cleanup

= 1.0.3 [2024.06.26] =
* deploy

= 1.0.1, 1.0.2 [2024.06.11] =
* hupe13 bug fix

= 1.0 [2024.06.11] =
* Initial Release
