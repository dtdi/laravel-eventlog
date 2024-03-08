# Export Process Mining Event Logs from your Laravel Application with  laravel-eventlog

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dtdi/laravel-eventlog.svg?style=flat-square)](https://packagist.org/packages/dtdi/laravel-eventlog)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dtdi/laravel-eventlog/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dtdi/laravel-eventlog/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dtdi/laravel-eventlog/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dtdi/laravel-eventlog/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dtdi/laravel-eventlog.svg?style=flat-square)](https://packagist.org/packages/dtdi/laravel-eventlog)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

Implements [OCEL 1.0](https://www.ocel-standard.org/1.0/)

## Support us

[<img src="https://dtdi.de/ads/laravel-eventlog.png" width="419px" />](https://dtdi.de/i.php?repo=laravel-eventlog)

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/yellow_img.png)](https://www.buymeacoffee.com/dtdi)


## Installation

You can install the package via composer:

```bash
composer require dtdi/laravel-eventlog
```


You should publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-eventlog-config"
```

This is the contents of the published config file:

```php
return [

    /*
     * This model will be used as base event.
     * and extend Illuminate\Database\Eloquent\Model.
     */
    'event_model' => null,

    'event_id' => 'id',
    'timestamp' => 'created_at',
    'event_name' => 'action_type',

];
```

## Usage

In code

```php
$logPath = eventlog()->setupForSnipeIt()->setLogExporter(new OCEL1)->write();
```

Using the `php artisan pm:dump` command.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tobias Fehrer](https://github.com/dtdi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
