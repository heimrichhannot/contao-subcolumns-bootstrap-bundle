# Changelog

All notable changes to this project will be documented in this file.

## [1.11.2] - 2023-09-14
- Fixed: subcolumn type not updated in settings (please run migration after update)

## [1.11.1] - 2023-09-13
- Fixed: typo in subcolumn type constants
- Fixed: php8 warnings
- Changed: columns now default to class col-12 with col-md-{n}

## [1.10.4] - 2023-08-28
- Fixed: warnings

## [1.10.3] - 2023-04-10
- Fixed: exception
- Fixed: warnings

## [1.10.2] - 2023-04-24
- Fixed: warnings

## [1.10.1] - 2023-04-06
- Fixed: col full js loaded by default

## [1.10.0] - 2023-01-11
- Added: encore contracts support ([#11])
- Changed: requires at least contao 4.9
- Changed: requires at least php 7.4

## [1.9.5] - 2023-01-11
- Fixed: warning with php 8

## [1.9.4] - 2023-01-11
- Fixed: warning with php 8

## [1.9.3] - 2022-07-21
- Fixed: php8 warnings in templates

## [1.9.2] - 2022-07-12
- Changed: raise subcolumns dependency
- Fixed: avoid array offset on php8

## [1.9.1] - 2022-05-30
- Fixed: warning in php 8

## [1.9.0] - 2022-02-14

- Fixed: array index issues in php 8+
- Added: heimrichhannot fork of felixpfeiffer subcolumns module since it's abandoned

## [1.8.0] - 2021-11-02
- Added: warning when database rows are missing
- Fixed: some deprecations

## [1.7.1] - 2021-09-29
- Fixed: another "Warning: Invalid argument supplied for foreach()"

## [1.7.0] - 2021-09-23
- Changed: corrected license to LGPL-3.0-or-later and added license file
- Fixed: "Warning: Invalid argument supplied for foreach()"

## [1.6.1] - 2021-09-10

- Fixed: support for bootstrap 5

## [1.6.0] - 2021-08-31

- Added: support for php 8

## [1.5.0] - 2021-08-18

- Added: support for bootstrap 5 (xxl breakpoint)

## [1.4.7] - 2021-02-08

- fixed backend css style for contao 4.9

## [1.4.6] - 2020-04-27

- fixed backend css style for contao 4.9

## [1.4.5] - 2020-03-17

- added backend css style

## [1.4.4] - 2020-03-17

- added backend css style

## [1.4.3] - 2020-01-07

- added missing translations (see #4, thanks to @daniel-nemeth)

## [1.4.2] - 2019-11-27

- removed symfony framework bundle dependency (see #3)
- fixed a warning

## [1.4.1] - 2019-10-02

### Fixed

- optional support for onemarshall/contao-aos

## [1.4.0] - 2019-10-01

### Added

- optional support for onemarshall/contao-aos

## [1.3.7] - 2019-09-18

### Fixed

- classes order-xs-<digit> to order-<digit>

## [1.3.6] - 2019-05-20

### Fixed

- remove `module` export from `contao-subcolumns-bootstrap-bundle.fe.js` files

## [1.3.5] - 2019-05-03

### Changes

- suppress warnings in compile method of content elements

## [1.3.4] - 2019-05-03

### Changes

- suppress warnings from tl_content_sc::getAllTypes

## [1.3.3] - 2019-02-19

### Fixed

- substract padding from containerWidth for correct `makeFullScreen` calculation

## [1.3.2] - 2018-10-04

### Fixed

- invoke `config_encore.yml` only if `heimrichhannot/contao-encore-bundle` is installed (
  see: [#1](https://github.com/heimrichhannot/contao-subcolumns-bootstrap-bundle/issues/1))

## [1.3.1] - 2018-09-24

### Fixed

- always replace hypen in `sccclass` variable value `col-[NUMBER]` (e.g. `col-1`) with underscore `_`
- fallback class should not start with `col-lg-`, better use `col-` instead to support width despite of viewport
  breakpoints

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


[#11]: https://github.com/heimrichhannot/contao-subcolumns-bootstrap-bundle/pull/11
