=== Woowup Abandoned Cart ===
Contributors: kevinwoowup
Tags: woocommerce, cart abandonment, crm, api, woowup
Requires at least: 5.4
Tested up to: 5.8
Stable tag: 1.1.6
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WoowUp: We are the omnichannel and predictive Retail CRM, which allows stores to make database intelligence, create segments and set triggers.
Knowing your customer and offering what they need when they need it is key to retaining and growing your active customer base.

== Description ==

Detects abandoned customer carts and is sent with customer information to WoowUp via WoowUp Api

== Installation ==

1. Upload `woowup-abandonedcart-woocommerce.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= When it's an abandoned cart? =

When you arrive at checkout and 30 minutes have passed without buying

= When information is sent to WoowUp? =

Every 10 minutes we try to send information to WoowUp

= What do I need to make it work? =

Only need to add your api-key in the setting section of this plugin

= Where do I get the api-key? =

In your WoowUp account settings

== Screenshots ==

== Changelog ==

= Version 1.0.0 - 2021-04-12 =
* Initial Release
= Version 1.0.1 - 2021-08-27 =
* Fix: Abandoned cart is not deleted if an error occurred when send to WoowUp
* Fix: It is verified that the client exists
= Version 1.1.1 - 2021-09-02 =
* Feat: Tables are added in the settings section to see abandoned carts
= Version 1.1.2 - 2022-01-13 =
* Fix: Abandoned cart does not map createtime anymore
= Version 1.1.3 - 2024-12-19 =
* Fix: Resolve issue preventing abandoned carts from being uploaded correctly
= Version 1.1.4 - 2024-12-23 =
* Fix: Minor changes in readme
= Version 1.1.6 - 2026-04-28 =
* Fix: Add retry logic with exponential backoff for 429, 502 and 503 API responses
= Version 1.1.5 - 2026-04-27 =
* Fix: SQL injection in cart recovery and send queries
* Fix: SQL injection in existExternalIdToRestore recovery table query
* Fix: Incorrect minute calculation in isValidToSend using DateInterval
* Fix: Cart table grows indefinitely, added 48h cleanup and LIMIT 50 per cron run
* Fix: Carts without email were sent to the API causing downstream errors
* Fix: Cron interval was 2 minutes instead of the intended 10 minutes
* Fix: ORDER BY createtime ASC added to cart SELECT so oldest carts are processed first
* Fix: Operator precedence bug caused carts to be deleted even when API call failed
* Fix: removeCart crashed with fatal error on orders created via WooCommerce REST API

== Upgrade Notice ==
