Settings Extension
===================

This extension provides support for persistent settings for Yii2.

[![Build Status](https://travis-ci.org/solutosoft/yii-settings.svg?branch=master)](https://travis-ci.org/solutosoft/yii-settings)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/solutosoft/yii-settings/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/solutosoft/yii-settings/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/solutosoft/yii-settings/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/solutosoft/yii-settings/?branch=master)
[![Total Downloads](https://poser.pugx.org/solutosoft/yii-settings/downloads.png)](https://packagist.org/packages/solutosoft/yii-settings)
[![Latest Stable Version](https://poser.pugx.org/solutosoft/yii-settings/v/stable.png)](https://packagist.org/packages/solutosoft/yii-settings)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist solutosoft/yii-settings
```

or add

```json
"solutosoft/yii-settings": "*"
```

Configuration
-------------

To use the Setting Component, you need to configure the components array in your application configuration:
```php
'components' => [
    'settings' => [
        'class' => 'Soluto\Settings\Settings',
    ],
],
```

Usage:
---------

```php
$settings = Yii::$app->settings;

$settings->set('key');

$settings->set('section.key');

// Checking existence of setting
$settings->exists('key');

// Removes a setting
$settings->remove('key');

// Removes all settings
$settings->remove();

```

Events
------

You can use `beforeExecute` event to store extra values and apply extra conditions on command execution

```php
<?php

'components' => [
    'settings' => [
        'class' => 'Soluto\Settings\Settings',
        'on beforeExecute' => function ($event) {
            $event->data = ['user_id' => Yii::$app->user->id];
        }
    ],
],

$settings = Yii::$app->settings;

//INSERT (`key`,`value`, `user_id`) INTO `setting` VALUES ('website', 'http://example.org', 1)
$settings->set('website', 'http://example.org');

//SELECT `value` FROM `setting` WHER (`settings`.`key` = 'website' and `settings`.`user_id` = 1)
$settings->get('website', 'http://example.org');

```




