=== User Session Synchronizer ===
Contributors: rafasashi
Donate link: https://code.recuweb.com
Tags: user, session, synchronizer, cookie
Requires at least: 4.3
Tested up to: 4.9.5
Stable tag: 1.3.8
License: GPLv3
License URI: https://code.recuweb.com/product-licenses/

Keep the user logged in from one wordpress to another by synchronizing user data and cookie session  

== Description ==

User Session Synchronizer allows you to keep the user logged in from one wordpress to another by synchronizing user data and cookie session based on a verified email. 
The user email is encrypted based on the current user ip and a secret key shared by the synchronized wordpress installations. 

= Features =

- Synchronize session between installations
- Verify user email through new registration
- Verify user email through manual admin action
- Verify user email through email verification code
- Prevent user form changing email
- Display historical sessions
- Auto add new subscriber if user doesn't exist
- Destroy session everywhere on logging out

= Upcoming =

- Multiple secret keys & networks
- Enable ajax cross-domain requests

== Installation ==

Installing "User Session Synchronizer" can be done either by searching for "User Session Synchronizer" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Set your first Secret Key throught the 'User Session Sync > Keys'
5. Repeat this installation process for every Wordpress you wish to sychnorize with the same Secret Key

== Screenshots ==

1. https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_1.png
2. https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_2.png
3. https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_3.png
4. https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_4.png

== Frequently Asked Questions ==

= What is the plugin template for? =

This plugin template is designed to Keep the user logged in from one wordpress to another by synchronizing user data and cookie session

== Changelog ==

= 1.3.8 =

* 2017-05-26
* logout bug fixed

= 1.3.7 =

* 2017-05-16
* activate plugin email fixed

= 1.3.6 =

* 2017-03-14
* Logout everywhere fixed
* Infinit loop fixed on SSL auth 

= 1.3.5 =

* 2017-02-09
* Content-Security-Policy implementation
* HTTPS supported

= 1.3.4 =

* 2017-01-27
* User IP detection improved 
* Synchronization via iframe instead of image 

= 1.3.3 =

* 2016-10-26
* Resend validation email improved

= 1.3.2 =

* 2016-10-14
* Issue regarding email validation corrected

= 1.3.1 =

* 2016-09-26
* Multiple Logout issues corrected

= 1.2 =
* 2016-09-26
* Multiple subfolders under same domain

= 1.1 =
* 2016-09-22
* Theme footer hooked

= 1.0 =
* 2016-09-02
* Initial release

== Upgrade Notice ==

= 1.0 =
* 2016-09-02
* Initial release
