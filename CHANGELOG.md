# Changelog

## [2.4.0](https://github.com/timber/timber/compare/v2.3.3...v2.4.0) (2026-04-17)


### Features

* Add ancestors method to Timber\Post ([#3158](https://github.com/timber/timber/issues/3158)) ([a4e02a9](https://github.com/timber/timber/commit/a4e02a9f77abdfe54f7bde2e02010db8e2778174))
* Add merge/group option for `Timber::get_terms()` ([#3213](https://github.com/timber/timber/issues/3213)) ([d493b19](https://github.com/timber/timber/commit/d493b1999915ea5fad9c204377c7ccee65f71743))
* Add missing class properties to core entities ([#3198](https://github.com/timber/timber/issues/3198)) ([389f0ed](https://github.com/timber/timber/commit/389f0ed6a8b50c61c060734ea627ce853a28f8d7))
* Add PostQuery::terms() to get terms scoped to the current query ([#3234](https://github.com/timber/timber/issues/3234)) ([f25f6ab](https://github.com/timber/timber/commit/f25f6ab1f3b64f4c310076c17b6857708e511e18))
* Add support for Twig Blocks ([#3236](https://github.com/timber/timber/issues/3236)) ([c41dffa](https://github.com/timber/timber/commit/c41dffa64a788c8ef309b83870970d537d5f1f6e))


### Bug Fixes

* "Fix content cache poisoning when excerpt() is called first ([#3208](https://github.com/timber/timber/issues/3208)) ([93e65ab](https://github.com/timber/timber/commit/93e65abf22ef12bc05ebd596becbd49c77eb4c04))
* **docs:** Fix classname for `Timber\Helper` in performance docs ([#3156](https://github.com/timber/timber/issues/3156)) ([1798ba9](https://github.com/timber/timber/commit/1798ba98c5b92e841bcce41e43e3e1c9ce061f48))
* Fix bug in image letterboxing when transparent background was converted to black ([#3233](https://github.com/timber/timber/issues/3233)) ([a9d62e8](https://github.com/timber/timber/commit/a9d62e8f9ef8ed2ae50d7bbf667b0266bba5be58))
* Fix bug when image resize with invalid input causes a fatal error ([#3235](https://github.com/timber/timber/issues/3235)) ([b66b208](https://github.com/timber/timber/commit/b66b208a924a39dba908dc5f7f5570f22db5b84e))
* Fix bug with transient cache expiration ([#3220](https://github.com/timber/timber/issues/3220)) ([091e60e](https://github.com/timber/timber/commit/091e60e935e90ba71fc9a1615a2aa7a5788b449f))
* Fix context stacking issue with `Timber\Site::switch_to_blog()` in multisite environment ([#3223](https://github.com/timber/timber/issues/3223)) ([1ff183b](https://github.com/timber/timber/commit/1ff183b91c666d0c5addac6f2b4e61063cb7badd))
* fix empty $params returning timber object ([#3179](https://github.com/timber/timber/issues/3179)) ([e732609](https://github.com/timber/timber/commit/e73260942bcb1e02428304795f6522304b29edf5))
* Fix timezone bug when transforming ACF date fields ([#3163](https://github.com/timber/timber/issues/3163)) ([513fc62](https://github.com/timber/timber/commit/513fc62fb70e7c57d388a954057ca4dffe34c7f5))
* Improve performance for reading attachment image dimensions ([#3232](https://github.com/timber/timber/issues/3232)) ([6b76d75](https://github.com/timber/timber/commit/6b76d75b8c5690ed24e2ed7f7f8a862e930f108b))
* Letterbox with transparent bg ([#3201](https://github.com/timber/timber/issues/3201)) ([22b2d51](https://github.com/timber/timber/commit/22b2d51dce313a2c736f6e5664e8ab592e50dce9))
* PHP 8.5 `null` index deprecations ([86a6b44](https://github.com/timber/timber/commit/86a6b4403ebd540749cdac170f28bd82b2724c23))
* Prevent PostsIterator from resetting the post global in the admin ([#3117](https://github.com/timber/timber/issues/3117)) ([9f14d48](https://github.com/timber/timber/commit/9f14d48382c4335267d3b04abba175bb3f7827a2))
* Refactor ACF transform hooks ([279e932](https://github.com/timber/timber/commit/279e9320ea7f852aa7cfc7290915dfd606c1e49b))
* update lint script to exclude 'tmp' directory ([a28a34d](https://github.com/timber/timber/commit/a28a34d5a665400ff4b8e8342feda7466450a249))
* Update static analysis workflow to correctly handle changed files ([533181b](https://github.com/timber/timber/commit/533181b8d1d0beb7b420c8f7dd26046847310d0f))


### Tests

* Add callable test for `timber/menu/classmap` ([f724acf](https://github.com/timber/timber/commit/f724acf6bbdbd9223b90c989ab104a6f1f87f74e))
* Enhance test suite idempotency ([b9d8260](https://github.com/timber/timber/commit/b9d8260fb5544cb02094f160dc56cfbc05bece4d))
* Fix ancestors post tests ([c49cd8f](https://github.com/timber/timber/commit/c49cd8fc2f66baf34958a305b5bffcc128b6e8dd))
* Fix issue with image test that sets a constant ([#3181](https://github.com/timber/timber/issues/3181)) ([6ae6e2a](https://github.com/timber/timber/commit/6ae6e2af88ef08ef68aabf82b7e1058d3a6ed038))
* Fix PHP 8.5 deprecation notice ([8d2788e](https://github.com/timber/timber/commit/8d2788e392d9c08c7e32018d3c3e78d879887a4d))
* Fix test pollution and WP 7.0 compatibility in test suite ([7ff0b73](https://github.com/timber/timber/commit/7ff0b73e51736988331f00cf5ea192ffb21ff726))
* Fix TimberWidgetsTest with sidebar registration and widget setup methods ([#3239](https://github.com/timber/timber/issues/3239)) ([3084850](https://github.com/timber/timber/commit/3084850b74aee0db6563cd5cce165e158c251351))
* Modernize test suite with PHPUnit 11.5+/12 and Mantle Testkit ([c854758](https://github.com/timber/timber/commit/c854758e72b9b5a3bb8b453c2f3301eb1008c14c))
* Refactor menu tests to use static factory methods for term and post creation ([1e4e027](https://github.com/timber/timber/commit/1e4e0277bbc7e9e8bd44f6fe4f3fa621943448a0))
* Update ACF field keys to ensure uniqueness ([a38fa40](https://github.com/timber/timber/commit/a38fa408069a5435ae34ea92161f05d5fd9edec8))
* Update ACF field names for consistency and clarity ([80b7986](https://github.com/timber/timber/commit/80b7986eab5e467ada518c7d6e15ed6beebde156))
* Update tests for improved idempotency and accuracy in post queries ([bd3eb80](https://github.com/timber/timber/commit/bd3eb8007e72abc55b4af1681d32888446ce65a9))
* Update tests to prevent auto-marking of current menu items ([fd7920f](https://github.com/timber/timber/commit/fd7920f7bd229a998726721470cbe337f046f54c))
* Update WPMLTest to use dynamic menu values ([251c505](https://github.com/timber/timber/commit/251c5059e61d7978048e41c829ee578c07f42df0))


### Documentation

* Expand documentation for available Twig functions and filters ([#3194](https://github.com/timber/timber/issues/3194)) ([75145db](https://github.com/timber/timber/commit/75145db88dbb37d62142a1187434e924cad7fb0e))
* fix docblock example of Site::__call() ([#3185](https://github.com/timber/timber/issues/3185)) ([9003468](https://github.com/timber/timber/commit/9003468dc19ba332e6ea483aedd1960b303a7f4b))
* Fix wrong use of apply_filters() instead of add_filter() in Performance Guide ([#3229](https://github.com/timber/timber/issues/3229)) ([a29dfc0](https://github.com/timber/timber/commit/a29dfc0034d84098c08d647f23ec81663fc09300))
* Update links in documentation and fix spelling mistakes ([#3238](https://github.com/timber/timber/issues/3238)) ([5af4b84](https://github.com/timber/timber/commit/5af4b84004b3967cdb76a8dee87a9e5d338403f1))
* WooCommerce guide - tease-product.twig fix ([#3199](https://github.com/timber/timber/issues/3199)) ([9a7d411](https://github.com/timber/timber/commit/9a7d41124ca1f7491b2f0cc2529b757eb5885470))


### Continuous Integration

* Bump Conductor PHP version ([21f7794](https://github.com/timber/timber/commit/21f7794f38b26cb7834e2a4617585be1becf8ddb))
* Update default PHP version to 8.2 in setup action ([e052190](https://github.com/timber/timber/commit/e0521907c025b56de881b3dafc7833f246644261))
* Update PHP/WP test versions ([98b010f](https://github.com/timber/timber/commit/98b010fb7485e8412d7cbbb5d885dc2d21200d8c))
* Upgrade Composer install action to version 4 ([#3215](https://github.com/timber/timber/issues/3215)) ([967541e](https://github.com/timber/timber/commit/967541e931ec7372827a4376bfeb2a08e7716681))


### Styles

* Fix coding style and rector issues ([#3164](https://github.com/timber/timber/issues/3164)) ([6ae23b1](https://github.com/timber/timber/commit/6ae23b1a24c1ec79a6db0e9ed6ae9776e5a2dad3))


### Miscellaneous Chores

* **deps-dev:** bump league/commonmark from 2.8.0 to 2.8.1 ([#3207](https://github.com/timber/timber/issues/3207)) ([93f8ece](https://github.com/timber/timber/commit/93f8ece1b02282898eeec4989fc2c40a28638252))
* **deps-dev:** bump symfony/process from 7.4.3 to 7.4.5 ([#3187](https://github.com/timber/timber/issues/3187)) ([c81733b](https://github.com/timber/timber/commit/c81733b23a4d84d900c4b97264270d19203576b8))
* **deps:** bump actions/checkout from 4 to 6 ([f1efafd](https://github.com/timber/timber/commit/f1efafd67ad4bc7715c2708bbe9ddb0a81d631cb))
* **deps:** bump actions/checkout from 5 to 6 ([#3160](https://github.com/timber/timber/issues/3160)) ([4d6b9a6](https://github.com/timber/timber/commit/4d6b9a6986366dc7fb6e41ec301bc5c6ff255536))
* **deps:** bump codecov/codecov-action from 5 to 6 ([#3224](https://github.com/timber/timber/issues/3224)) ([abb44ac](https://github.com/timber/timber/commit/abb44ac53f962c8a797238dc64b16f170809a6c4))
* **deps:** bump lycheeverse/lychee-action from 2.6.1 to 2.7.0 ([#3157](https://github.com/timber/timber/issues/3157)) ([4f7bde4](https://github.com/timber/timber/commit/4f7bde4b9580652274d259b5c514e0cad5394ea9))
* **deps:** bump lycheeverse/lychee-action from 2.7.0 to 2.8.0 ([#3205](https://github.com/timber/timber/issues/3205)) ([b64b67a](https://github.com/timber/timber/commit/b64b67a503574a47a03361a54f09b83501ef3d77))
* **deps:** bump peter-evans/create-issue-from-file from 5 to 6 ([#3154](https://github.com/timber/timber/issues/3154)) ([1bfd169](https://github.com/timber/timber/commit/1bfd169533393cbc169bbfd72a814089cbd911ac))
* **deps:** bump ramsey/composer-install from 3 to 4 ([#3214](https://github.com/timber/timber/issues/3214)) ([944c871](https://github.com/timber/timber/commit/944c871a1e0c2ecc544a981edb47e10733ff1d2b))
* **deps:** bump WyriHaximus/github-action-composer-php-versions-in-range ([#3225](https://github.com/timber/timber/issues/3225)) ([d31a521](https://github.com/timber/timber/commit/d31a521b866c3e869c2f558c6a1819d2e093ac62))
* **deps:** Update Composer dependencies ([#3165](https://github.com/timber/timber/issues/3165)) ([31c78f9](https://github.com/timber/timber/commit/31c78f9ee73952d9b7eebd749633f2fc71e15cb6))
* **deps:** update content-hash and bump easy-coding-standard to version 13.0.4 ([c451ded](https://github.com/timber/timber/commit/c451dedf1efc63c9c12c1504e8a37c8806d805f1))
* **deps:** update mantle-framework dependencies to version 1.16 ([e752c14](https://github.com/timber/timber/commit/e752c146474bf2945d938d5ef07c8fd9a5dedf0b))
* Final code quality improvements before release ([#3237](https://github.com/timber/timber/issues/3237)) ([966873e](https://github.com/timber/timber/commit/966873e1f353d47189ab7b9c3797a11b7c1843ab))
* final dependency update and rector fix ([a76daed](https://github.com/timber/timber/commit/a76daed63ae4ddef2fe73dea413c1390109fb693))
* remove deprecated escaper functions and streamline escaper setup ([#3193](https://github.com/timber/timber/issues/3193)) ([072b4fa](https://github.com/timber/timber/commit/072b4fa0b88cc69423df49004b6048e3d2d71794))
* Update changelog sections in release configuration ([223d674](https://github.com/timber/timber/commit/223d67420a2d0a399173fac57fe395e986ffaeca))
* Update CODEOWNERS ([#3166](https://github.com/timber/timber/issues/3166)) ([aa38e98](https://github.com/timber/timber/commit/aa38e984b5b21974ac8790d698c10cfe2914b60c))
* update README badges for Codecov and remove Coveralls and Scrutinizer links ([d89bff9](https://github.com/timber/timber/commit/d89bff9560a257c35a24eac0f4870da6a8d26f31))

## [2.3.3](https://github.com/timber/timber/compare/v2.3.2...v2.3.3) (2025-09-17)


### Bug Fixes

* Fix deprecation notice in `trim_words` method when `null` is passed. ([#3131](https://github.com/timber/timber/issues/3131)) ([78d928d](https://github.com/timber/timber/commit/78d928d792af7113bc032b96571ce8560a366e9c))
* Fix incorrect ACF user filter ([#3121](https://github.com/timber/timber/issues/3121)) ([6f0a6dd](https://github.com/timber/timber/commit/6f0a6dd0bc24cee4468f16e374c590401be13492))
* Make sure Post(s) exists before we call setup() ([#3114](https://github.com/timber/timber/issues/3114)) ([54cf691](https://github.com/timber/timber/commit/54cf69191a4a1a50089da8f244972203a780ec68))
* Prevent ValueError for faulty or empty sideloaded image URLs ([#3125](https://github.com/timber/timber/issues/3125)) ([c2accc4](https://github.com/timber/timber/commit/c2accc44fee762adce061696ab3dea5050d9ff9e))
* Update Helper::deprecated to use E_USER_DEPRECATED instead of the default E_USER_NOTICE ([#3113](https://github.com/timber/timber/issues/3113)) ([3cabe81](https://github.com/timber/timber/commit/3cabe81be179b943a709648a9bb1e5113bc4de2e))


### Miscellaneous Chores

* commit composer.lock file ([#3119](https://github.com/timber/timber/issues/3119)) ([e974e25](https://github.com/timber/timber/commit/e974e252851af262426319aca4991fb09afbe6b1))
* **deps:** bump actions/checkout from 4 to 5 ([#3134](https://github.com/timber/timber/issues/3134)) ([97ad233](https://github.com/timber/timber/commit/97ad2339dfe3c2c21a9bdf124d721c3b82a1a42c))
* **deps:** bump lycheeverse/lychee-action from 2.2.0 to 2.4.1 ([#3108](https://github.com/timber/timber/issues/3108)) ([edbd398](https://github.com/timber/timber/commit/edbd39809fb2d47390744cdff432b16b16950f78))
* **deps:** bump lycheeverse/lychee-action from 2.4.1 to 2.5.0 ([#3133](https://github.com/timber/timber/issues/3133)) ([ffe9901](https://github.com/timber/timber/commit/ffe9901580d4aba1bfe2398a6d7c5e4b3f7f6f44))
* **deps:** bump lycheeverse/lychee-action from 2.5.0 to 2.6.1 ([#3137](https://github.com/timber/timber/issues/3137)) ([fbafff9](https://github.com/timber/timber/commit/fbafff9263fc33b35ce77130dc60e9119c34ff2d))
* **deps:** bump tj-actions/changed-files from 46 to 47 ([#3147](https://github.com/timber/timber/issues/3147)) ([cee39ed](https://github.com/timber/timber/commit/cee39edf85d37d3790eca371549f7dad1b566cf3))
* **deps:** update .lock file and update ci steps ([af8c48f](https://github.com/timber/timber/commit/af8c48f1dc480c8c730f91d51bfb2c49d097e592))
* Update Composer dependencies ([#3130](https://github.com/timber/timber/issues/3130)) ([46942a3](https://github.com/timber/timber/commit/46942a34833c44fcd2ec5f747d9e82d8e4a7f48d))
* Update composer.lock file with PHP 8.1 ([#3132](https://github.com/timber/timber/issues/3132)) ([184d99a](https://github.com/timber/timber/commit/184d99a6767992c40467d0a625564cfe06dd611f))
* Upgrade dev dependencies ([#3118](https://github.com/timber/timber/issues/3118)) ([8293d1a](https://github.com/timber/timber/commit/8293d1aae15a1907543fe28f2db9419e73c6acee))

## [2.3.2](https://github.com/timber/timber/compare/v2.3.1...v2.3.2) (2025-05-13)


### Bug Fixes

* Fix MenuItem::is_external() returning false positives for relative URLs ([#3089](https://github.com/timber/timber/issues/3089)) ([2a14525](https://github.com/timber/timber/commit/2a145250d3ad2ea88f7fdabc20a649720e5e3cec))
* Fix typos in source code([#3077](https://github.com/timber/timber/issues/3077)) ([d7b3b80](https://github.com/timber/timber/commit/d7b3b804c3244083f6ae60e9f760f86aa512b054))
* **security:** Bump minimum required Twig version to fix security issue in Twig ([#3104](https://github.com/timber/timber/issues/3104)) ([9766a9c](https://github.com/timber/timber/commit/9766a9c1ac58b82dc2433536ab2a1a8442bc3ffa))


### Miscellaneous Chores

* **deps:** bump lycheeverse/lychee-action from 2.0.2 to 2.2.0 ([#3078](https://github.com/timber/timber/issues/3078)) ([11a74ba](https://github.com/timber/timber/commit/11a74ba68cd05a109eff14d6fcf19119743626d9))
* **deps:** bump tj-actions/changed-files from 45 to 46 ([#3105](https://github.com/timber/timber/issues/3105)) ([d8535cf](https://github.com/timber/timber/commit/d8535cf693a5bbdae55b1396b2fa24471dad22d9))

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
