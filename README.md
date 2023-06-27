<br />
<br />
<div align="center">
  <a href="https://github.com/YorCreative">
    <img src="content/logo.png" alt="Logo" width="128" height="128">
  </a>
</div>
<h3 align="center">Laravel Scanator</h3>

<div align="center">
<a href="https://github.com/YorCreative/Laravel-Scanator/blob/main/LICENSE.md"><img alt="GitHub license" src="https://img.shields.io/github/license/YorCreative/Laravel-Scanator"></a>
<a href="https://github.com/YorCreative/Laravel-Scanator/stargazers"><img alt="GitHub stars" src="https://img.shields.io/github/stars/YorCreative/Laravel-Scanator"></a>
<a href="https://github.com/YorCreative/Laravel-Scanator/issues"><img alt="GitHub issues" src="https://img.shields.io/github/issues/YorCreative/Laravel-Scanator"></a>
<a href="https://github.com/YorCreative/Laravel-Scanator/network"><img alt="GitHub forks" src="https://img.shields.io/github/forks/YorCreative/Laravel-Scanator"></a>
<a href="https://github.com/YorCreative/Laravel-Scanator/actions/workflows/phpunit.yml"><img alt="PHPUnit" src="https://github.com/YorCreative/Laravel-Scanator/actions/workflows/phpunit.yml/badge.svg"></a>
</div>

A Laravel package that provides functionalities for detecting sensitive information and patterns in the database, helping to ensure data privacy and security by empowering developers to easily integrate database scanning capabilities into their applications and take proactive measures to protect sensitive data.

## Installation

install the package via composer:

```bash
composer require yorcreative/laravel-scanator
```

Publish the assets.

```bash
php artisan vendor:publish --provider="YorCreative\Scanator\ScanatorServiceProvider"
php artisan vendor:publish --provider="YorCreative\Scanator\ScrubberServiceProvider"
```

## Configuration

### Adjusting the Scanators Configuration File

Adjust the configuration file to suite your application, located in `/config/scanator.php`.

```php
return [
    'sql' => [
        'ignore_tables' => [
            'failed_jobs',
            'migrations'
        ],
        'ignore_columns' => [
            'id',
            'created_at',
            'updated_at'
        ],
        'ignore_types' => [
            'timestamp'
        ],
        'select' => [
            'low_limit' => 3,
            'high_limit' => 10
        ],
    ]
];
```

### Adjusting the Scrubber Configuration File

