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
$ php artisan carve:helper {name} [--force]
$ php artisan carve:presenter {name} [--force]
$ php artisan carve:repository {name} [--model=] [--force]
$ php artisan carve:service {name} [--force]
$ php artisan carve:view-composer {name} [--force]
```

- `name` is the class name, which may contain sub-namespaces separated by `/` or `\` (e.g. `Admin/UserService`).
  The class is carved under the configured root and namespace (e.g. `App\Services\Admin\UserService` in `app/Services/Admin/UserService.php`).
- `--force` overwrites the class if it already exists. Otherwise the command fails with exit code 1.
- `--model` is the model bound to the repository, which is asked for if not given (and required with `--no-interaction`).
  It is resolved under the application namespace (and its `Models` namespace if the `app/Models` directory exists),
  unless it already starts with the application namespace. A leading `\` gives a fully qualified model (e.g. `--model='\Domain\Models\User'`).

## Testing

```sh
$ composer install
$ composer test
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
