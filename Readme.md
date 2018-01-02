[![Codecov](https://img.shields.io/codecov/c/github/scoutnet/plugins.sn_webservice.svg)]()
[![Build Status](https://jenkins.scoutnet.eu/buildStatus/icon?job=scoutnet/plugins.sn_webservice/master)](https://jenkins.scoutnet.eu/job/scoutnet/job/plugins.sn_webservice/job/master/)
[![Packagist](https://img.shields.io/packagist/v/scoutnet/sn-webservice.svg)](https://packagist.org/packages/scoutnet/sn-webservice)
[![Packagist](https://img.shields.io/packagist/dt/scoutnet/sn-webservice.svg?label=packagist%20downloads)](https://packagist.org/packages/scoutnet/sn-webservice)
[![Packagist](https://img.shields.io/packagist/l/scoutnet/sn-webservice.svg)](https://packagist.org/packages/scoutnet/sn-webservice)
---

ScoutNet Api Webservice
=======================

With this lib you can read and write ScoutNet Kalender Events and read ScoutNet Index Elements. It is used by different Plugins for Typo3, Joomla, Wordpress and MediaWiki.

Install
-------

run 

```bash
composer require "scoutnet/sn-webservice:^1.0"
```

to install ScoutNet Api Webservice. 

run

```bash
composer dumpautoload
```

to regenerate autoloader script.

Use API
-------

ReadApi:

```php
// load Autoloader from composer
require_once('vendor/autoload.php');

// reading Elements from the API with this code:
$scoutNetApi = new \ScoutNet\Api\ScoutnetApi();
$events = $scoutNetApi->get_events_for_global_id_with_filter(4, ['limit' => 3]);

print_r($events);
```
