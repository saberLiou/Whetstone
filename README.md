# Whetstone

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-total-downloads]][link-downloads]
[![Total Downloads][ico-monthly-downloads]][link-downloads]
[![Total Downloads][ico-daily-downloads]][link-downloads]

A Laravel package with console commands like `php artisan make` for generating self-defined PHP blank classes as below:

- Helper
- Presenter
- Repository
- Service
- ViewComposer

## Installation

Via Composer

```sh
$ composer require saberliou/whetstone --dev
```

## Usage

```sh
$ php artisan vendor:publish --provider="saberLiou\Whetstone\WhetstoneServiceProvider"
```

to decide the directory path of carved class by the namespace.

## Commands

```sh
$ php artisan carve:helper
$ php artisan carve:presenter
$ php artisan carve:repository
$ php artisan carve:service
$ php artisan carve:view-composer
```

## Security

If you discover any security related issues, please email saberliou@gmail.com instead of using the issue tracker.

## License

[MIT](LICENSE) © saberLiou

[ico-version]: https://img.shields.io/packagist/v/saberliou/whetstone.svg
[ico-total-downloads]: https://img.shields.io/packagist/dt/saberliou/whetstone.svg
[ico-monthly-downloads]: https://img.shields.io/packagist/dm/saberliou/whetstone.svg
[ico-daily-downloads]: https://img.shields.io/packagist/dd/saberliou/whetstone.svg
[link-packagist]: https://packagist.org/packages/saberliou/whetstone
[link-downloads]: https://packagist.org/packages/saberliou/whetstone
