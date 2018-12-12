# Whetstone

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-total-downloads]][link-downloads]
[![Total Downloads][ico-monthly-downloads]][link-downloads]
[![Total Downloads][ico-daily-downloads]][link-downloads]

A Laravel plugin package to include self-defined php artisan make commands for creating blank ToolBox classes as below.
- Helper
- Presenter
- Repository
- Service
- ViewComposer

## Installation

Via Composer

``` bash
$ composer require saberliou/whetstone
```

## Usage

``` bash
$ php artisan vendor:publish --provider="saberLiou\Whetstone\WhetstoneServiceProvider"
```

to decide the directory path of carved class by the namespace.

## Commands
``` bash
$ php artisan carve:helper
$ php artisan carve:presenter
$ php artisan carve:repository
$ php artisan carve:service
$ php artisan carve:view-composer
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email saberliou@gmail.com instead of using the issue tracker.

## Credits

- [saberLiou][link-author]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/saberliou/whetstone.svg
[ico-total-downloads]: https://img.shields.io/packagist/dt/saberliou/whetstone.svg
[ico-monthly-downloads]: https://img.shields.io/packagist/dm/saberliou/whetstone.svg
[ico-daily-downloads]: https://img.shields.io/packagist/dd/saberliou/whetstone.svg

[link-packagist]: https://packagist.org/packages/saberliou/whetstone
[link-downloads]: https://packagist.org/packages/saberliou/whetstone
[link-author]: https://github.com/saberliou