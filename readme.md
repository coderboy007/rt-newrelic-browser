<!-- DO NOT EDIT THIS FILE; it is auto-generated from readme.txt -->
# New Relic Browser by rtCamp

![Banner](assets/banner-1544x500.jpg)
This plugin instantly adds free New Relic Browser monitoring to your website.

**Contributors:** [rtcamp](http://profiles.wordpress.org/rtcamp), [newrelic](http://profiles.wordpress.org/newrelic), [prasad-nevase](http://profiles.wordpress.org/prasad-nevase), [rakshit](http://profiles.wordpress.org/rakshit), [rohanveer](http://profiles.wordpress.org/rohanveer)  
**Tags:** [new relic browser for wordpress](http://wordpress.org/plugins/tags/new relic browser for wordpress), [new relic wordpress](http://wordpress.org/plugins/tags/new relic wordpress), [new relic wordpress analytics](http://wordpress.org/plugins/tags/new relic wordpress analytics), [new relic wordpress multisite](http://wordpress.org/plugins/tags/new relic wordpress multisite), [new relic wordpress mu](http://wordpress.org/plugins/tags/new relic wordpress mu), [javascript monitoring](http://wordpress.org/plugins/tags/javascript monitoring), [javascript errors](http://wordpress.org/plugins/tags/javascript errors), [browser monitoring](http://wordpress.org/plugins/tags/browser monitoring)  
**Requires at least:** 3.0.1  
**Tested up to:** 4.1  
**Stable tag:** 1.0.3  
**License:** [MIT](http://opensource.org/licenses/mit-license.html)  
**Donate Link:** http://rtcamp.com/donate  

## Description ##

[![Build Status](https://travis-ci.org/rtCamp/rt-newrelic-browser.png?branch=master)](https://travis-ci.org/rtCamp/rt-newrelic-browser)
### Features ###
* Allows you to create a New Relic Account and Browser App instantly.
* If you already have New Relic Account, you can select between creating a new Browser app, or connecting your website to an existing browser app.
* You do not need to edit your theme's header file manually to insert the New Relic Browser javascript, nor do you need to worry about updating the javascript when New Relic releases new versions - this plugin takes care of all that!
* Compatible with WordPress Multisite.
* Thoroughly tested on the top 10-WordPress open source themes and plugins in WordPress themes/plugins repository.
* Actively supported by rtCamp and New Relic.

**Important Links**

* [GitHub](http://github.com/rtcamp/rt-newrelic-browser) - Please mention your wordpress.org username when sending pull requests.


## Installation ##

* Install the plugin from the 'Plugins' section in your dashboard (Go to `Plugins > Add New > Search` and search for 'New Relic Browser by rtCamp').
* Alternatively, you can [download](http://downloads.wordpress.org/plugin/rt-newrelic-browser.zip "Download New Relic Browser by rtCamp") the plugin from the repository. Unzip it and upload it to the plugins folder of your WordPress installation (`wp-content/plugins/` directory of your WordPress installation).
* Activate it through the 'Plugins' section.
* Access the settings page from WordPress backend under `Settings > New Relic Browser`.
* Once the plugin is configured, wait a minute or two, then login to your New Relic account to see Browser monitoring details.

## Frequently Asked Questions ##

### What if I do not have New Relic account? ###
This plugin will create one for you.

### If I have a New Relic account, where do I find my API Key? ###
[Here](https://docs.newrelic.com/docs/apm/apis/requirements/api-key#creating)! Make sure to use your 'API Key' and not your ’Data access key'.

### What if I accidentally disable the plugin and thus disable New Relic Browser monitoring? ###
No worries - simply re-enable the plugin, indicate you have an existing New Relic account, and provide [your API Key](https://docs.newrelic.com/docs/apm/apis/requirements/api-key#creating).

### Something is not working - what do I do? ###
Sorry about that! Please open a [Github Issue](https://github.com/rtCamp/rt-newrelic-browser/issues) or post to this plugin’s [Support section](https://wordpress.org/support/plugin/rt-newrelic-browser).

### Do I have to pay to use New Relic? ###
Nope! New Relic’s services, including [Browser monitoring](http://newrelic.com/browser-monitoring), [APM for your WordPress PHP app](http://newrelic.com/php/wordpress) (and [many other languages](http://newrelic.com/application-monitoring)), [Server monitoring](http://newrelic.com/server-monitoring), [Synthetic monitoring](http://newrelic.com/synthetics), and even [Plugins for your entire stack](http://newrelic.com/platform/), are all available in ‘Lite’ versions for free forever!


## Screenshots ##

### Select whether or not you have a New Relic account.

![Select whether or not you have a New Relic account.](assets/screenshot-1.png)

### If you have a New Relic account, then select an existing Browser Application, or create a new one.

![If you have a New Relic account, then select an existing Browser Application, or create a new one.](assets/screenshot-2.png)

### Enter required details to create a New Relic account.

![Enter required details to create a New Relic account.](assets/screenshot-3.png)

### You’ll see this when you are done!

![You’ll see this when you are done!](assets/screenshot-4.png)

## Changelog ##

### 1.0 ###
* First Public Release

### 1.0.1 ###
* Added uninstall.php for clean uninstallation of plugin
* Updated plugin core file as per updates in New Relic script updates

### 1.0.2 ###
* Replaced cURL request with WordPress HTTP API, thus removing server side package dependency for plugin users.


