# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## 0.4.0 (2020-06-10)
### Added
- [#10](https://github.com/sualko/cloud_bbb/issues/10)[#24](https://github.com/sualko/cloud_bbb/issues/24) add access policy
- move room settings to dialog

### Fixed
- log failing api request
- [#14](https://github.com/sualko/cloud_bbb/issues/14) room creation error handling
- [#20](https://github.com/sualko/cloud_bbb/issues/20) remove dependency on shares

### Misc
- run tests before push
- add yarn test
- update phpunit
- update bbb api
- ignore tx-robot commits if linting
- reduce db requests

## 0.3.2 (2020-05-24)
### Fix
- add lang files to build

### Misc
- change title for Github release

## 0.3.1 (2020-05-23)
### Fixed
- fix regression which prevents start with presentation

## 0.3.0 (2020-05-23)
### Added
- [#1](https://github.com/sualko/cloud_bbb/issues/1) use direct share for presentation (bypass password requirement for shares)
- [#2](https://github.com/sualko/cloud_bbb/issues/2) add translations (a big thank you to the awesome Nextcloud community)

### Fixed
- fix submit input field
- [#34](https://github.com/sualko/cloud_bbb/issues/34) update bbb library

### Misc
- update screenshots
- change icon order
- change shortcut icon

## 0.2.0 (2020-05-17)
### Added
- add option to store room url as shortcut
- add api check
- show warning if api is not configured
- show spinner while rooms are loading
- [#19](https://github.com/sualko/cloud_bbb/issues/19) manage recordings

### Fixed
- auto complete for api secret

### Misc
- skip merge commits
- use same code style for scripts
- enhance publish script
- update app description
- add contributing guidelines
- add code of conduct
- use adapter for bbb
- [#2](https://github.com/sualko/cloud_bbb/issues/2) prepare translation

## 0.1.2 (2020-04-29)
### Added
- show room name on join page
- [#8](https://github.com/sualko/cloud_bbb/issues/8) show menu for more file types

### Fixed
- [#17](https://github.com/sualko/cloud_bbb/issues/17) allow start/stop recording
- [#12](https://github.com/sualko/cloud_bbb/issues/12) invitation link in moderator message

### Misc
- update readme
- add travis
- enhance publish script

## [0.1.1] - 2020-04-28
### Fixed
- failed bbb api request
- define column types

## [0.1.0] - 2020-04-27
### Added
- First release