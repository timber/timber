# Changelog

## [2.6.0](https://github.com/timber/timber/compare/v2.5.2...v2.6.0) (2026-11-25)


### Features

* add  filter to cache methods ([#2878](https://github.com/timber/timber/issues/2878)) ([b347677](https://github.com/timber/timber/commit/b34767750ba5e1e3dc67942d4f42bf0def3e28aa))
* Add ancestors method to Timber\Post ([#3158](https://github.com/timber/timber/issues/3158)) ([a4e02a9](https://github.com/timber/timber/commit/a4e02a9f77abdfe54f7bde2e02010db8e2778174))
* add filter for sideloaded images basename ([a602ec5](https://github.com/timber/timber/commit/a602ec5e8c42ebbe8c35ca3b05dc2c53ee8bfd3f))
* add filter for sideloaded images basename ([e4ff72f](https://github.com/timber/timber/commit/e4ff72f451e11b05887179086e4bb5a82d799184))
* add filter to $output before it is cached ([#2910](https://github.com/timber/timber/issues/2910)) ([d1356fd](https://github.com/timber/timber/commit/d1356fd550ccb9b2f9679789e345e22283f8c33c))
* Add ImageHelper filters to customize paths ([ebc54fc](https://github.com/timber/timber/commit/ebc54fce4bfb74429b1608f7643121e848eb9a13))
* add is_current and profile_link methods ([#2924](https://github.com/timber/timber/issues/2924)) ([b048da8](https://github.com/timber/timber/commit/b048da899df98ecdcfc8a04c25819fec489251a2))
* Add merge/group option for `Timber::get_terms()` ([#3213](https://github.com/timber/timber/issues/3213)) ([d493b19](https://github.com/timber/timber/commit/d493b1999915ea5fad9c204377c7ccee65f71743))
* Add missing class properties to core entities ([#3198](https://github.com/timber/timber/issues/3198)) ([389f0ed](https://github.com/timber/timber/commit/389f0ed6a8b50c61c060734ea627ce853a28f8d7))
* Add post excerpt content filter ([#3247](https://github.com/timber/timber/issues/3247)) ([a8aa17e](https://github.com/timber/timber/commit/a8aa17e7a363025eb59f58561282e8d1cf4568a1))
* Add PostQuery::terms() to get terms scoped to the current query ([#3234](https://github.com/timber/timber/issues/3234)) ([f25f6ab](https://github.com/timber/timber/commit/f25f6ab1f3b64f4c310076c17b6857708e511e18))
* Add support for avif image format [#3015](https://github.com/timber/timber/issues/3015) ([#3019](https://github.com/timber/timber/issues/3019)) ([92716c1](https://github.com/timber/timber/commit/92716c1b2a9ecee090df9bebfcfcf5acf3192fc5))
* Add support for Twig Blocks ([#3236](https://github.com/timber/timber/issues/3236)) ([c41dffa](https://github.com/timber/timber/commit/c41dffa64a788c8ef309b83870970d537d5f1f6e))
* Add WP escapers via Twig filters ([#2933](https://github.com/timber/timber/issues/2933)) ([a88aa00](https://github.com/timber/timber/commit/a88aa006fe18cc329170859707462c6a1927b500))
* Allow pagination object to be generated using `$prefs` only ([99219a9](https://github.com/timber/timber/commit/99219a97f328ff5369510996c5cc0d15d551e42e))
* allow pagination object to be generated using $prefs only ([2834fd4](https://github.com/timber/timber/commit/2834fd457375f4e8467839505cdd91fe5198c39c))
* bump php-stubs/acf-pro-stubs to ^6.0 ([829c3c3](https://github.com/timber/timber/commit/829c3c362186c7a81f4607f668b9cc6cae1bea05))
* bump php-stubs/acf-pro-stubs to ^6.0 ([ac17052](https://github.com/timber/timber/commit/ac17052787d2d97eb0f37d477ea14e15b74b00f7))
* Introduce Rector to upgrade code for PHP 8.1 ([#2977](https://github.com/timber/timber/issues/2977)) ([9edf999](https://github.com/timber/timber/commit/9edf999a6d4a12f6a0e96ffaaa38b3e48dc3ea2f))
* update ECS config and apply standards ([#2893](https://github.com/timber/timber/issues/2893)) ([71111e1](https://github.com/timber/timber/commit/71111e1dc0eabc78b11f45b095c638fa45374044))
* Upgrade Timber requirements and testing (PHP 8.1/WP 6.2/Twig 3.5) ([#2970](https://github.com/timber/timber/issues/2970)) ([a2f0f07](https://github.com/timber/timber/commit/a2f0f07e9423f66c1998b3aabccfc2d803512c33))


### Bug Fixes

* "Fix content cache poisoning when excerpt() is called first ([#3208](https://github.com/timber/timber/issues/3208)) ([93e65ab](https://github.com/timber/timber/commit/93e65abf22ef12bc05ebd596becbd49c77eb4c04))
* add more default arguments to PagesMenu::build method ([#3050](https://github.com/timber/timber/issues/3050)) ([c7aea5d](https://github.com/timber/timber/commit/c7aea5d9b800836bfa51ef11f2b7493d5a8ce91b))
* Add note about PostsIterator and removal of timber/class/posts_iterator filter ([9f90df5](https://github.com/timber/timber/commit/9f90df508d117eb872f3c5df330dbf100ad64603))
* Add patch for PHAR deserialization vulnerability for Timber 2.x (security advisory GHSA-6363-v5m4-fvq3) ([13c6b0f](https://github.com/timber/timber/commit/13c6b0f60346304f2eed4da1e0bb51566518de4a))
* adding classes in `MenuItem` ([#2905](https://github.com/timber/timber/issues/2905)) ([7e00eeb](https://github.com/timber/timber/commit/7e00eeba682e54f13a9064359306580e0e628f52))
* Allow overwrite of default avatar in comments. ([#2786](https://github.com/timber/timber/issues/2786)) ([9c6e0e3](https://github.com/timber/timber/commit/9c6e0e3035b6312de63609c65a7d38b5735d8721)), closes [#2468](https://github.com/timber/timber/issues/2468)
* allow Timber\PostExcerpt::read_more to accept bool value ([#2937](https://github.com/timber/timber/issues/2937)) ([85e2a32](https://github.com/timber/timber/commit/85e2a32e79616f937a19f1521c1378755c0e5014))
* Apply Rector code standard on MenuItem.php ([5d64d9a](https://github.com/timber/timber/commit/5d64d9a390664de0e32aa51a7c69c5c4964f9559))
* Attachment size doc block ([58d6719](https://github.com/timber/timber/commit/58d671992c45a2b6b62c48e25337e4ef9de34688))
* Attachment size doc block ([0bd5791](https://github.com/timber/timber/commit/0bd5791ed4b7239dec8088f262956ed3c7d7fae1))
* bump twig/twig to ^3.27 for sandbox security fixes ([112d369](https://github.com/timber/timber/commit/112d369e6877a3746271e9523d87fd01ef6ae466))
* consider 'fields' when returning terms from query, incl. tests ([fd95ebb](https://github.com/timber/timber/commit/fd95ebba4b072b1ec409ab765dabe4ecd6a8d220))
* correct indentation in core.php ([b8e0e77](https://github.com/timber/timber/commit/b8e0e779bcc0bf697e7010c1570a7046fac936f8))
* **deps:** bump twig/twig to ^3.26 to address security advisories ([a66aa85](https://github.com/timber/timber/commit/a66aa855ff6d1c718e0fc04b6ac5edcbba4b3faa))
* **deps:** update twig/cache-extra to ^3.21 for compatibility ([6f554f9](https://github.com/timber/timber/commit/6f554f91c6cb84d106cd15ba8f4cb77df7968910))
* **docs:** Fix classname for `Timber\Helper` in performance docs ([#3156](https://github.com/timber/timber/issues/3156)) ([1798ba9](https://github.com/timber/timber/commit/1798ba98c5b92e841bcce41e43e3e1c9ce061f48))
* **docs:** Simplify an if-check in the ACF docs ([96d2874](https://github.com/timber/timber/commit/96d287470a16cab3cc4b14aa373c88423816b3cb))
* Exclude docs folder for PHPStan ([6fbf274](https://github.com/timber/timber/commit/6fbf2748043d5302a493efdc55ab636e60f6b010))
* exclude tests from PHPStan ([fae8554](https://github.com/timber/timber/commit/fae8554f5c813bb232288f842b6d0d838448a476))
* External attachment alt/caption ([e85013f](https://github.com/timber/timber/commit/e85013ff098844f8e1d3e42f36cbfce731743ed2))
* failing tests for WP &lt;6.4 ([f4a233e](https://github.com/timber/timber/commit/f4a233ec6b3afacee5db592725090d775d654de1))
* file permissions ([#2842](https://github.com/timber/timber/issues/2842)) ([337d54d](https://github.com/timber/timber/commit/337d54d2727d3c1a511377e1b1a3c367a6ed006b))
* Fix a bug with URL check for avatars ([#3002](https://github.com/timber/timber/issues/3002)) ([456c24e](https://github.com/timber/timber/commit/456c24e7a438569d9e7fefd351f4f68cd3f7394d))
* fix avatar test ([#3071](https://github.com/timber/timber/issues/3071)) ([0e65e54](https://github.com/timber/timber/commit/0e65e54897fead31d3ba5eb3065242e294dcf51b))
* Fix bug in image letterboxing when transparent background was converted to black ([#3233](https://github.com/timber/timber/issues/3233)) ([a9d62e8](https://github.com/timber/timber/commit/a9d62e8f9ef8ed2ae50d7bbf667b0266bba5be58))
* Fix bug when image resize with invalid input causes a fatal error ([#3235](https://github.com/timber/timber/issues/3235)) ([b66b208](https://github.com/timber/timber/commit/b66b208a924a39dba908dc5f7f5570f22db5b84e))
* Fix bug with Attachment::path() method ([#3073](https://github.com/timber/timber/issues/3073)) ([5434dde](https://github.com/timber/timber/commit/5434dde5889f174bf1d36c0686b94b180d93fa5d))
* Fix bug with transient cache expiration ([#3220](https://github.com/timber/timber/issues/3220)) ([091e60e](https://github.com/timber/timber/commit/091e60e935e90ba71fc9a1615a2aa7a5788b449f))
* Fix bug with WP version notice in admin ([#3264](https://github.com/timber/timber/issues/3264)) ([5a26a21](https://github.com/timber/timber/commit/5a26a21ce0beaa2f2604aef2fd2db646a2c161cd))
* Fix context stacking issue with `Timber\Site::switch_to_blog()` in multisite environment ([#3223](https://github.com/timber/timber/issues/3223)) ([1ff183b](https://github.com/timber/timber/commit/1ff183b91c666d0c5addac6f2b4e61063cb7badd))
* Fix deprecation notice in `trim_words` method when `null` is passed. ([#3131](https://github.com/timber/timber/issues/3131)) ([78d928d](https://github.com/timber/timber/commit/78d928d792af7113bc032b96571ce8560a366e9c))
* Fix deprecation notice since twig 3.10 to now use EscaperRuntime instead of EscaperExtension ([#2997](https://github.com/timber/timber/issues/2997)) ([295349b](https://github.com/timber/timber/commit/295349b0316640014a0841acef0f185bbdb8bd2e))
* fix empty $params returning timber object ([#3179](https://github.com/timber/timber/issues/3179)) ([e732609](https://github.com/timber/timber/commit/e73260942bcb1e02428304795f6522304b29edf5))
* Fix failing tests for WP 6.4 ([5938c64](https://github.com/timber/timber/commit/5938c64ec72f7b0e948850cf3814eb777848cc9d))
* fix get location by id in Timber::get_menu_location() ([#3066](https://github.com/timber/timber/issues/3066)) ([5b33ba8](https://github.com/timber/timber/commit/5b33ba8475361e1e31974ee42a7e9a27e34e8b65))
* Fix incorrect ACF user filter ([#3121](https://github.com/timber/timber/issues/3121)) ([6f0a6dd](https://github.com/timber/timber/commit/6f0a6dd0bc24cee4468f16e374c590401be13492))
* Fix menu location compatibility with WPML ([#2733](https://github.com/timber/timber/issues/2733)) ([8603855](https://github.com/timber/timber/commit/86038557c683fa65e0564e078c600ea2fc3ea446))
* Fix MenuItem::is_external() returning false positives for relative URLs ([#3089](https://github.com/timber/timber/issues/3089)) ([2a14525](https://github.com/timber/timber/commit/2a145250d3ad2ea88f7fdabc20a649720e5e3cec))
* fix minor codestyle issue in loader.php to make easy-coding-standard happy ([#2950](https://github.com/timber/timber/issues/2950)) ([6e8b6ab](https://github.com/timber/timber/commit/6e8b6ab375df317207ea658cccb12cfb4710e64b))
* Fix problem when an empty ACF taxonomy relationship field transform loads all terms instead of none. ([#2960](https://github.com/timber/timber/issues/2960)) ([f95b82a](https://github.com/timber/timber/commit/f95b82af7cc8fa79ef8e10a75dbf62477b073ada))
* Fix regression bug when Timber would access empty properties in Twig ([#3249](https://github.com/timber/timber/issues/3249)) ([22eecab](https://github.com/timber/timber/commit/22eecab1bf58fe64c3fa7ecf019fd14a16223977))
* fix regression where crops with the default crop setting would s… ([#2998](https://github.com/timber/timber/issues/2998)) ([8090247](https://github.com/timber/timber/commit/809024798d720fc743fac807431144605bb1cea3))
* Fix some anchors ([b57670d](https://github.com/timber/timber/commit/b57670de799e1728b031e48b7347f122f6a5f4a5))
* Fix timezone bug when transforming ACF date fields ([#3163](https://github.com/timber/timber/issues/3163)) ([513fc62](https://github.com/timber/timber/commit/513fc62fb70e7c57d388a954057ca4dffe34c7f5))
* Fix typos in codebase ([#2968](https://github.com/timber/timber/issues/2968)) ([e40ceb3](https://github.com/timber/timber/commit/e40ceb3a72c7decaa597f6e2cdb27b4d1f3f5420))
* Fix typos in source code([#3077](https://github.com/timber/timber/issues/3077)) ([d7b3b80](https://github.com/timber/timber/commit/d7b3b804c3244083f6ae60e9f760f86aa512b054))
* Fix URI to FS parsing in ImageHelper ([#3027](https://github.com/timber/timber/issues/3027)) ([87d3ef4](https://github.com/timber/timber/commit/87d3ef4e81f55ddb783ad6eb7da4c96ca9c643aa)), closes [#3024](https://github.com/timber/timber/issues/3024)
* fixes an issue where in some cases images would not be rouned properly by image operations. This could lead to artifacts in the generated images. ([#3046](https://github.com/timber/timber/issues/3046)) ([10ab23d](https://github.com/timber/timber/commit/10ab23d5cfcd1b1e777a5f4a65f8e983e272b73d))
* ignore acf_get_field_type void errors ([441ef9e](https://github.com/timber/timber/commit/441ef9e82478cb250373938972bc09c0c1acf154))
* Improve doing_it_wrong messages for using deprecated parameters in Timber::get_attachment() and Timber::get_image() ([#2999](https://github.com/timber/timber/issues/2999)) ([e6cdf7e](https://github.com/timber/timber/commit/e6cdf7ef584f43de585d0b437cb250179d1a0045))
* Improve performance for reading attachment image dimensions ([#3232](https://github.com/timber/timber/issues/3232)) ([6b76d75](https://github.com/timber/timber/commit/6b76d75b8c5690ed24e2ed7f7f8a862e930f108b))
* initialize typed properties correctly ([747b089](https://github.com/timber/timber/commit/747b0898a8220bae58228fac1d146241215833ad))
* Letterbox with transparent bg ([#3201](https://github.com/timber/timber/issues/3201)) ([22b2d51](https://github.com/timber/timber/commit/22b2d51dce313a2c736f6e5664e8ab592e50dce9))
* make PostIterator-&gt;last_post nullable ([#2918](https://github.com/timber/timber/issues/2918)) ([064dde7](https://github.com/timber/timber/commit/064dde77998288c10cd39c26914a7e5ea934e04b))
* Make sure Post(s) exists before we call setup() ([#3114](https://github.com/timber/timber/issues/3114)) ([54cf691](https://github.com/timber/timber/commit/54cf69191a4a1a50089da8f244972203a780ec68))
* minor typo in Core method docs ([c80047d](https://github.com/timber/timber/commit/c80047d168114f890f38d6e52d500787226bc22c))
* now changed the correct url.. ([aab9a30](https://github.com/timber/timber/commit/aab9a30005faba0c85bdee87506807f00877a754))
* overwrite magic __call method in Site.php ([20dd75e](https://github.com/timber/timber/commit/20dd75efd1407aaa15c2c1cdb47aa684ddbef8c6))
* PHP 8.5 `null` index deprecations ([86a6b44](https://github.com/timber/timber/commit/86a6b4403ebd540749cdac170f28bd82b2724c23))
* phpstan issues & use fully qualified imports ([f587cef](https://github.com/timber/timber/commit/f587ceffab5f232a5888e9f2f6bcdee717abf00f))
* phpstan issues & use fully qualified imports ([4d0d3d7](https://github.com/timber/timber/commit/4d0d3d7ad9ab7f12106089ad7c9ae557d98e9aca))
* Prevent PostsIterator from resetting the post global in the admin ([#3117](https://github.com/timber/timber/issues/3117)) ([9f14d48](https://github.com/timber/timber/commit/9f14d48382c4335267d3b04abba175bb3f7827a2))
* Prevent unneeded blog switching in multisite env. ([#2781](https://github.com/timber/timber/issues/2781)) ([d81f995](https://github.com/timber/timber/commit/d81f9951ae41b27e1134b8bf6ae7354a9bae0546))
* Prevent ValueError for faulty or empty sideloaded image URLs ([#3125](https://github.com/timber/timber/issues/3125)) ([c2accc4](https://github.com/timber/timber/commit/c2accc44fee762adce061696ab3dea5050d9ff9e))
* Refactor ACF transform hooks ([279e932](https://github.com/timber/timber/commit/279e9320ea7f852aa7cfc7290915dfd606c1e49b))
* Remove security patch not needed in PHP 8 ([#2983](https://github.com/timber/timber/issues/2983)) ([8a30865](https://github.com/timber/timber/commit/8a30865b753b51771b524cf8745f5ee362a7de85))
* Run CS fixes on codebase ([#3047](https://github.com/timber/timber/issues/3047)) ([48dc3fc](https://github.com/timber/timber/commit/48dc3fc5a9104251f440af6b65f6a622660a91dc))
* **security:** Bump minimum required Twig version to fix security issue in Twig ([#3104](https://github.com/timber/timber/issues/3104)) ([9766a9c](https://github.com/timber/timber/commit/9766a9c1ac58b82dc2433536ab2a1a8442bc3ffa))
* **security:** Require nonce and capability check for autosave previews in `Timber::handle_preview()` (GHSA-53wf-g7j4-hwhh) ([b4c91db](https://github.com/timber/timber/commit/b4c91dbe7faaaf1eb4471d0642ac04a6715bd54f))
* setup composite action ([6c8d0bf](https://github.com/timber/timber/commit/6c8d0bfa250d6f1fa0cf2d5ab966679518f47213))
* split test running for integrations (plugins) ([#2904](https://github.com/timber/timber/issues/2904)) ([8d03809](https://github.com/timber/timber/commit/8d03809fe2ded38f497dab7c2347fa48a8de10b9))
* switch version property to constant and mark old property as deprecated ([971a38b](https://github.com/timber/timber/commit/971a38b46dfa3255be780052d8c76112eccbe3c1))
* tests failing since Twig 3.8.0 ([#2895](https://github.com/timber/timber/issues/2895)) ([f4a233e](https://github.com/timber/timber/commit/f4a233ec6b3afacee5db592725090d775d654de1))
* **tests:** fix missing constants in static analysis test ([ae50ccd](https://github.com/timber/timber/commit/ae50ccd25db099d18a93c96b20ecfc82e86a5c58))
* **tests:** update image test to prevent race conditions ([#3243](https://github.com/timber/timber/issues/3243)) ([5fce361](https://github.com/timber/timber/commit/5fce3616b5a443419f6963d1a84e05cb77da35a3))
* **test:** update used filter transient test ([32e594f](https://github.com/timber/timber/commit/32e594f0a98bdf9ccebad1a18fb6f16d0cbf9519))
* **test:** use new filter in tests ([c12e9af](https://github.com/timber/timber/commit/c12e9af6027f5bed6c418c2c933c3492e7d68d3e))
* timber::get_menu(0) returns alphabetically first menu instead of nothing ([#3070](https://github.com/timber/timber/issues/3070)) ([d278f95](https://github.com/timber/timber/commit/d278f954f672c0f3bb56e0a40e5d0acf40fc0608))
* undefined property ([9e8409e](https://github.com/timber/timber/commit/9e8409e69985925e256d7d48bb855dd95708f84f))
* unnecessary lowercasing parameters ([#2877](https://github.com/timber/timber/issues/2877)) ([664ea62](https://github.com/timber/timber/commit/664ea625504a0d781ac2efeb5e2b8a39c5ac3e70))
* Update admin notice for minimum required WordPress version ([#3001](https://github.com/timber/timber/issues/3001)) ([66e92a5](https://github.com/timber/timber/commit/66e92a526622afeb3eba3da52f47db2b8ae6735e))
* Update documentation for 2.0 release ([ded4599](https://github.com/timber/timber/commit/ded4599ce880c114b0037eb27abe5a000c92f842))
* Update Helper::deprecated to use E_USER_DEPRECATED instead of the default E_USER_NOTICE ([#3113](https://github.com/timber/timber/issues/3113)) ([3cabe81](https://github.com/timber/timber/commit/3cabe81be179b943a709648a9bb1e5113bc4de2e))
* update lint script to exclude 'tmp' directory ([a28a34d](https://github.com/timber/timber/commit/a28a34d5a665400ff4b8e8342feda7466450a249))
* Update static analysis workflow to correctly handle changed files ([533181b](https://github.com/timber/timber/commit/533181b8d1d0beb7b420c8f7dd26046847310d0f))
* update twig & twig/cache-extra dependency to version 3.17 to fix unit tests ([cbac2e0](https://github.com/timber/timber/commit/cbac2e0fcf0b01c3bc3eaaf7de01bc721003b926))
* Use correct deprecation_info for Twig callables ([#3064](https://github.com/timber/timber/issues/3064)) ([72a013e](https://github.com/timber/timber/commit/72a013e604ea098cb2819906a7be3454f4a3802d))


### Reverts

* revert changing property name ([a7b019b](https://github.com/timber/timber/commit/a7b019b75d5358c35b4237c39817d5a830e8dce2))


### Code Refactoring

* **admin:** extract MINIMUM_WP_VERSION constant and simplify notice ([79c4813](https://github.com/timber/timber/commit/79c481320eb478b492f8b2a45e0cd28149b7dc62))


### Tests

* Add callable test for `timber/menu/classmap` ([f724acf](https://github.com/timber/timber/commit/f724acf6bbdbd9223b90c989ab104a6f1f87f74e))
* Assert the published post stays in the preview listing test ([734fe09](https://github.com/timber/timber/commit/734fe095eb1e6266298a6c559db1c06f29d5f4e5))
* **cache:** drop vestigial sleep(1) in twig cache tests ([bbe859a](https://github.com/timber/timber/commit/bbe859a10bdfa419375b4a7ee14a4a650d5cb421))
* **cache:** replace sleep() with manual transient expiry ([03d6a6e](https://github.com/timber/timber/commit/03d6a6e172f1baac2704374de4df01c90a689d4c))
* Enhance test suite idempotency ([b9d8260](https://github.com/timber/timber/commit/b9d8260fb5544cb02094f160dc56cfbc05bece4d))
* Fix ancestors post tests ([c49cd8f](https://github.com/timber/timber/commit/c49cd8fc2f66baf34958a305b5bffcc128b6e8dd))
* Fix issue with image test that sets a constant ([#3181](https://github.com/timber/timber/issues/3181)) ([6ae6e2a](https://github.com/timber/timber/commit/6ae6e2af88ef08ef68aabf82b7e1058d3a6ed038))
* Fix PHP 8.5 deprecation notice ([8d2788e](https://github.com/timber/timber/commit/8d2788e392d9c08c7e32018d3c3e78d879887a4d))
* Fix test pollution and WP 7.0 compatibility in test suite ([7ff0b73](https://github.com/timber/timber/commit/7ff0b73e51736988331f00cf5ea192ffb21ff726))
* Fix tests ([#3101](https://github.com/timber/timber/issues/3101)) ([530a415](https://github.com/timber/timber/commit/530a4158e328515356595d389d31fa5e02bd9562))
* Fix TimberWidgetsTest with sidebar registration and widget setup methods ([#3239](https://github.com/timber/timber/issues/3239)) ([3084850](https://github.com/timber/timber/commit/3084850b74aee0db6563cd5cce165e158c251351))
* **helper:** use usleep(50ms) instead of sleep(1) in testTimers ([95949ff](https://github.com/timber/timber/commit/95949ff0c4e378339d3dda6fe52b12eb5d1f015b))
* Install svn to revert replacing svn with other methods when it’s missing in GitHub Actions ([#3115](https://github.com/timber/timber/issues/3115)) ([6ed649a](https://github.com/timber/timber/commit/6ed649a7f48142c5237c0ca5fb25bb0c5d2b8f06))
* **main:** trim testGetPostsInLoop fixture to loop length ([bc023c1](https://github.com/timber/timber/commit/bc023c196522a6fc787455db1f689c27b1959228))
* Modernize test suite with PHPUnit 11.5+/12 and Mantle Testkit ([c854758](https://github.com/timber/timber/commit/c854758e72b9b5a3bb8b453c2f3301eb1008c14c))
* Move preview security tests into `TimberRevisionsTest` ([6c5c53b](https://github.com/timber/timber/commit/6c5c53b59801f51466fa9e272d365d469d1fd431))
* **pagination:** reduce fixture sizes via WithOption posts_per_page=2 ([1d49c5b](https://github.com/timber/timber/commit/1d49c5bf439cb293fa86109e8617f0b5bc8526cd))
* **pagination:** reduce testPaginationEndLimits fixture cost ([34631bc](https://github.com/timber/timber/commit/34631bc3d0a5daba1c046751d92ca79e120562a4))
* **post-query:** cut fixture sizes via method-level WithOption ([0894595](https://github.com/timber/timber/commit/0894595174ad37e611465d1f5f3e599d7c7f5a09))
* Refactor menu tests to use static factory methods for term and post creation ([1e4e027](https://github.com/timber/timber/commit/1e4e0277bbc7e9e8bd44f6fe4f3fa621943448a0))
* Replace missing `svn` command in GitHub Actions when installing WordPress ([#3045](https://github.com/timber/timber/issues/3045)) ([bf2de48](https://github.com/timber/timber/commit/bf2de486ab0b8d8ace7d533c26eed28219b702d3))
* stop custom-upload-dir tests leaking into random-order runs ([c62f138](https://github.com/timber/timber/commit/c62f138941ddb4ab244884349728eb95bf524cba))
* Update ACF field keys to ensure uniqueness ([a38fa40](https://github.com/timber/timber/commit/a38fa408069a5435ae34ea92161f05d5fd9edec8))
* Update ACF field names for consistency and clarity ([80b7986](https://github.com/timber/timber/commit/80b7986eab5e467ada518c7d6e15ed6beebde156))
* update external image provider to a more reliable external service ([#3052](https://github.com/timber/timber/issues/3052)) ([d56177f](https://github.com/timber/timber/commit/d56177f5c6a133d2b02b14b212e10c6176087ea7))
* update external image test to use other placeholder service ([15fdc38](https://github.com/timber/timber/commit/15fdc38307b593847ad0e7ef6e9b07cd0438295a))
* Update tests for improved idempotency and accuracy in post queries ([bd3eb80](https://github.com/timber/timber/commit/bd3eb8007e72abc55b4af1681d32888446ce65a9))
* Update tests to prevent auto-marking of current menu items ([fd7920f](https://github.com/timber/timber/commit/fd7920f7bd229a998726721470cbe337f046f54c))
* Update WPMLTest to use dynamic menu values ([251c505](https://github.com/timber/timber/commit/251c5059e61d7978048e41c829ee578c07f42df0))


### Documentation

* Add documentation about the Timber plugin ([d4b77ec](https://github.com/timber/timber/commit/d4b77ec843f0d81adad980cc6413c61e79c699e9))
* Add missing `\Twig\` namespace to filter and function examples ([#3043](https://github.com/timber/timber/issues/3043)) ([191056b](https://github.com/timber/timber/commit/191056b62f3fcb5db5965745b02cff3444b710e5))
* Add security info and additional Timber project to readme. ([#2946](https://github.com/timber/timber/issues/2946)) ([fc06441](https://github.com/timber/timber/commit/fc06441745d1ab3a50bc97d438404a68cc68770e))
* Add security notice to the compile string function in Timber class ([#3116](https://github.com/timber/timber/issues/3116)) ([4978fb8](https://github.com/timber/timber/commit/4978fb8e2bfc78c6f7ff0c91cfd56420afff9447))
* add suggestions by [@gchtr](https://github.com/gchtr) ([aaf3269](https://github.com/timber/timber/commit/aaf326992ccae5e7217fe0a6ee82936ad5dff5ac))
* Add wp-i18n-twig to Internationalization docs ([#3061](https://github.com/timber/timber/issues/3061)) ([3e87249](https://github.com/timber/timber/commit/3e872495f2fe70d50bae2e149362daefa3fb188e))
* Applying suggestions by [@gchtr](https://github.com/gchtr) ([71d1098](https://github.com/timber/timber/commit/71d10985a74c4b580c5eb3a655d7c8340d3c583a))
* **cheatcheat:** Explained theme path, link and uri helpers. ([767ae79](https://github.com/timber/timber/commit/767ae790ddbce611c9d5d2a30f3235bc3f8e39df))
* doc path changes ([42ff452](https://github.com/timber/timber/commit/42ff452fa672f89b7043a31bda9c656b35d045f3))
* Document filters and some methods ([2614580](https://github.com/timber/timber/commit/26145807dce01a00d588f8e930407256ce6d8c7b))
* Document filters and some methods ([9f082f6](https://github.com/timber/timber/commit/9f082f6192f9e578cbf7585758d0daf5be6a4595))
* Documented transform_value filter/parameter ([7992eee](https://github.com/timber/timber/commit/7992eeed2039851ae705e0fa52545eabbc1d8293))
* Documented transform_value filter/parameter ([cb79370](https://github.com/timber/timber/commit/cb7937036dd6af2153968f96d81798d59127e9b1))
* Expand documentation for available Twig functions and filters ([#3194](https://github.com/timber/timber/issues/3194)) ([75145db](https://github.com/timber/timber/commit/75145db88dbb37d62142a1187434e924cad7fb0e))
* Fix broken links about theme security ([3413783](https://github.com/timber/timber/commit/34137837bc252fad7a8cb86156fee7fc84a6dc43))
* Fix broken links about theme security ([d942398](https://github.com/timber/timber/commit/d94239800c19913ccf566e661a958f420ccd86fd))
* fix code example in gutenberg.md ([54ed1e5](https://github.com/timber/timber/commit/54ed1e5ec3962b60d8855c0328294f67670cfd2a))
* fix docblock example of Site::__call() ([#3185](https://github.com/timber/timber/issues/3185)) ([9003468](https://github.com/timber/timber/commit/9003468dc19ba332e6ea483aedd1960b303a7f4b))
* Fix documentation references to `post.modified_date()` and `post.modified_time()` ([#3100](https://github.com/timber/timber/issues/3100)) ([41a35ba](https://github.com/timber/timber/commit/41a35baffde62a60c72c3ed429b02e39e18ff8b8))
* Fix HTML syntax in code example in Getting Started Guide ([#3042](https://github.com/timber/timber/issues/3042)) ([5c3f51c](https://github.com/timber/timber/commit/5c3f51ce6efd2c26ad40d43e00de5d3c51cb3c75))
* Fix spelling error in README ([#3241](https://github.com/timber/timber/issues/3241)) ([b25f32c](https://github.com/timber/timber/commit/b25f32cbeacd0f2dfeb4185787493bf82dba74c1))
* fix tests badge ([a666f7d](https://github.com/timber/timber/commit/a666f7d47835b1df6cc4606fc032e17d9ce34e9b))
* Fix two relative links in DocBlocks ([#3242](https://github.com/timber/timber/issues/3242)) ([0b0c0a4](https://github.com/timber/timber/commit/0b0c0a420339a8ab9867b33141f241273f8e2ede))
* fix typo in Timber::get_users docs ([#3013](https://github.com/timber/timber/issues/3013)) ([478a4c2](https://github.com/timber/timber/commit/478a4c29751616d8efc5986726e509155df1f6e1))
* Fix typo with `__return_true` in ACF Guide ([#3034](https://github.com/timber/timber/issues/3034)) ([02d31d7](https://github.com/timber/timber/commit/02d31d75e6cbff5b6d61daf07960c94b0fb71da5))
* Fix wrong use of apply_filters() instead of add_filter() in Performance Guide ([#3229](https://github.com/timber/timber/issues/3229)) ([a29dfc0](https://github.com/timber/timber/commit/a29dfc0034d84098c08d647f23ec81663fc09300))
* Fixes example query in upgrade guide ([#2930](https://github.com/timber/timber/issues/2930)) ([e07751e](https://github.com/timber/timber/commit/e07751e42f0f11235176a77b84cb38c37c323338))
* incorrect anchor from update 2.0 to get_posts ([#3083](https://github.com/timber/timber/issues/3083)) ([b7e72e3](https://github.com/timber/timber/commit/b7e72e3fcb574ef2762d3d7620a7055760e2e971))
* Mark Helper::convert_wp_object as API method ([#3005](https://github.com/timber/timber/issues/3005)) ([1a801d2](https://github.com/timber/timber/commit/1a801d273b336048a5bd6e19fe6e6c40d2921b31))
* readme update last links ([07ca956](https://github.com/timber/timber/commit/07ca9569158f50e3482d77caf0afebbbeae27ea7))
* remove video tutorials from readme ([468e1b5](https://github.com/timber/timber/commit/468e1b54a93289d3fb36c60a5e0f841d6dd0ba29))
* Update ACF integration docs to use recommended method to get meta in ACF Integration ([ac9ee1b](https://github.com/timber/timber/commit/ac9ee1b5db0b9d283a069cedcdfc01899cff5ed6))
* update bug_report.yml ([ef3da5b](https://github.com/timber/timber/commit/ef3da5bd23227f45ca0957b955a9f54d1bcf8fc3))
* Update deprecated Timber::get_context() in code examples ([#2962](https://github.com/timber/timber/issues/2962)) ([152e173](https://github.com/timber/timber/commit/152e173f80c18e6bef78c0abf41278ea28ed2b8c))
* update filter in docs ([6b72a01](https://github.com/timber/timber/commit/6b72a01f807770fa82cc6476475b5ad64a5e792c))
* update filter in docs ([fe44490](https://github.com/timber/timber/commit/fe44490af951e7f3e9747d635e675290d0df06e2))
* update links and https ([56e9057](https://github.com/timber/timber/commit/56e905796ba33760bb0c3b0874dfa34921924ecb))
* Update links in documentation and fix spelling mistakes ([#3238](https://github.com/timber/timber/issues/3238)) ([5af4b84](https://github.com/timber/timber/commit/5af4b84004b3967cdb76a8dee87a9e5d338403f1))
* update links to docs in php file ([739b346](https://github.com/timber/timber/commit/739b3463fd9918c65293bccda7445a01519a23e3))
* update readme ([9f32501](https://github.com/timber/timber/commit/9f32501dbf8e5dcb015126cba76dda14e60ead7b))
* update the widgets guide ([#3067](https://github.com/timber/timber/issues/3067)) ([aa5bf74](https://github.com/timber/timber/commit/aa5bf741f04eedb095363dc6a8b80af462cef146))
* Update v2 caching docs ([81b2284](https://github.com/timber/timber/commit/81b22844d2e1e142b50280935d5a35993fdc7b52))
* Updated ACF guide ([aa374ca](https://github.com/timber/timber/commit/aa374cadadf69101b664ef60334a721a08c7a7d7))
* updated plugin support part ([3e1e798](https://github.com/timber/timber/commit/3e1e7981821596b180351c28a1dbb85fbe0322b6))
* updated plugin support part ([479ea4b](https://github.com/timber/timber/commit/479ea4b49cde873abb60ce879a36e986ecf76091))
* v2 readme refer to v1 ([011e845](https://github.com/timber/timber/commit/011e845b0fe8d33bb648b4ec728a1b15798147fd))
* v2 readme refer to v1 ([7b80371](https://github.com/timber/timber/commit/7b803718cffb60501d331655fac6c4894afc285f))
* WooCommerce guide - loop enhancement ([3d5bcdf](https://github.com/timber/timber/commit/3d5bcdffae8c1a025a40af0a7a61937359c61c5a))
* WooCommerce guide - tease-product.twig fix ([0f10599](https://github.com/timber/timber/commit/0f10599265512b5118095b7acc7a02e57e9a8a0c))
* WooCommerce guide - tease-product.twig fix ([#3199](https://github.com/timber/timber/issues/3199)) ([9a7d411](https://github.com/timber/timber/commit/9a7d41124ca1f7491b2f0cc2529b757eb5885470))


### Continuous Integration

* add additional files to update version numbers & switch to updated action ([#3041](https://github.com/timber/timber/issues/3041)) ([426e0db](https://github.com/timber/timber/commit/426e0db9e54dc8c6848da5088845d6ac5214d0de))
* Add Conductor workflow ([#3128](https://github.com/timber/timber/issues/3128)) ([7d63897](https://github.com/timber/timber/commit/7d638971972811a10d61910494f0d5642ab5663b))
* Add cs2pr for ECS which now supports checktyle format ([#3010](https://github.com/timber/timber/issues/3010)) ([d84910d](https://github.com/timber/timber/commit/d84910decc1970cfedb22207d5e83b5152c0868b))
* add PHP 8.2/8.3 and update test matrix ([#2900](https://github.com/timber/timber/issues/2900)) ([ef281c8](https://github.com/timber/timber/commit/ef281c8efffddd5dce5a49dce75ef5af1a0bda23))
* add release please workflow ([#2912](https://github.com/timber/timber/issues/2912)) ([f3401a5](https://github.com/timber/timber/commit/f3401a5a5ec6a78d4b9d9338bd4ccc194e5a174c))
* Bump Conductor PHP version ([21f7794](https://github.com/timber/timber/commit/21f7794f38b26cb7834e2a4617585be1becf8ddb))
* Fix permission issue for release-please ([b6da40f](https://github.com/timber/timber/commit/b6da40fcdb13ec2146f776a117cc8b4fc2aab5ac))
* harden GitHub Actions workflows ([43d0b89](https://github.com/timber/timber/commit/43d0b8918d6e89c45eca292348bfca8788908324))
* Lint only PHP files ([#2969](https://github.com/timber/timber/issues/2969)) ([e992632](https://github.com/timber/timber/commit/e99263259b5a9e4bbfc058d5fa3b0822b9672a39))
* only run on main branches ([116b5ec](https://github.com/timber/timber/commit/116b5ec30c205f69f03548b3070acaa255e70e5f))
* run workflows on the 2.5.x branch ([378a6ef](https://github.com/timber/timber/commit/378a6effde5fd2d2bec24847405dc5ef86d53539))
* Temporarily disable insecure-package block for Composer installation ([93a20a1](https://github.com/timber/timber/commit/93a20a1e8bf8ffccf8f42d8f6c8df14d8bbc3a05))
* update composer PHP versions action to v2.1.0 ([dadab84](https://github.com/timber/timber/commit/dadab8469dcf6de755b0c15330af126dcba49e62))
* Update default PHP version to 8.2 in setup action ([e052190](https://github.com/timber/timber/commit/e0521907c025b56de881b3dafc7833f246644261))
* Update PHP/WP test versions ([98b010f](https://github.com/timber/timber/commit/98b010fb7485e8412d7cbbb5d885dc2d21200d8c))
* Update workflows ([b4a5874](https://github.com/timber/timber/commit/b4a58740d5cc45961b291f6e1adbc3584095e951))
* Upgrade Composer install action to version 4 ([#3215](https://github.com/timber/timber/issues/3215)) ([967541e](https://github.com/timber/timber/commit/967541e931ec7372827a4376bfeb2a08e7716681))
* Use draft release for release please ([238587e](https://github.com/timber/timber/commit/238587e5398b28997d117c080938c636b861dc77))


### Styles

* Fix coding style and rector issues ([#3164](https://github.com/timber/timber/issues/3164)) ([6ae23b1](https://github.com/timber/timber/commit/6ae23b1a24c1ec79a6db0e9ed6ae9776e5a2dad3))


### Miscellaneous Chores

* **2.5.x:** release 2.5.2 ([#3328](https://github.com/timber/timber/issues/3328)) ([f179f2c](https://github.com/timber/timber/commit/f179f2ccd3c7e645906a8a33f4b74a3e496c34ff))
* **2.x:** release 2.1.0 ([#2913](https://github.com/timber/timber/issues/2913)) ([d353d19](https://github.com/timber/timber/commit/d353d1912a1a051f47ba2d3f2e3ae1af1e5bed53))
* **2.x:** release 2.2.0 ([#2965](https://github.com/timber/timber/issues/2965)) ([5fc65f2](https://github.com/timber/timber/commit/5fc65f235323e8119fddc3cadbfef39ec97a663a))
* **2.x:** release 2.3.0 ([#3003](https://github.com/timber/timber/issues/3003)) ([55acea4](https://github.com/timber/timber/commit/55acea4414eac6ea9d0a11a102af37cf13f219b2))
* **2.x:** release 2.3.1 ([#3058](https://github.com/timber/timber/issues/3058)) ([3f6e73f](https://github.com/timber/timber/commit/3f6e73feadf5d547dff4992f645805da7fbc4d3a))
* **2.x:** release 2.3.2 ([#3076](https://github.com/timber/timber/issues/3076)) ([08ce601](https://github.com/timber/timber/commit/08ce601167a03ed7a4fa7da740481c17ed650ef0))
* **2.x:** release 2.3.3 ([#3107](https://github.com/timber/timber/issues/3107)) ([7a87ac2](https://github.com/timber/timber/commit/7a87ac27c0b9deedffe419388b63a0c95d8798ca))
* **2.x:** release 2.4.0 ([#3149](https://github.com/timber/timber/issues/3149)) ([44c7171](https://github.com/timber/timber/commit/44c7171d154ee697bab416a31852b9340845cc7f))
* **2.x:** release 2.4.1 ([#3240](https://github.com/timber/timber/issues/3240)) ([35b869a](https://github.com/timber/timber/commit/35b869a7f1029d0b54e10066eb0da04c9c54e46f))
* **2.x:** release 2.5.0 ([#3251](https://github.com/timber/timber/issues/3251)) ([537e59c](https://github.com/timber/timber/commit/537e59c4f6fe9858ef9905822c9667d0939bf380))
* **2.x:** release 2.5.1 ([#3263](https://github.com/timber/timber/issues/3263)) ([b91d24b](https://github.com/timber/timber/commit/b91d24b9d76b44e49a345d2844ed5dfc21b118a0))
* Add script descriptions in composer file ([#2951](https://github.com/timber/timber/issues/2951)) ([5785128](https://github.com/timber/timber/commit/5785128c1fbb817e146bbf5fdecc270c1856bae8))
* add several files to export-ignore ([0cd0cdf](https://github.com/timber/timber/commit/0cd0cdf3e09438f54b8e65bc408b08a98e42cdd7))
* add Timber authors ([567475e](https://github.com/timber/timber/commit/567475eb396eec7d3c80715e7db7880d2875f338))
* bump minimum WordPress version to 6.4 ([1333635](https://github.com/timber/timber/commit/1333635dc4bf15b3dc9ec80984315c8e6502875d))
* clean up .gitignore by removing unnecessary entries and reordering sections ([1c1adeb](https://github.com/timber/timber/commit/1c1adeb707bd637c63f07d58c5eab1ced3fb29d5))
* commit composer.lock file ([#3119](https://github.com/timber/timber/issues/3119)) ([e974e25](https://github.com/timber/timber/commit/e974e252851af262426319aca4991fb09afbe6b1))
* Create SECURITY.md ([#2939](https://github.com/timber/timber/issues/2939)) ([be36065](https://github.com/timber/timber/commit/be360651eedad4e99a59d185ecaf04d7ab6a3b11))
* **deps-dev:** bump league/commonmark from 2.8.0 to 2.8.1 ([#3207](https://github.com/timber/timber/issues/3207)) ([93f8ece](https://github.com/timber/timber/commit/93f8ece1b02282898eeec4989fc2c40a28638252))
* **deps-dev:** bump symfony/process from 7.4.3 to 7.4.5 ([#3187](https://github.com/timber/timber/issues/3187)) ([c81733b](https://github.com/timber/timber/commit/c81733b23a4d84d900c4b97264270d19203576b8))
* **deps:** bump actions/checkout from 4 to 5 ([#3134](https://github.com/timber/timber/issues/3134)) ([97ad233](https://github.com/timber/timber/commit/97ad2339dfe3c2c21a9bdf124d721c3b82a1a42c))
* **deps:** bump actions/checkout from 4 to 6 ([f1efafd](https://github.com/timber/timber/commit/f1efafd67ad4bc7715c2708bbe9ddb0a81d631cb))
* **deps:** bump actions/checkout from 5 to 6 ([#3160](https://github.com/timber/timber/issues/3160)) ([4d6b9a6](https://github.com/timber/timber/commit/4d6b9a6986366dc7fb6e41ec301bc5c6ff255536))
* **deps:** bump codecov/codecov-action from 5 to 6 ([#3224](https://github.com/timber/timber/issues/3224)) ([abb44ac](https://github.com/timber/timber/commit/abb44ac53f962c8a797238dc64b16f170809a6c4))
* **deps:** bump googleapis/release-please-action from 4 to 5 ([#3244](https://github.com/timber/timber/issues/3244)) ([43c4e49](https://github.com/timber/timber/commit/43c4e4964b11d2ce6020b14bc77759dfda71de79))
* **deps:** bump lycheeverse/lychee-action from 1.10.0 to 2.0.2 ([#3053](https://github.com/timber/timber/issues/3053)) ([480534f](https://github.com/timber/timber/commit/480534fc95cf7d0b92af0ffc1f64805a352406ea))
* **deps:** bump lycheeverse/lychee-action from 1.8.0 to 1.9.1 ([4f16233](https://github.com/timber/timber/commit/4f16233db533a43ab949aad52f631523af9036b9))
* **deps:** bump lycheeverse/lychee-action from 1.8.0 to 1.9.1 ([1ca79af](https://github.com/timber/timber/commit/1ca79aff20b5ac821cded348a2e4ed151bb58777))
* **deps:** bump lycheeverse/lychee-action from 1.9.1 to 1.9.3 ([#2907](https://github.com/timber/timber/issues/2907)) ([eecfb03](https://github.com/timber/timber/commit/eecfb039dd7fbf3020cdf0310f6f96b6306616b0))
* **deps:** bump lycheeverse/lychee-action from 1.9.3 to 1.10.0 ([#2980](https://github.com/timber/timber/issues/2980)) ([dd34720](https://github.com/timber/timber/commit/dd3472030a25ee59f760abe95c48c5fabcf54abb))
* **deps:** bump lycheeverse/lychee-action from 2.0.2 to 2.2.0 ([#3078](https://github.com/timber/timber/issues/3078)) ([11a74ba](https://github.com/timber/timber/commit/11a74ba68cd05a109eff14d6fcf19119743626d9))
* **deps:** bump lycheeverse/lychee-action from 2.2.0 to 2.4.1 ([#3108](https://github.com/timber/timber/issues/3108)) ([edbd398](https://github.com/timber/timber/commit/edbd39809fb2d47390744cdff432b16b16950f78))
* **deps:** bump lycheeverse/lychee-action from 2.4.1 to 2.5.0 ([#3133](https://github.com/timber/timber/issues/3133)) ([ffe9901](https://github.com/timber/timber/commit/ffe9901580d4aba1bfe2398a6d7c5e4b3f7f6f44))
* **deps:** bump lycheeverse/lychee-action from 2.5.0 to 2.6.1 ([#3137](https://github.com/timber/timber/issues/3137)) ([fbafff9](https://github.com/timber/timber/commit/fbafff9263fc33b35ce77130dc60e9119c34ff2d))
* **deps:** bump lycheeverse/lychee-action from 2.6.1 to 2.7.0 ([#3157](https://github.com/timber/timber/issues/3157)) ([4f7bde4](https://github.com/timber/timber/commit/4f7bde4b9580652274d259b5c514e0cad5394ea9))
* **deps:** bump lycheeverse/lychee-action from 2.7.0 to 2.8.0 ([#3205](https://github.com/timber/timber/issues/3205)) ([b64b67a](https://github.com/timber/timber/commit/b64b67a503574a47a03361a54f09b83501ef3d77))
* **deps:** bump peter-evans/create-issue-from-file from 4 to 5 ([#2906](https://github.com/timber/timber/issues/2906)) ([64703f8](https://github.com/timber/timber/commit/64703f86ae16d68b5706cd3bfd001a34ec821153))
* **deps:** bump peter-evans/create-issue-from-file from 5 to 6 ([#3154](https://github.com/timber/timber/issues/3154)) ([1bfd169](https://github.com/timber/timber/commit/1bfd169533393cbc169bbfd72a814089cbd911ac))
* **deps:** bump ramsey/composer-install from 2 to 3 ([#2941](https://github.com/timber/timber/issues/2941)) ([97010c4](https://github.com/timber/timber/commit/97010c47a27788c262b214a62d69a530a802b6c0))
* **deps:** bump ramsey/composer-install from 3 to 4 ([#3214](https://github.com/timber/timber/issues/3214)) ([944c871](https://github.com/timber/timber/commit/944c871a1e0c2ecc544a981edb47e10733ff1d2b))
* **deps:** bump shivammathur/setup-php ([#3257](https://github.com/timber/timber/issues/3257)) ([b5f545f](https://github.com/timber/timber/commit/b5f545f94d9b1c51a331841e68ead10c8e7e91b1))
* **deps:** bump tj-actions/changed-files from 39 to 42 ([2e877e4](https://github.com/timber/timber/commit/2e877e4367e85b3817dcd0da959011666454b943))
* **deps:** bump tj-actions/changed-files from 39 to 42 ([964f11a](https://github.com/timber/timber/commit/964f11aa496f577179e03f1afadbd1da1e7a5d1b))
* **deps:** bump tj-actions/changed-files from 42 to 44 ([#2959](https://github.com/timber/timber/issues/2959)) ([66eabe2](https://github.com/timber/timber/commit/66eabe28a32b40d9eadaae6864c6bf7c3f8144c4))
* **deps:** bump tj-actions/changed-files from 44 to 45 ([#3031](https://github.com/timber/timber/issues/3031)) ([880c0ff](https://github.com/timber/timber/commit/880c0ff23df5e7952cc6499d0043996a4d2c89bf))
* **deps:** bump tj-actions/changed-files from 45 to 46 ([#3105](https://github.com/timber/timber/issues/3105)) ([d8535cf](https://github.com/timber/timber/commit/d8535cf693a5bbdae55b1396b2fa24471dad22d9))
* **deps:** bump tj-actions/changed-files from 46 to 47 ([#3147](https://github.com/timber/timber/issues/3147)) ([cee39ed](https://github.com/timber/timber/commit/cee39edf85d37d3790eca371549f7dad1b566cf3))
* **deps:** bump tj-actions/changed-files from 47.0.0 to 47.0.6 ([e8f79dd](https://github.com/timber/timber/commit/e8f79ddd146c001691fe64f59e3d2f0a0ac054a6))
* **deps:** bump WyriHaximus/github-action-composer-php-versions-in-range ([#3225](https://github.com/timber/timber/issues/3225)) ([d31a521](https://github.com/timber/timber/commit/d31a521b866c3e869c2f558c6a1819d2e093ac62))
* **deps:** update .lock file and update ci steps ([af8c48f](https://github.com/timber/timber/commit/af8c48f1dc480c8c730f91d51bfb2c49d097e592))
* **deps:** Update Composer dependencies ([#3165](https://github.com/timber/timber/issues/3165)) ([31c78f9](https://github.com/timber/timber/commit/31c78f9ee73952d9b7eebd749633f2fc71e15cb6))
* **deps:** update content-hash and bump easy-coding-standard to version 13.0.4 ([c451ded](https://github.com/timber/timber/commit/c451dedf1efc63c9c12c1504e8a37c8806d805f1))
* **deps:** update mantle-framework dependencies to version 1.16 ([e752c14](https://github.com/timber/timber/commit/e752c146474bf2945d938d5ef07c8fd9a5dedf0b))
* Final code quality improvements before release ([#3237](https://github.com/timber/timber/issues/3237)) ([966873e](https://github.com/timber/timber/commit/966873e1f353d47189ab7b9c3797a11b7c1843ab))
* final dependency update and rector fix ([a76daed](https://github.com/timber/timber/commit/a76daed63ae4ddef2fe73dea413c1390109fb693))
* inherit Funding from .github repo ([5623a79](https://github.com/timber/timber/commit/5623a797483542f496df0c3002cc211d9838960e))
* Remove Conductor workflow ([e2a2cf4](https://github.com/timber/timber/commit/e2a2cf485f838a3d7fdb71850a45d7c3fffac7a6))
* remove deprecated escaper functions and streamline escaper setup ([#3193](https://github.com/timber/timber/issues/3193)) ([072b4fa](https://github.com/timber/timber/commit/072b4fa0b88cc69423df49004b6048e3d2d71794))
* remove Lando config ([#2899](https://github.com/timber/timber/issues/2899)) ([6fa8ffc](https://github.com/timber/timber/commit/6fa8ffcdb51d286169b47e29ddf54f26568da95a))
* remove phpcs.xml configuration file ([767d0b4](https://github.com/timber/timber/commit/767d0b4f91dc13451afcc10fcc06655201aa7825))
* set proper return types on build methods ([#2976](https://github.com/timber/timber/issues/2976)) ([6b72908](https://github.com/timber/timber/commit/6b72908d473188aa756d0b8ebb6641fae747e0b4))
* update .gitattributes to exclude phpstan.neon.dist and typos.toml from dist ([3a226cf](https://github.com/timber/timber/commit/3a226cffae5a0e3305202fc511a17d394cafe02e))
* Update all links in the codebase and documentation to https ([#2947](https://github.com/timber/timber/issues/2947)) ([05af54f](https://github.com/timber/timber/commit/05af54f7f5463c737299fb9b0512f79b334d2e94))
* Update changelog sections in release configuration ([223d674](https://github.com/timber/timber/commit/223d67420a2d0a399173fac57fe395e986ffaeca))
* Update CODEOWNERS ([#3166](https://github.com/timber/timber/issues/3166)) ([aa38e98](https://github.com/timber/timber/commit/aa38e984b5b21974ac8790d698c10cfe2914b60c))
* Update Composer dependencies ([#3130](https://github.com/timber/timber/issues/3130)) ([46942a3](https://github.com/timber/timber/commit/46942a34833c44fcd2ec5f747d9e82d8e4a7f48d))
* Update composer.lock file with PHP 8.1 ([#3132](https://github.com/timber/timber/issues/3132)) ([184d99a](https://github.com/timber/timber/commit/184d99a6767992c40467d0a625564cfe06dd611f))
* update deprecation notice version ([fe9e33e](https://github.com/timber/timber/commit/fe9e33e8c7e0706b873a653dbd011d9716ac9096))
* update links in contributing.md ([3b2c855](https://github.com/timber/timber/commit/3b2c855495b7877a6967537c68054aaebf972eea))
* update method references to use class name directly ([#3262](https://github.com/timber/timber/issues/3262)) ([b24cf4c](https://github.com/timber/timber/commit/b24cf4cbb0f43337e0fae6bbe62ae59db3e42ed9))
* update minimum WordPress version to 6.8.0 in tests and Timber class ([7947b63](https://github.com/timber/timber/commit/7947b631ad8d1efe858e778d6fc1b348419f8b9b))
* update README badges for Codecov and remove Coveralls and Scrutinizer links ([d89bff9](https://github.com/timber/timber/commit/d89bff9560a257c35a24eac0f4870da6a8d26f31))
* Upgrade dev dependencies ([#3118](https://github.com/timber/timber/issues/3118)) ([8293d1a](https://github.com/timber/timber/commit/8293d1aae15a1907543fe28f2db9419e73c6acee))

## [2.5.2](https://github.com/timber/timber/compare/v2.5.1...v2.5.2) (2026-11-25)


### Bug Fixes

* **security:** Require nonce and capability check for autosave previews in `Timber::handle_preview()` (GHSA-53wf-g7j4-hwhh) ([b4c91db](https://github.com/timber/timber/commit/b4c91dbe7faaaf1eb4471d0642ac04a6715bd54f))


### Tests

* Assert the published post stays in the preview listing test ([734fe09](https://github.com/timber/timber/commit/734fe095eb1e6266298a6c559db1c06f29d5f4e5))
* Move preview security tests into `TimberRevisionsTest` ([6c5c53b](https://github.com/timber/timber/commit/6c5c53b59801f51466fa9e272d365d469d1fd431))


### Continuous Integration

* run workflows on the 2.5.x branch ([378a6ef](https://github.com/timber/timber/commit/378a6effde5fd2d2bec24847405dc5ef86d53539))

## [2.5.1](https://github.com/timber/timber/compare/v2.5.0...v2.5.1) (2026-05-29)


### Bug Fixes

* Fix bug with WP version notice in admin ([#3264](https://github.com/timber/timber/issues/3264)) ([5a26a21](https://github.com/timber/timber/commit/5a26a21ce0beaa2f2604aef2fd2db646a2c161cd))


### Miscellaneous Chores

* update .gitattributes to exclude phpstan.neon.dist and typos.toml from dist ([3a226cf](https://github.com/timber/timber/commit/3a226cffae5a0e3305202fc511a17d394cafe02e))

## [2.5.0](https://github.com/timber/timber/compare/v2.4.1...v2.5.0) (2026-05-29)


### Features

* Add post excerpt content filter ([#3247](https://github.com/timber/timber/issues/3247)) ([a8aa17e](https://github.com/timber/timber/commit/a8aa17e7a363025eb59f58561282e8d1cf4568a1))


### Bug Fixes

* bump twig/twig to ^3.27 for sandbox security fixes ([112d369](https://github.com/timber/timber/commit/112d369e6877a3746271e9523d87fd01ef6ae466))
* correct indentation in core.php ([b8e0e77](https://github.com/timber/timber/commit/b8e0e779bcc0bf697e7010c1570a7046fac936f8))
* switch version property to constant and mark old property as deprecated ([971a38b](https://github.com/timber/timber/commit/971a38b46dfa3255be780052d8c76112eccbe3c1))


### Code Refactoring

* **admin:** extract MINIMUM_WP_VERSION constant and simplify notice ([79c4813](https://github.com/timber/timber/commit/79c481320eb478b492f8b2a45e0cd28149b7dc62))


### Tests

* **cache:** drop vestigial sleep(1) in twig cache tests ([bbe859a](https://github.com/timber/timber/commit/bbe859a10bdfa419375b4a7ee14a4a650d5cb421))
* **cache:** replace sleep() with manual transient expiry ([03d6a6e](https://github.com/timber/timber/commit/03d6a6e172f1baac2704374de4df01c90a689d4c))
* **helper:** use usleep(50ms) instead of sleep(1) in testTimers ([95949ff](https://github.com/timber/timber/commit/95949ff0c4e378339d3dda6fe52b12eb5d1f015b))
* **main:** trim testGetPostsInLoop fixture to loop length ([bc023c1](https://github.com/timber/timber/commit/bc023c196522a6fc787455db1f689c27b1959228))
* **pagination:** reduce fixture sizes via WithOption posts_per_page=2 ([1d49c5b](https://github.com/timber/timber/commit/1d49c5bf439cb293fa86109e8617f0b5bc8526cd))
* **pagination:** reduce testPaginationEndLimits fixture cost ([34631bc](https://github.com/timber/timber/commit/34631bc3d0a5daba1c046751d92ca79e120562a4))
* **post-query:** cut fixture sizes via method-level WithOption ([0894595](https://github.com/timber/timber/commit/0894595174ad37e611465d1f5f3e599d7c7f5a09))
* stop custom-upload-dir tests leaking into random-order runs ([c62f138](https://github.com/timber/timber/commit/c62f138941ddb4ab244884349728eb95bf524cba))


### Continuous Integration

* harden GitHub Actions workflows ([43d0b89](https://github.com/timber/timber/commit/43d0b8918d6e89c45eca292348bfca8788908324))
* update composer PHP versions action to v2.1.0 ([dadab84](https://github.com/timber/timber/commit/dadab8469dcf6de755b0c15330af126dcba49e62))


### Miscellaneous Chores

* bump minimum WordPress version to 6.4 ([1333635](https://github.com/timber/timber/commit/1333635dc4bf15b3dc9ec80984315c8e6502875d))
* **deps:** bump shivammathur/setup-php ([#3257](https://github.com/timber/timber/issues/3257)) ([b5f545f](https://github.com/timber/timber/commit/b5f545f94d9b1c51a331841e68ead10c8e7e91b1))
* **deps:** bump tj-actions/changed-files from 47.0.0 to 47.0.6 ([e8f79dd](https://github.com/timber/timber/commit/e8f79ddd146c001691fe64f59e3d2f0a0ac054a6))
* remove phpcs.xml configuration file ([767d0b4](https://github.com/timber/timber/commit/767d0b4f91dc13451afcc10fcc06655201aa7825))
* update deprecation notice version ([fe9e33e](https://github.com/timber/timber/commit/fe9e33e8c7e0706b873a653dbd011d9716ac9096))
* update method references to use class name directly ([#3262](https://github.com/timber/timber/issues/3262)) ([b24cf4c](https://github.com/timber/timber/commit/b24cf4cbb0f43337e0fae6bbe62ae59db3e42ed9))
* update minimum WordPress version to 6.8.0 in tests and Timber class ([7947b63](https://github.com/timber/timber/commit/7947b631ad8d1efe858e778d6fc1b348419f8b9b))

## [2.4.1](https://github.com/timber/timber/compare/v2.4.0...v2.4.1) (2026-05-20)


### Bug Fixes

* **deps:** bump twig/twig to ^3.26 to address security advisories ([a66aa85](https://github.com/timber/timber/commit/a66aa855ff6d1c718e0fc04b6ac5edcbba4b3faa))
* **deps:** update twig/cache-extra to ^3.21 for compatibility ([6f554f9](https://github.com/timber/timber/commit/6f554f91c6cb84d106cd15ba8f4cb77df7968910))
* Fix regression bug when Timber would access empty properties in Twig ([#3249](https://github.com/timber/timber/issues/3249)) ([22eecab](https://github.com/timber/timber/commit/22eecab1bf58fe64c3fa7ecf019fd14a16223977))
* **tests:** update image test to prevent race conditions ([#3243](https://github.com/timber/timber/issues/3243)) ([5fce361](https://github.com/timber/timber/commit/5fce3616b5a443419f6963d1a84e05cb77da35a3))


### Documentation

* Fix spelling error in README ([#3241](https://github.com/timber/timber/issues/3241)) ([b25f32c](https://github.com/timber/timber/commit/b25f32cbeacd0f2dfeb4185787493bf82dba74c1))
* Fix two relative links in DocBlocks ([#3242](https://github.com/timber/timber/issues/3242)) ([0b0c0a4](https://github.com/timber/timber/commit/0b0c0a420339a8ab9867b33141f241273f8e2ede))


### Continuous Integration

* Temporarily disable insecure-package block for Composer installation ([93a20a1](https://github.com/timber/timber/commit/93a20a1e8bf8ffccf8f42d8f6c8df14d8bbc3a05))


### Miscellaneous Chores

* clean up .gitignore by removing unnecessary entries and reordering sections ([1c1adeb](https://github.com/timber/timber/commit/1c1adeb707bd637c63f07d58c5eab1ced3fb29d5))
* **deps:** bump googleapis/release-please-action from 4 to 5 ([#3244](https://github.com/timber/timber/issues/3244)) ([43c4e49](https://github.com/timber/timber/commit/43c4e4964b11d2ce6020b14bc77759dfda71de79))
* Remove Conductor workflow ([e2a2cf4](https://github.com/timber/timber/commit/e2a2cf485f838a3d7fdb71850a45d7c3fffac7a6))

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
