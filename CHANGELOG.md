# Changelog
All notable changes to this project will be documented in this file.

## [1.3.4] - 2018-05-03

### Changed
- surpress warning from tl_content_sc::getAllTypes

## [1.3.3] - 2018-02-19

### Fixed
- substract padding from containerWidth for correct `makeFullScreen` calculation

## [1.3.2] - 2018-10-04

### Fixed
- invoke `config_encore.yml` only if `heimrichhannot/contao-encore-bundle` is installed (see: [#1](https://github.com/heimrichhannot/contao-subcolumns-bootstrap-bundle/issues/1)) 

## [1.3.1] - 2018-09-24

### Fixed
- always replace hypen in `sccclass` variable value `col-[NUMBER]` (e.g. `col-1`) with underscore `_`
- fallback class should not start with `col-lg-`, better use `col-` instead to support width despite of viewport breakpoints 

## [1.3.0] - 2018-09-24

### Added
- `heimrichhannot/contao-encore-bundle` support, to load frontend js on demand for performance reasons

### Changed
- rewrite frontend js from jQuery to vanilla js

## [1.2.2] - 2018-06-12

### Fixed
- `ColsetEnd` did not provide `useInside` and `inside` property from colset settings that caused invalid html markup

## [1.2.1] - 2018-06-12

### Fixed
- Check if `columnset_id` is numeric before render edit link in `tl_content` 

## [1.2.0] - 2018-06-07

### Changed
- replaced `heimrichhannot/contao-haste_plus` with `heimrichhannot/contao-utils-bundle`

## [1.1.1] - 2017-12-08

### Fixed
- classes col-xs-<digit> and offset-xs-<digit> to col-<digit> and offset-<digit>

## [1.1.0] - 2017-12-08

### Added
- Support for order classes

## [1.0.5] - 2017-12-07

### Fixed
- backend styling

## [1.0.4] - 2017-11-13

### Fixed
- backend styling

## [1.0.3] - 2017-11-10

### Added
- outside div

## [1.0.2] - 2017-11-07

### Fixed
- composer.json

## [1.0.1] - 2017-10-27

### Fixed
- docs

## [1.0.0] - 2017-10-27

### Added
- initial state
