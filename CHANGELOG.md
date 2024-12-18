# Changelog

## [2.3.1](https://github.com/timber/timber/compare/v2.3.0...v2.3.1) (2024-12-18)


### Bug Fixes

* fix avatar test ([#3071](https://github.com/timber/timber/issues/3071)) ([0e65e54](https://github.com/timber/timber/commit/0e65e54897fead31d3ba5eb3065242e294dcf51b))
* Fix bug with Attachment::path() method ([#3073](https://github.com/timber/timber/issues/3073)) ([5434dde](https://github.com/timber/timber/commit/5434dde5889f174bf1d36c0686b94b180d93fa5d))
* fix get location by id in Timber::get_menu_location() ([#3066](https://github.com/timber/timber/issues/3066)) ([5b33ba8](https://github.com/timber/timber/commit/5b33ba8475361e1e31974ee42a7e9a27e34e8b65))
* timber::get_menu(0) returns alphabetically first menu instead of nothing ([#3070](https://github.com/timber/timber/issues/3070)) ([d278f95](https://github.com/timber/timber/commit/d278f954f672c0f3bb56e0a40e5d0acf40fc0608))
* update twig & twig/cache-extra dependency to version 3.17 to fix unit tests ([cbac2e0](https://github.com/timber/timber/commit/cbac2e0fcf0b01c3bc3eaaf7de01bc721003b926))
* Use correct deprecation_info for Twig callables ([#3064](https://github.com/timber/timber/issues/3064)) ([72a013e](https://github.com/timber/timber/commit/72a013e604ea098cb2819906a7be3454f4a3802d))

## [2.3.0](https://github.com/timber/timber/compare/v2.2.0...v2.3.0) (2024-11-08)


### Features

* Add support for avif image format [#3015](https://github.com/timber/timber/issues/3015) ([#3019](https://github.com/timber/timber/issues/3019)) ([92716c1](https://github.com/timber/timber/commit/92716c1b2a9ecee090df9bebfcfcf5acf3192fc5))


### Bug Fixes

* add more default arguments to PagesMenu::build method ([#3050](https://github.com/timber/timber/issues/3050)) ([c7aea5d](https://github.com/timber/timber/commit/c7aea5d9b800836bfa51ef11f2b7493d5a8ce91b))
* Apply Rector code standard on MenuItem.php ([5d64d9a](https://github.com/timber/timber/commit/5d64d9a390664de0e32aa51a7c69c5c4964f9559))
* Fix menu location compatibility with WPML ([#2733](https://github.com/timber/timber/issues/2733)) ([8603855](https://github.com/timber/timber/commit/86038557c683fa65e0564e078c600ea2fc3ea446))
* Fix URI to FS parsing in ImageHelper ([#3027](https://github.com/timber/timber/issues/3027)) ([87d3ef4](https://github.com/timber/timber/commit/87d3ef4e81f55ddb783ad6eb7da4c96ca9c643aa)), closes [#3024](https://github.com/timber/timber/issues/3024)
* fixes an issue where in some cases images would not be rouned properly by image operations. This could lead to artifacts in the generated images. ([#3046](https://github.com/timber/timber/issues/3046)) ([10ab23d](https://github.com/timber/timber/commit/10ab23d5cfcd1b1e777a5f4a65f8e983e272b73d))
* Run CS fixes on codebase ([#3047](https://github.com/timber/timber/issues/3047)) ([48dc3fc](https://github.com/timber/timber/commit/48dc3fc5a9104251f440af6b65f6a622660a91dc))


### Miscellaneous Chores

* add several files to export-ignore ([0cd0cdf](https://github.com/timber/timber/commit/0cd0cdf3e09438f54b8e65bc408b08a98e42cdd7))
* **deps:** bump lycheeverse/lychee-action from 1.10.0 to 2.0.2 ([#3053](https://github.com/timber/timber/issues/3053)) ([480534f](https://github.com/timber/timber/commit/480534fc95cf7d0b92af0ffc1f64805a352406ea))
* **deps:** bump tj-actions/changed-files from 44 to 45 ([#3031](https://github.com/timber/timber/issues/3031)) ([880c0ff](https://github.com/timber/timber/commit/880c0ff23df5e7952cc6499d0043996a4d2c89bf))
* inherit Funding from .github repo ([5623a79](https://github.com/timber/timber/commit/5623a797483542f496df0c3002cc211d9838960e))

## [2.2.0](https://github.com/timber/timber/compare/v2.1.0...v2.2.0) (2024-05-15)


### Features

* Introduce Rector to upgrade code for PHP 8.1 ([#2977](https://github.com/timber/timber/issues/2977)) ([9edf999](https://github.com/timber/timber/commit/9edf999a6d4a12f6a0e96ffaaa38b3e48dc3ea2f))
* Upgrade Timber requirements and testing (PHP 8.1/WP 6.2/Twig 3.5) ([#2970](https://github.com/timber/timber/issues/2970)) ([a2f0f07](https://github.com/timber/timber/commit/a2f0f07e9423f66c1998b3aabccfc2d803512c33))


### Bug Fixes

* allow Timber\PostExcerpt::read_more to accept bool value ([#2937](https://github.com/timber/timber/issues/2937)) ([85e2a32](https://github.com/timber/timber/commit/85e2a32e79616f937a19f1521c1378755c0e5014))
* Fix a bug with URL check for avatars ([#3002](https://github.com/timber/timber/issues/3002)) ([456c24e](https://github.com/timber/timber/commit/456c24e7a438569d9e7fefd351f4f68cd3f7394d))
* Fix deprecation notice since twig 3.10 to now use EscaperRuntime instead of EscaperExtension ([#2997](https://github.com/timber/timber/issues/2997)) ([295349b](https://github.com/timber/timber/commit/295349b0316640014a0841acef0f185bbdb8bd2e))
* Fix problem when an empty ACF taxonomy relationship field transform loads all terms instead of none. ([#2960](https://github.com/timber/timber/issues/2960)) ([f95b82a](https://github.com/timber/timber/commit/f95b82af7cc8fa79ef8e10a75dbf62477b073ada))
* fix regression where crops with the default crop setting would s… ([#2998](https://github.com/timber/timber/issues/2998)) ([8090247](https://github.com/timber/timber/commit/809024798d720fc743fac807431144605bb1cea3))
* Fix typos in codebase ([#2968](https://github.com/timber/timber/issues/2968)) ([e40ceb3](https://github.com/timber/timber/commit/e40ceb3a72c7decaa597f6e2cdb27b4d1f3f5420))
* Improve doing_it_wrong messages for using deprecated parameters in Timber::get_attachment() and Timber::get_image() ([#2999](https://github.com/timber/timber/issues/2999)) ([e6cdf7e](https://github.com/timber/timber/commit/e6cdf7ef584f43de585d0b437cb250179d1a0045))
* Remove security patch not needed in PHP 8 ([#2983](https://github.com/timber/timber/issues/2983)) ([8a30865](https://github.com/timber/timber/commit/8a30865b753b51771b524cf8745f5ee362a7de85))
* Update admin notice for minimum required WordPress version ([#3001](https://github.com/timber/timber/issues/3001)) ([66e92a5](https://github.com/timber/timber/commit/66e92a526622afeb3eba3da52f47db2b8ae6735e))


### Miscellaneous Chores

* **deps:** bump lycheeverse/lychee-action from 1.9.3 to 1.10.0 ([#2980](https://github.com/timber/timber/issues/2980)) ([dd34720](https://github.com/timber/timber/commit/dd3472030a25ee59f760abe95c48c5fabcf54abb))
* **deps:** bump tj-actions/changed-files from 42 to 44 ([#2959](https://github.com/timber/timber/issues/2959)) ([66eabe2](https://github.com/timber/timber/commit/66eabe28a32b40d9eadaae6864c6bf7c3f8144c4))
* set proper return types on build methods ([#2976](https://github.com/timber/timber/issues/2976)) ([6b72908](https://github.com/timber/timber/commit/6b72908d473188aa756d0b8ebb6641fae747e0b4))
* Update all links in the codebase and documentation to https ([#2947](https://github.com/timber/timber/issues/2947)) ([05af54f](https://github.com/timber/timber/commit/05af54f7f5463c737299fb9b0512f79b334d2e94))

## [2.1.0](https://github.com/timber/timber/compare/2.0.0...v2.1.0) (2024-04-10)


### Features

* add  filter to cache methods ([#2878](https://github.com/timber/timber/issues/2878)) ([b347677](https://github.com/timber/timber/commit/b34767750ba5e1e3dc67942d4f42bf0def3e28aa))
* add filter for sideloaded images basename ([e4ff72f](https://github.com/timber/timber/commit/e4ff72f451e11b05887179086e4bb5a82d799184))
* add filter to $output before it is cached ([#2910](https://github.com/timber/timber/issues/2910)) ([d1356fd](https://github.com/timber/timber/commit/d1356fd550ccb9b2f9679789e345e22283f8c33c))
* add is_current and profile_link methods ([#2924](https://github.com/timber/timber/issues/2924)) ([b048da8](https://github.com/timber/timber/commit/b048da899df98ecdcfc8a04c25819fec489251a2))
* Add WP escapers via Twig filters ([#2933](https://github.com/timber/timber/issues/2933)) ([a88aa00](https://github.com/timber/timber/commit/a88aa006fe18cc329170859707462c6a1927b500))
* Allow pagination object to be generated using `$prefs` only ([99219a9](https://github.com/timber/timber/commit/99219a97f328ff5369510996c5cc0d15d551e42e))
* allow pagination object to be generated using $prefs only ([2834fd4](https://github.com/timber/timber/commit/2834fd457375f4e8467839505cdd91fe5198c39c))
* bump php-stubs/acf-pro-stubs to ^6.0 ([ac17052](https://github.com/timber/timber/commit/ac17052787d2d97eb0f37d477ea14e15b74b00f7))
* update ECS config and apply standards ([#2893](https://github.com/timber/timber/issues/2893)) ([71111e1](https://github.com/timber/timber/commit/71111e1dc0eabc78b11f45b095c638fa45374044))


### Bug Fixes

* Add patch for PHAR deserialization vulnerability for Timber 2.x (security advisory GHSA-6363-v5m4-fvq3) ([13c6b0f](https://github.com/timber/timber/commit/13c6b0f60346304f2eed4da1e0bb51566518de4a))
* adding classes in `MenuItem` ([#2905](https://github.com/timber/timber/issues/2905)) ([7e00eeb](https://github.com/timber/timber/commit/7e00eeba682e54f13a9064359306580e0e628f52))
* Allow overwrite of default avatar in comments. ([#2786](https://github.com/timber/timber/issues/2786)) ([9c6e0e3](https://github.com/timber/timber/commit/9c6e0e3035b6312de63609c65a7d38b5735d8721)), closes [#2468](https://github.com/timber/timber/issues/2468)
* **docs:** Simplify an if-check in the ACF docs ([96d2874](https://github.com/timber/timber/commit/96d287470a16cab3cc4b14aa373c88423816b3cb))
* file permissions ([#2842](https://github.com/timber/timber/issues/2842)) ([337d54d](https://github.com/timber/timber/commit/337d54d2727d3c1a511377e1b1a3c367a6ed006b))
* fix minor codestyle issue in loader.php to make easy-coding-standard happy ([#2950](https://github.com/timber/timber/issues/2950)) ([6e8b6ab](https://github.com/timber/timber/commit/6e8b6ab375df317207ea658cccb12cfb4710e64b))
* ignore acf_get_field_type void errors ([441ef9e](https://github.com/timber/timber/commit/441ef9e82478cb250373938972bc09c0c1acf154))
* make PostIterator-&gt;last_post nullable ([#2918](https://github.com/timber/timber/issues/2918)) ([064dde7](https://github.com/timber/timber/commit/064dde77998288c10cd39c26914a7e5ea934e04b))
* Prevent unneeded blog switching in multisite env. ([#2781](https://github.com/timber/timber/issues/2781)) ([d81f995](https://github.com/timber/timber/commit/d81f9951ae41b27e1134b8bf6ae7354a9bae0546))
* split test running for integrations (plugins) ([#2904](https://github.com/timber/timber/issues/2904)) ([8d03809](https://github.com/timber/timber/commit/8d03809fe2ded38f497dab7c2347fa48a8de10b9))
* tests failing since Twig 3.8.0 ([#2895](https://github.com/timber/timber/issues/2895)) ([f4a233e](https://github.com/timber/timber/commit/f4a233ec6b3afacee5db592725090d775d654de1))
* **tests:** fix missing constants in static analysis test ([ae50ccd](https://github.com/timber/timber/commit/ae50ccd25db099d18a93c96b20ecfc82e86a5c58))
* **test:** use new filter in tests ([c12e9af](https://github.com/timber/timber/commit/c12e9af6027f5bed6c418c2c933c3492e7d68d3e))
* undefined property ([9e8409e](https://github.com/timber/timber/commit/9e8409e69985925e256d7d48bb855dd95708f84f))
* unnecessary lowercasing parameters ([#2877](https://github.com/timber/timber/issues/2877)) ([664ea62](https://github.com/timber/timber/commit/664ea625504a0d781ac2efeb5e2b8a39c5ac3e70))


### Reverts

* revert changing property name ([a7b019b](https://github.com/timber/timber/commit/a7b019b75d5358c35b4237c39817d5a830e8dce2))


### Miscellaneous Chores

* Add script descriptions in composer file ([#2951](https://github.com/timber/timber/issues/2951)) ([5785128](https://github.com/timber/timber/commit/5785128c1fbb817e146bbf5fdecc270c1856bae8))
* add Timber authors ([567475e](https://github.com/timber/timber/commit/567475eb396eec7d3c80715e7db7880d2875f338))
* Create SECURITY.md ([#2939](https://github.com/timber/timber/issues/2939)) ([be36065](https://github.com/timber/timber/commit/be360651eedad4e99a59d185ecaf04d7ab6a3b11))
* **deps:** bump lycheeverse/lychee-action from 1.8.0 to 1.9.1 ([1ca79af](https://github.com/timber/timber/commit/1ca79aff20b5ac821cded348a2e4ed151bb58777))
* **deps:** bump lycheeverse/lychee-action from 1.9.1 to 1.9.3 ([#2907](https://github.com/timber/timber/issues/2907)) ([eecfb03](https://github.com/timber/timber/commit/eecfb039dd7fbf3020cdf0310f6f96b6306616b0))
* **deps:** bump peter-evans/create-issue-from-file from 4 to 5 ([#2906](https://github.com/timber/timber/issues/2906)) ([64703f8](https://github.com/timber/timber/commit/64703f86ae16d68b5706cd3bfd001a34ec821153))
* **deps:** bump ramsey/composer-install from 2 to 3 ([#2941](https://github.com/timber/timber/issues/2941)) ([97010c4](https://github.com/timber/timber/commit/97010c47a27788c262b214a62d69a530a802b6c0))
* **deps:** bump tj-actions/changed-files from 39 to 42 ([964f11a](https://github.com/timber/timber/commit/964f11aa496f577179e03f1afadbd1da1e7a5d1b))
* remove Lando config ([#2899](https://github.com/timber/timber/issues/2899)) ([6fa8ffc](https://github.com/timber/timber/commit/6fa8ffcdb51d286169b47e29ddf54f26568da95a))
* update links in contributing.md ([3b2c855](https://github.com/timber/timber/commit/3b2c855495b7877a6967537c68054aaebf972eea))
