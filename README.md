# Wordpress User Session Synchronizer

Keep the user logged in from one wordpress to another by synchronizing user data and cookie session  

## Description

User Session Synchronizer allows you to keep the user logged in from one wordpress to another by synchronizing user data and cookie session based on a verified email. 
The user email is encrypted based on the current user ip and a secret key shared by the synchronized wordpress installations. 

### Features

- Synchronize session between installations
- Verify user email through new registration
- Verify user email through manual admin action
- Verify user email through email verification code
- Prevent user form changing email
- Display historical sessions
- Auto add new subscriber if user doesn't exist
- Destroy session everywhere on logging out

### Upcoming

- Multiple secret keys & networks
- Enable ajax cross-domain requests

## Installation

Installing "User Session Synchronizer" can be done either by searching for "User Session Synchronizer" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Set your first Secret Key throught the 'User Session Sync > Keys'
5. Repeat this installation process for every Wordpress you wish to sychnorize with the same Secret Key

## Screenshots

![Screenshot](https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_1.png)
![Screenshot](https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_2.png)
![Screenshot](https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_3.png)
![Screenshot](https://raw.githubusercontent.com/rafasashi/user-session-synchronizer/master/screenshot_4.png)

## Information

- Contributors: rafasashi
- Donate link: https://www.paypal.me/recuweb
- Tags: user, session, synchronizer, cookie
- Requires at least: 4.3
- Tested up to: 4.3
- Stable tag: 1.2
- License: GPLv3 or later
- License URI: http://www.gnu.org/licenses/gpl-3.0.html

## Frequently Asked Questions

### What is the plugin template for?

This plugin template is designed to Keep the user logged in from one wordpress to another by synchronizing user data and cookie session

## Changelog ##

### 1.3
* 2016-10-09
* Multiple Logout issues corrected

### 1.2
* 2016-09-26
* Multiple subfolders under same domain

### 1.1
* 2016-09-22
* Theme footer hooked

### 1.0
* 2016-09-06
* Initial release

## Upgrade Notice 

### 1.0
* 2016-09-06
* Initial release
