# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/smajti1/laravel-wizard/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/smajti1/laravel-wizard/compare/v1.0.0...v1.1.0
