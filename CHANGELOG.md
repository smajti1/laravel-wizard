# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.7.1] - 2022-03-03
### Fixed
- wrong return phpdoc in Step.php

## [1.7.0] - 2022-03-03
### Added
- phpstan library to dev dependencies
- phpdocs with type properties

## [1.6.1] - 2022-02-28
### Added
- support `laravel` version `^9.0`

## [1.6.0] - 2021-05-22
### Added
- require min `php ^7.3` and `illuminate/http ^7.0`
- rewrite/refactor tests to be more reusable
- Add manage steps: `append`, `insert`, `replace`, `destroy`, thanks [@jamesRUS52](https://github.com/jamesRUS52) ([#8](https://github.com/smajti1/laravel-wizard/pull/8))

## [1.5.0] - 2020-11-13
### Added
- support `laravel` version `^8.0` and php `^8.0`
- update README.md see [automatic-controller-namespace-prefixing](https://laravel.com/docs/8.x/upgrade#automatic-controller-namespace-prefixing)

## [1.4.0] - 2020-04-25
### Added
- support `laravel` version `^7.0`

## [1.3.0] - 2020-02-18
### Added
- added docker container files

## [1.2.0] - 2019-10-24
### Added
- added docker container files
### Changed
- require `php ^7.2` and `illuminate/http ^6.0`
- more return type hinting

## [1.1.1] - 2017-09-23
### Added
- `Smajit1\Laravel\Wizard::createStepClass` function has been added
### Changed
- rewrite test for `Smajti1\Laravel\Wizrad` class
- `Smajti1\Laravel\Step::$label`, `Smajti1\Laravel\Step::$slug` and `Smajti1\Laravel\Step::$view` has been deprecated. 
And will be changed to non-static
### Fixed
- Type declaration of `Smajti1\Laravel\Step::$key` has been changed to mixed

## [1.1.0] - 2017-09-21
### Added
- this CHANGELOG file
### Changed
- This version required `^php-7.0` and `^laravel-5.5`
- The `Smajti1\Laravel\Wizard::lastProcessed` has been deprecated. Please use `Smajti1\Laravel\Wizard::lastProcessedIndex` instead.
- `Smajti1\Laravel\Wizard::__construct` now call `view()->share(['wizard' => $this])`
- Where it's possible function use now argument/return type declarations
- `Smajti1\Laravel\Wizard::data` from now return empty array when helper function `session` not exists
- abstract `Smajti1\Laravel\Step:rules` has been changed to public and return empty array

## 1.0.0 - 2016-11-16
### Added
- abstract `Smajti1\Laravel\Step` class to keep main information/validation rules about one step
- `Smajti1\Laravel\Wizard` class to manage steps

[Unreleased]: https://github.com/smajti1/laravel-wizard/compare/v1.1.1...HEAD
[1.1.1]: https://github.com/smajti1/laravel-wizard/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/smajti1/laravel-wizard/compare/v1.0.0...v1.1.0
[1.2.0]: https://github.com/smajti1/laravel-wizard/compare/v1.1.0...v1.2.0
[1.3.0]: https://github.com/smajti1/laravel-wizard/compare/v1.2.0...v1.3.0
[1.4.0]: https://github.com/smajti1/laravel-wizard/compare/v1.3.0...v1.4.0
[1.5.0]: https://github.com/smajti1/laravel-wizard/compare/v1.4.0...v1.5.0
[1.6.0]: https://github.com/smajti1/laravel-wizard/compare/v1.5.0...v1.6.0
[1.7.0]: https://github.com/smajti1/laravel-wizard/compare/v1.6.0...v1.7.0