Adjust the `regex_loader` field to suite your application, located in `/config/scrubber.php`.
For more information on the Scrubber configuration file, please see the source documentation [here](https://github.com/YorCreative/Laravel-Scrubber).

```php
return [
    ...
    'regex_loader' => ['*'], // Opt-in to specific regex classes OR include all with * wildcard.
    ...
];
```

## Usage

This package is shipped without implementation. It is shipped as a tool and up to developers to choose how they implement to suite to applications needs.

### Detection Manager

The [DetectionManager](https://github.com/YorCreative/Laravel-Scanator/blob/dev/src/Support/DetectionManager.php#L8) 
class is an essential component of the Laravel Scanator package. It manages and stores the [Detections](https://github.com/YorCreative/Laravel-Scanator/blob/dev/src/Support/Detection.php#L9) during the scanning process.
It provides methods to record detections, retrieve the list of detections, and obtain the scan start time.

### Full Database Scan
This package ships with the ability to analyze and build out database schema and then scans for sensitive 
information excluding any tables, columns or types from the Scanator configuration file finally to return the 
Detection Manager class.

```php
$detectionManager = Scanator::init();

$detections = $detectionManager->getDetections();
```

### Selective Database Scan

This package ships with the ability to selectively scan tables.

```php
$detectionManager = new DetectionManager();

Scanator::analyze($detectionManager, 'table_name', ['columns', 'to', 'scan']);

$detections = $detectionManager->getDetections();
```

### Defining Excludable Tables

The configuration file of this package offers the functionality to define excludable tables, allowing you to exclude them from the scanning process.


```php
 'ignore_tables' => [
    'failed_jobs',
    'migrations'
],
```

### Defining Excludable Columns

Similarly, you can define excludable columns within the configuration file to prevent the package from scanning them.

```php
'ignore_columns' => [
    'id',
    'created_at',
    'updated_at'
],
```

### Defining Excludable Data Types

To further refine the scanning process, you can specify excludable data types in the configuration file. 
The package will then disregard these data types during scanning.

```php
 'ignore_types' => [
    'timestamp'
],
```

### Defining Sample Size

For greater control over the scanning procedure, the configuration file allows you to define the sample size extracted
from each table.

```php
'select' => [
    'low_limit' => 3,
    'high_limit' => 10
],
```

## Scrubber Documentation

This package builds on the [RegexRepository](https://github.com/YorCreative/Laravel-Scrubber/blob/main/src/Scrubber.php#L17) provided by the scrubber package. For complete documentation on the scrubber, see [here](https://github.com/YorCreative/Laravel-Scrubber)

### Regex Class Opt-in

You have the ability through the scrubber configuration file to define what regex classes you want loaded into the application
when it is bootstrapped. By default, this package ships with a wildcard value.

### Regex Collection & Defining Opt-in

To opt in, utilize the static properties on
the [RegexCollection](https://github.com/YorCreative/Laravel-Scrubber/blob/main/src/Repositories/RegexCollection.php)
class.

```php
 'regex_loader' => [
        RegexCollection::$GOOGLE_API,
        RegexCollection::$AUTHORIZATION_BEARER,
        RegexCollection::$CREDIT_CARD_AMERICAN_EXPRESS,
        RegexCollection::$CREDIT_CARD_DISCOVER,
        RegexCollection::$CREDIT_CARD_VISA,
        RegexCollection::$JSON_WEB_TOKEN
    ],
```

### Creating Custom Extended Classes

The Scrubber package ships with a command to create custom extended classes and allows further refining of database scans for the Scanator. 

```bash
php artisan make:regex-class {name} 
```

This command will create a stubbed out class in `App\Scrubber\RegexCollection`. The Scrubber package will autoload
everything from the `App\Scrubber\RegexCollection` folder with the wildcard value on the `regex_loader` array in the
scrubber config file. You will need to provide a `Regex Pattern` and a `Testable String` for the class.

### Opting Into Custom Extended Classes

The `regex_loader` array takes strings, not objects. To opt in to specific custom extended regex classes, define the
class name as a string.

For example if I have a custom extended class as such:

```php
<?php

namespace App\Scrubber\RegexCollection;

use YorCreative\Scrubber\Interfaces\RegexCollectionInterface;

class TestRegex implements RegexCollectionInterface
{
    public function getPattern(): string
    {
        /**
         * @todo
         * @note return a regex pattern to detect a specific piece of sensitive data.
         */
        return '(?<=basic) [a-zA-Z0-9=:\\+\/-]{5,100}';
    }

    public function getTestableString(): string
    {
        /**
         * @todo
         * @note return a string that can be used to verify the regex pattern provided.
         */
        return 'basic f9Iu+YwMiJEsQu/vBHlbUNZRkN/ihdB1sNTU';
    }

    public function isSecret(): bool
    {
        return false;
    }
}
```

The `regex_loader` array should be defined as such:

```php
 'regex_loader' => [
        RegexCollection::$GOOGLE_API,
        RegexCollection::$AUTHORIZATION_BEARER,
        RegexCollection::$CREDIT_CARD_AMERICAN_EXPRESS,
        RegexCollection::$CREDIT_CARD_DISCOVER,
        RegexCollection::$CREDIT_CARD_VISA,
        RegexCollection::$JSON_WEB_TOKEN,
        'TestRegex'
    ],
```

## Testing

```bash
composer test
```

## Credits

- [Yorda](https://github.com/yordadev)
- [All Scanator Contributors](../../contributors)
- [All Scrubber Contributors](https://github.com/YorCreative/Laravel-Scrubber/graphs/contributors)

