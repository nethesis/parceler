# Changelog

## [0.3.2](https://github.com/nethesis/parceler/compare/v0.3.1...v0.3.2) (2024-11-14)


### Miscellaneous Chores

* **deps:** lock file maintenance ([59cfa8d](https://github.com/nethesis/parceler/commit/59cfa8d2bf7ee4b9d76814760e3950951c40233f))

## [0.3.1](https://github.com/nethesis/parceler/compare/v0.3.0...v0.3.1) (2024-07-22)


### Bug Fixes

* added more verbosity to crond commands ([cdbe052](https://github.com/nethesis/parceler/commit/cdbe052afb8b623e84499c1b9a6748756f4e3a86))
* missing log level for crond ([5b563dc](https://github.com/nethesis/parceler/commit/5b563dc046e9e0a978a97acf9aa7a399320c8561))

## [0.3.0](https://github.com/nethesis/parceler/compare/v0.2.1...v0.3.0) (2024-07-22)


### Features

* added helper commands ([d719798](https://github.com/nethesis/parceler/commit/d719798c1176deb8f0653723b756bff0925b7906))
* added repository:list command ([632d2df](https://github.com/nethesis/parceler/commit/632d2dfe1584af315c5a00cc7a7d974996ae1ed1))


### Bug Fixes

* fixing issue with composer in prod environment ([a9e9ce6](https://github.com/nethesis/parceler/commit/a9e9ce68b179b04f8b7f48a8e98bb1ebc20d6e6f))


### Miscellaneous Chores

* **deps:** lockfile maintainance ([2c4a30c](https://github.com/nethesis/parceler/commit/2c4a30c1157ecb5864e27a0ff7cb14481d85ca0b))
* **deps:** updated build environments ([1a11909](https://github.com/nethesis/parceler/commit/1a1190921adfe5e955e3252d0e0a732ff9dcf5cf))
* removed scheduler and worker from dev environment ([adbc31e](https://github.com/nethesis/parceler/commit/adbc31ea0349268846ec945017fdc02a621fe099))

## [0.2.1](https://github.com/nethesis/parceler/compare/v0.2.0...v0.2.1) (2024-06-19)


### Bug Fixes

* using generic endpoint for key verification ([05894ad](https://github.com/nethesis/parceler/commit/05894ad46a9f12d011c1856a7ce2d66480255dd2))


### Miscellaneous Chores

* **deps:** update docker/bake-action to v5 ([bd24159](https://github.com/nethesis/parceler/commit/bd241599b024418e95a9480633c2f892c441419d))

## [0.2.0](https://github.com/nethesis/parceler/compare/v0.1.2...v0.2.0) (2024-05-24)


### Features

* added confirmation before dispatcing sync on deploy ([4d1a2d9](https://github.com/nethesis/parceler/commit/4d1a2d98c18120627396bcf44aeff0e37bb9de87))
* adding freeze and unfreeze commands ([8dcd245](https://github.com/nethesis/parceler/commit/8dcd24554cbf7e3729f166c39e760989a5ebbf7c))


### Bug Fixes

* fixed issue where custom directories are parsed to time ([c9c259c](https://github.com/nethesis/parceler/commit/c9c259c33904fa464a04345a7cff4d91f2a44447))
* updated endpoints ([6a0304e](https://github.com/nethesis/parceler/commit/6a0304e3a7a0c8d7e22c580c20083ac95faaa653))


### Performance Improvements

* offloading file download when using S3 services ([603aa24](https://github.com/nethesis/parceler/commit/603aa2457cdd67d9515aee6c48e1b30ebd3b5b9c))


### Miscellaneous Chores

* defining phpunit most used variables ([587f9ec](https://github.com/nethesis/parceler/commit/587f9ecca25a4b04699056d198383f8826562ed2))
* releases cleanup ([e410d4a](https://github.com/nethesis/parceler/commit/e410d4a08f12feb585404331323daceb02d52b70))

## [0.1.2](https://github.com/nethesis/parceler/compare/v0.1.1...v0.1.2) (2024-05-13)


### Bug Fixes

* **scheduler:** misspell costed me another release ([1615a87](https://github.com/nethesis/parceler/commit/1615a87aefce35d17cdb8442aa62cdcb9a08f126))

## [0.1.1](https://github.com/nethesis/parceler/compare/v0.1.0...v0.1.1) (2024-05-13)


### Bug Fixes

* **scheduler:** fixing sync-repo scheduled job ([c7fae49](https://github.com/nethesis/parceler/commit/c7fae49b18c4ac9e2cce4ab468f025ba85b2b009))

## 0.1.0 (2024-05-13)


### Features

* added creation command for repositories ([6e7ffe8](https://github.com/nethesis/parceler/commit/6e7ffe8ad77c0caa1bf748a391b0eceae97d72af))
* added daily scheduling of repo sync ([1237dd6](https://github.com/nethesis/parceler/commit/1237dd612662edd8e45a03be63bb2d787c490705))
* added flysystem for S3 ([cae95a8](https://github.com/nethesis/parceler/commit/cae95a8532d11d549a28b4b76287e0c9222ea238))
* added folder to put directories in ([88df0f9](https://github.com/nethesis/parceler/commit/88df0f9304c173b3952e3fbf69dd837f1130f659))
* added logging to stdout by default ([47b19ad](https://github.com/nethesis/parceler/commit/47b19ad23e788c2008ec357019cc55dd06ed700e))
* added removal of old directories ([45062dc](https://github.com/nethesis/parceler/commit/45062dc57c55da429786368a39a9856114f86201))
* added repository filtering and rolling timestamp ([2242c3f](https://github.com/nethesis/parceler/commit/2242c3fc3a1fefab68dd72a5d9f1ee63b7862a42))
* added repository model ([c00bebb](https://github.com/nethesis/parceler/commit/c00bebb02db8ce877eaf5d238d3753e8d76ed259))
* added repository serve ([c163fa2](https://github.com/nethesis/parceler/commit/c163fa2ebb4031c3ec4e086bbe66605bd325c254))
* added timestamping of folders ([27f0573](https://github.com/nethesis/parceler/commit/27f0573bcbf4a441528b49e81e6316578631e2e2))
* freeze repository ([cbda136](https://github.com/nethesis/parceler/commit/cbda1369d5e364e4a154ba7fe54629381565f8a4))


### Bug Fixes

* added timeout of one hour to queue workers ([846ad74](https://github.com/nethesis/parceler/commit/846ad7466371b9730602619bebaaab2b6faeb1f4))
* avoiding folder misplacement if job fails ([d14db9d](https://github.com/nethesis/parceler/commit/d14db9d05d44cdb33b1ffb6dad54f4b4fa1cf053))
* completely misspelled storage location ([2c7f807](https://github.com/nethesis/parceler/commit/2c7f80759ebbc0ca4fe63078e20878d1550166dd))
* **deploy:** fixed issue with selinux ([8399ad9](https://github.com/nethesis/parceler/commit/8399ad99f0b18f12722e21209b1acb23b1f76379))
* fixed issue with multiple queue workers ([18bbf42](https://github.com/nethesis/parceler/commit/18bbf42d5de39a24f7ae98398633ae17000ad037))
* fixing entrypoint missing variables ([d56e0f0](https://github.com/nethesis/parceler/commit/d56e0f0e2563fd9148fa3cb7aa1766f4854805cb))
* removed default user creation ([4328bde](https://github.com/nethesis/parceler/commit/4328bde1be4f581167ee40409301267faa586d58))
* updated tests and refactored commands ([1390aa4](https://github.com/nethesis/parceler/commit/1390aa4cd53f9e1958a7cc9b18ee17e5c0b91d9c))
* using queue:listen on dev environments ([86ea21e](https://github.com/nethesis/parceler/commit/86ea21ee3c517add19b5848aabf848ba29b950a0))
* waiting for app to setup completely ([d18f7f4](https://github.com/nethesis/parceler/commit/d18f7f4a6512b458207eb6940616514955563981))


### Performance Improvements

* removed cache from final image ([99d5740](https://github.com/nethesis/parceler/commit/99d574085f25afe6c62567387e21d701b20ce605))


### Miscellaneous Chores

* added licensing ([23e0375](https://github.com/nethesis/parceler/commit/23e037589c06d90dc33a17b99aba1fbb62be8e96))
* composer hash refresh ([c7535de](https://github.com/nethesis/parceler/commit/c7535dec411c7832919c62cb427152692791d6c7))
* **deps:** added pest testing framework ([04b0e66](https://github.com/nethesis/parceler/commit/04b0e663f9ace982487f8b1bd5a676c1519a4c00))
* **deps:** added pnctl to required extensions ([a567c7c](https://github.com/nethesis/parceler/commit/a567c7c8b5dd4471e1426c7f58e4a1f6cbc6a528))
* **deps:** updated composer dependencies ([ed4758c](https://github.com/nethesis/parceler/commit/ed4758c277dbfae342e1ff31caff874f543aa6fc))
* initial commit ([07a5c91](https://github.com/nethesis/parceler/commit/07a5c915c0f0a58cd89e38da72478c8159f9b8a7))
* miscellaneous tasks ([0ca1a40](https://github.com/nethesis/parceler/commit/0ca1a40c0215d125658d78311006dc54aca40188))
* removed placeholder test ([9461828](https://github.com/nethesis/parceler/commit/946182868a1427288408872df03806ba7a1280c8))
* renamed project ([290eeb3](https://github.com/nethesis/parceler/commit/290eeb33e3e1b80a74671816589c583071fe72b7))
* renamed project in .env.example ([57ee8f7](https://github.com/nethesis/parceler/commit/57ee8f75d2ad3a02cb32f9ea89fe0f2461f9b08e))


### Continuous Integration

* adding release-please ([453b8dd](https://github.com/nethesis/parceler/commit/453b8dd9607b1d34b4e168cc7d42d709a188a34e))
