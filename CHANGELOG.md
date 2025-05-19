# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## 2.8.0 (2025-05-19)

### Changed

- Support nextcloud 31 by @smarinier in [#338](https://github.com/littleredbutton/cloud_bbb/pull/338)
- Changed recordingReady endpoint HTTP method to POST by @atilas88 in [#263](https://github.com/littleredbutton/cloud_bbb/pull/263)

### Fix

- Group displayname might be null by @smarinier in [#310](https://github.com/littleredbutton/cloud_bbb/pull/310)
- Copy to clipboard as plain/text by @Brtbr in [#275](https://github.com/littleredbutton/cloud_bbb/pull/275)
- Double encoded html in dialog title and translations by @smarinier in [#339](https://github.com/littleredbutton/cloud_bbb/pull/339)
- Start button : fixed insufficient left space when hovering over a room by @Jerome-Herbinet in [#311](https://github.com/littleredbutton/cloud_bbb/pull/311)

## 2.7.3 (2024-12-03)
### Added

- missing icon and adapt dialog style to NC30 by @smarinier in [#304](https://github.com/littleredbutton/cloud_bbb/pull/304)

### Changed

- readme, screenshots and project files. by @smarinier in [#300](https://github.com/littleredbutton/cloud_bbb/pull/300)
- remove NC25 support by @smarinier in [#302](https://github.com/littleredbutton/cloud_bbb/pull/302)

### Fix

- max number of rooms shloud take only owned rooms in account by @smarinier in [#296](https://github.com/littleredbutton/cloud_bbb/pull/296)
- admin interface displays groups gid instead of displayname by @smarinier in [#295](https://github.com/littleredbutton/cloud_bbb/pull/295)
- style lint warnings and typescript compiler need upgrade by @smarinier in [#294](https://github.com/littleredbutton/cloud_bbb/pull/294)

### Misc

- psalm github actions by @smarinier in [#298](https://github.com/littleredbutton/cloud_bbb/pull/298)
- update php versions for tests in github actions by @smarinier in [#297](https://github.com/littleredbutton/cloud_bbb/pull/297)
- expose app capabilities by @smarinier in [#293](https://github.com/littleredbutton/cloud_bbb/pull/293)
- make clean-dev and release:build by @smarinier in [#303](https://github.com/littleredbutton/cloud_bbb/pull/303)
- psalm bump 5.0 by @smarinier in [#301](https://github.com/littleredbutton/cloud_bbb/pull/301)
- Release/2.7.3 by @smarinier in [#307](https://github.com/littleredbutton/cloud_bbb/pull/307)

## 2.7.2 (2024-10-08)
### Misc
- rebuild frontend client
- update dependencies
- update translations

## 2.7.1 (2024-09-18)
### Misc
- fix composer install in build script
- remove deprecated simple-git import

## 2.7.0 (2024-09-15)
### Added
- sharing rooms with moderators and users
- manage view of rooms for moderators and users
- videos for all users and moderators
- sharing rooms with moderators and users
- manage view of rooms for moderators and users
- videos for all users and moderators
- videos for all users and moderators
- sharing rooms with moderators and users
- manage view of rooms for moderators and users
- list all shared rooms for users and moderators

### Fixed
- no clone action for non-admin
- bug with name of shared groups
- don't use backticks
- add missing translations
- use querybuilder for user comparison

### Misc
- respect tabs
- disabled checkbox

## 2.6.0 (2024-04-28)
### Added
- add Search Provider
- bump Nextcloud version (max 28)

## 2.5.0 (2023-07-27)
### Added
- bump Nextcloud version (min 25, max 27)

### Fixed
- fix oversized avatar
- fix unscrollable room list

## 2.4.0 (2022-11-10)
### Added
- bump Nextcloud version (min 23, max 25)
- allow to send files to running presentation (bbb > 2.5)

### Misc
- drop tests for php 7.3
- replace deprecated package
- update php cs fixer

## 2.3.1 (2022-07-28)
### Fixed
- broken build
- convert boolean columns to not nullable

## 2.3.0 (2022-07-24)
### Added
- [#191](https://github.com/sualko/cloud_bbb/issues/191) clone rooms
- bump Nextcloud version (min 22, max 24)
- disable file transfer if room is running
- [#79](https://github.com/sualko/cloud_bbb/issues/79) track room state
- [#5](https://github.com/sualko/cloud_bbb/issues/5) use modal to send file
- [#194](https://github.com/sualko/cloud_bbb/issues/194) add support for analytics callback url
- [#192](https://github.com/sualko/cloud_bbb/issues/192) add access mode to send all users to lobby

### Fixed
- add feedback to send file action
- image loader for webpack 5
- change button color

## 2.2.0 (2022-01-17)
### Added
- add optional avatar cache
- add command to clear avatar cache

### Fixed
- use injection for random generator

### Misc
- remove deprecated api calls
- enable app for unit tests

## 2.1.0 (2021-12-08)
### Added
- bump max nc version to 23
- [#184](https://github.com/sualko/cloud_bbb/issues/184) add origin meta params
- [#176](https://github.com/sualko/cloud_bbb/issues/176) add admin settings for default skip media
- [#152](https://github.com/sualko/cloud_bbb/issues/152) add option for all users to join a meeting muted (#173)

### Fixed
- [#183](https://github.com/sualko/cloud_bbb/issues/183) replace non-ascii chars for filenames
- [#174](https://github.com/sualko/cloud_bbb/issues/174) sort recordings by date
- fix collapsible arrow
- use settings icon for room settings
- log response message for create request

### Misc
- update dependencies

## 2.0.0 (2021-07-28)
### Added
- change default navigation label to BBB
- [#171](https://github.com/sualko/cloud_bbb/issues/171) bump Nextcloud versions

### Fixed
- use official circle api
- change boolean columns to nullable (#166)

### Misc
- remove composer.phar
- update nc api
- update psalm baseline
- BREAKING [#116](https://github.com/sualko/cloud_bbb/issues/116) update php dependencies
- update to latest nc code style

## 1.4.2 (2021-07-03)
### Fixed
- [#155](https://github.com/sualko/cloud_bbb/issues/155) set default values on room creation

### Misc
- update dependencies
- fix formatting

## 1.4.1 (2021-04-30)
### Fixed
- [#147](https://github.com/sualko/cloud_bbb/issues/147) set layout params only for clean layout

### Misc
- update translations

## 1.4.0 (2021-04-25)
### Added
- add avatars for Nextcloud users (requires BBB server 2.3)
- add room option to disable listen only mode
- add room option to skip audio check and video preview on first join (requires BBB server 2.3)
- add room option to hide user list, chat area and presentation by default
- add admin option to use Nextcloud theme colors in BBB

### Fixed
- [#130](https://github.com/sualko/cloud_bbb/issues/130) setting registration for NC 19
- [#131](https://github.com/sualko/cloud_bbb/issues/131) delete only owned rooms from deleted user

### Misc
- add IGL as sponsor
- fix build script
- adapt restriction table

## 1.3.0 (2021-03-09)
### Added
- [#124](https://github.com/sualko/cloud_bbb/issues/124) make navigation label configurable
- bump Nextcloud version

### Fixed
- do not register file action without authentication
- always show missing configuration message
- [#125](https://github.com/sualko/cloud_bbb/issues/125) prevent group restriction
- psalm errors
- translate settings header
- increase qr code error correction
- adapt restriction table to common style

### Misc
- add link to contributor guide
- use psalm
- add admin screenshot
- move to github actions

## 1.2.0 (2021-02-01)
### Added
- add option to invite moderators via URL (warning: if you use the URL shortener, make sure the moderator token is forwarded)
- close edit dialog on overlay click
- add copy-to-clipboard for readonly inputs
- show exact match first in sharing widget
- add room url with qr code to edit dialog

### Fixed
- add missing dark chain icon
- fix translation of access options
- enhance accessibility and usability
- add missing button titles

### Misc
- update app description
- update js dependencies
- add description to share with input
- add gitattributes
- include changelog in build

## 1.1.4 (2020-12-16)
### Fixed
- room join failure for php < 7.4

## 1.1.3 (2020-12-15)
### Fixed
- [#103](https://github.com/sualko/cloud_bbb/issues/103) parameter type
- [#98](https://github.com/sualko/cloud_bbb/issues/98) manager in dark mode
- removal of url shortener option

## 1.1.2 (2020-11-19)
### Fixed
- [#102](https://github.com/sualko/cloud_bbb/issues/102) remote link generation
- [#96](https://github.com/sualko/cloud_bbb/issues/96) app init

## 1.1.1 (2020-11-03)
### Fixed
- [#92](https://github.com/sualko/cloud_bbb/issues/92) wait for file action api

### Misc
- update languages

## 1.1.0 (2020-09-29)
### Added
- [#57](https://github.com/sualko/cloud_bbb/issues/57) add support for URL shortener
- [#62](https://github.com/sualko/cloud_bbb/issues/62) create meeting activities
- show access mode in room overview
- show room shared icon in room overview
- add support for Nextcloud 20

### Fixed
- [#80](https://github.com/sualko/cloud_bbb/issues/80) clean up after user was deleted
- [#73](https://github.com/sualko/cloud_bbb/issues/73) drop down not clickable
- translate moderator message
- [#78](https://github.com/sualko/cloud_bbb/issues/78) plural translation
- quoted column names in sql query
- unify error handling for room and restriction
- [#76](https://github.com/sualko/cloud_bbb/issues/76) record deletion with scalelight
- [#85](https://github.com/sualko/cloud_bbb/issues/85) set password on room creation

### Misc
- add meeting events
- order imports
- update composer dependencies

## 1.0.2 (2020-09-04)
### Fixed
- [#70](https://github.com/sualko/cloud_bbb/issues/70) incompatibility with circle v0.18.x

### Misc
- update languages

## 1.0.1 (2020-09-02)
### Fixed
- fix restrictions on postgres
- [#68](https://github.com/sualko/cloud_bbb/issues/68) fix missing personal settings section on some instances

### Misc
- add RestrictionService test
- use identical comparision

## 1.0.0 (2020-09-01)
### Added
- add link to room page to log in into Nextcloud
- [#23](https://github.com/sualko/cloud_bbb/issues/23) add option to require moderator to start a room
- [#61](https://github.com/sualko/cloud_bbb/issues/61) add ability to share rooms with circles
- [#43](https://github.com/sualko/cloud_bbb/issues/43) add admin setting to restrict rooms
- [#31](https://github.com/sualko/cloud_bbb/issues/31) add option to show manager in app navigation

### Fixed
- use custom 404 page
- fix no permission status code
- reset search field after selection
- [#65](https://github.com/sualko/cloud_bbb/issues/65) fix user/group selection with exact match
- hide success message after 3 sec in admin settings
- [#58](https://github.com/sualko/cloud_bbb/issues/58)[#49](https://github.com/sualko/cloud_bbb/issues/49) fix multiple issues with user/group dropdown
- [#64](https://github.com/sualko/cloud_bbb/issues/64) fix room configuration after room creation
- [#47](https://github.com/sualko/cloud_bbb/issues/47) bypass form action error

### Misc
- update feature list
- add TU Graz as sponsor
- update js dependencies
- fix scss style
- remove obsolete config injection

## 0.5.1 (2020-06-19)
### Fixed
- allow admin to start room with presentation
- [#50](https://github.com/sualko/cloud_bbb/issues/50) fix error which prevents guests from joining a room

### Misc
- use Nextcloud coding standard

## 0.5.0 (2020-06-18)
### Added
- [#33](https://github.com/sualko/cloud_bbb/issues/33) add option to share room
- add option to set everyone as moderator
- [#25](https://github.com/sualko/cloud_bbb/issues/25) restrict room access to user and groups
- [#33](https://github.com/sualko/cloud_bbb/issues/33) allow to define user and groups as moderator

### Fixed
- trim user supplied displayname
- show room loading error
- max dialog height

### Misc
- update screenshots
- add integration test for room mapper
- expand name cell
- remove unused controller

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
