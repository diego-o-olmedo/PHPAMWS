<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [0.10.4](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.10.3...v0.10.4) (2021-10-03)


### Bug Fixes

* Disable html report when not on report mode [#36](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues/36) ([49a5aa](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/49a5aadc3106c8b0112e1ce5d76b992764a58201))
* Undefined index when running programmatically [#35](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues/35) ([fb265f](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/fb265fa4f5d0d324e8d04504e5a1e7d2d0c06c36))

---

## [0.10.3](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.10.2...v0.10.3) (2021-10-02)


### Bug Fixes

* Programmatically use infinity loop [#34](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues/34) ([a0c953](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/a0c953fea470af7dc8f9285484a1ea16d9fb546e))

---

## [0.10.2](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.10.1...v0.10.2) (2021-09-04)


---

## [0.10.1](https://github.com/marcocesarato/AMWScan/compare/v0.10.0...v0.10.1) (2021-07-16)


### Bug Fixes

* Some warnings and possibile issues ([3af6ca](https://github.com/marcocesarato/AMWScan/commit/3af6ca3a35f56db3f218363ed3262b70f7122653))

---

## [0.10.0](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.9.1...v0.10.0) (2021-06-17)


### Features

* Add --scan-all flag to scan all files and removed mime checking ([46a091](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/46a091f960b462bed871d4cd73c393c7e61fa670))

---

## [0.9.1](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.9.0...v0.9.1) (2021-06-06)


### Bug Fixes

* Allow scanner to skip unreadable directories [#22](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues/22) ([61533f](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/61533f85287e862e73072e0d09c678bfab28bea2))
* Better explanation of path-report option [#20](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues/20) ([1fae07](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/1fae07ab3197954ac08dbf1d4a33c8c58789fa2a))
* Improve signature optimization for raw contents ([0c55db](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/0c55dbb14ce6108b30ed264f3fa3291edd0e3046))

---

## [0.9.0](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.8.3...v0.9.0) (2021-03-28)


### Features

* Add new dangerous functions ([1194eb](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/1194eb533d154c5fd20f5f0d038cfedc55b31427))
* Add new raw data signatures ([a5b0b4](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/a5b0b447014db949bcb6b4b69d2e4380af92229b))

### Bug Fixes

* Autoload on phar ([3c7295](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/3c7295ca0add3c277dfbde32432e1afed11cd02e))

---

## [0.8.3](https://github.com/marcocesarato/AMWScan/compare/v0.8.2...v0.8.3) (2021-03-03)


### Bug Fixes


##### Autoload

* Change chdir if not on phar archive ([aaa257](https://github.com/marcocesarato/AMWScan/commit/aaa257660e9bd5f9528d2bb85af8e74e9d5b0752))

##### Modes

* Only exploits, functions and signatures modes [#18](https://github.com/marcocesarato/AMWScan/issues/18) ([d901f7](https://github.com/marcocesarato/AMWScan/commit/d901f7bbc763068ff6a9f56ee446753eefc520b5))

---

## [0.8.2](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.8.1.241...v0.8.2) (2021-02-27)


### Bug Fixes

* Silent mode and disable cache/checksum programmatically ([73cd76](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/73cd76f59b0a5b418c614f4edcf4f9e1e1aa9fc5))
* Update autoloader ([2b61fd](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/2b61fda2cf6d4d9d4b6ed2f0c754e5d678e35586))

---

## [0.8.1.241](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.8.1.236...vv0.8.1.241) (2021-01-17)

### Bug Fixes

* Minified report template ([aca0cf](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/aca0cff8cbb13dfbadc87990019ed63cae67f5cf))

---

## [0.8.1.236](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.8.0.234...vv0.8.1.236) (2021-01-11)


### Features

* Add disable-checksum flag ([ff0bc2](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/ff0bc2e399f60c9cb391fa6ca41cf5af02b8a016))

---

## [0.8.0.234](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.5.177...vv0.8.0.234) (2021-01-10)


### Features

* Add cache for encoded functions ([aa8f98](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/aa8f98b59772444b5e8aaecc533497010385bc8d))
* Add defs list functions encoded ([5dd139](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/5dd139e50c9432eb1d5a3fbcf1cd899d3d8e5d05))
* Add deobfuscate commands and functionality and fix backup path ([9238b6](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/9238b6e71a945169d88138422d5621a6607b392b))
* Add figlet class for figlet generation ([3363ae](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/3363aed6f6c1eaae18aebc880f8bcc1636ec7832))
* Add header figlet generation with random font ([bf0fc0](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/bf0fc074d5ec3531db72dc29750e5c3a6d8a50e2))
* Add new dangerous functions ([a0ada4](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/a0ada447d291e67a231eb28da5cb852ac9e534db))
* Add new encoded functions on list ([459d75](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/459d75d027a26ea590bfc7296449b7f1d5930cf1))
* Decode chr functions ([da4fa8](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/da4fa8ed6b38ed48b43f6875b2d151e84f19b8c2))
* Report now is printed every time run, add `--disable-report` and value name on flags ([8f5256](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/8f525610ed0875f0a6ee30c7693c2918933e4212))

##### Cache

* Improve performance and reduce data loss ([4747ad](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/4747ad5e29f9ad73c89d3a7bb7f754ca2668706d))

### Bug Fixes

* Deobfuscation php open and close tags ([0c359e](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/0c359efcc8d904dcfbbe294843d4f0833c384e48))
* Disable color ([59c287](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/59c2879158d730dbf8d6be8a5c1978f8337ecf10))
* Disable startup error display ([eb2950](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/eb29500f150975aef3eb888cdd72f0f9c8da5d33))
* Exploit lite mode ([662552](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/662552ab1123dcae9b4e2cf1322f22d27d6fd1c7))
* Improve deobfuscation with multiple types ([761449](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/7614492fa83233102ef77ab38df2dc73160fbf03))
* Improve functions check with less false positivity ([df9882](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/df98821b4523e941540c824893812e8c3242555d))
* Match patterns catastrophic backtracking issue ([49544d](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/49544d7b151e93cd13b8970d282f15488563d07d))
* Match result cleaning ([6f3d11](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/6f3d115f9a3c1855e6d6e9200cec4b8f6ba3c780))
* Php file code detection ([932b3f](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/932b3f7c639b8f138c6aaaf4355d74a0e9838fae))
* Switch options show preview lines ([aebd2c](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/aebd2cb1f8f7f546676aaae04c843d4506821be0))
* Trim filtered exploits and functions ([900de7](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/900de7043012c7b004a75b33f2053a3229affdf7))

##### Wordpress

* Multi checksum arrays ([42c9f5](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/42c9f5bb1f341a8efa049230436c627bcf8d5431))

---

## [0.7.5.177](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.4.147...vv0.7.5.177) (2020-12-31)


### Features

* Add console default line max length ([32ff20](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/32ff2047c03666db0ad8f96765160d076ffb31df))
* Add description of evil code on interactive cli ([71d947](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/71d947f1b5381eaf265cbd627d9ad8d71ee93412))
* Add functions link and improve severity report of encoded functions ([c42a33](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/c42a3394341af28ac608fe290acab52c221b827a))
* Add malware found description adn danger level for exploits on html report ([c6f7be](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/c6f7be48c3662161ee5b1feea2f2b5048d7a0405))
* Add new funtions encoded and add new decoders for functions ([e08a2a](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/e08a2ac6648003180ab9343fdaa07d28e1970623))
* Add source guardian exploit ([62eb02](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/62eb024e44bc6c6d1d6a89fdab16bdb65df947e1))
* Helper commands list autogeneration ([6c2f69](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/6c2f69eb69a88c9da0b6f42b3655e5193f677f5f))
* Improve report ui ([6763cc](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/6763cc622aa7a66604a89e779c875c8bd4cc2989))

### Bug Fixes

* Add margin right on report file info badges ([677942](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/6779426afba8732dd6f6c423525800efa37348f6))
* Build toggle report table ([d71421](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/d71421c968a46e46f999fffada5e0fb5f76291bb))
* Collapse report table ([c2ccd4](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/c2ccd436acc4c4b986853c209b7494add318be35))
* Dec and oct decoding ([f472f0](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/f472f01efc122e08fe4b449a230385e7f2fde932))
* Deobfuscator php string detection ([a87656](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/a876561d46eebfb884d7e1bb7f23e43c4aad10f0))
* Phar running detection ([fdb19a](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/fdb19a4b8b24c1426aacf21c6896e6c62adbed5e))
* Remove duplicate match without line number ([8f92e2](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/8f92e21ce8f97151d0f6d982e5f39dbcf0c104df))
* Toggle report table ([de35a1](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/de35a188edb717d727e52f6df08cfba1614e7c13))

---

## [0.7.4.147](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.3.133...vv0.7.4.147) (2020-12-27)


### Features

* Add dockerfile ([a3add6](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/a3add63323d40cc1cbc70a98e2516251283c7b12))
* Report with html format ([8d0194](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/8d0194a548c8f7109f401a03458f3b16cf233830))

### Bug Fixes

* Append extension report ([fc2630](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/fc2630f27af103c8580fec3cbf4ca1481189ff2c))
* Duplicated exploits on report and add encoded functions also on exploit mode ([4f5ce8](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/4f5ce847c996091c9c66f150f3cb3f98b0cc68d4))

---

## [0.7.3.133](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.2.125...vv0.7.3.133) (2020-12-24)


### Features

* Add possibility to check a single file ([208438](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/208438f442fe812b0364fd6d5aa28238648b268b))

### Bug Fixes

* Improve key cache performance ([e25f20](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/e25f2036bd77d6047d4f21cf40943f8ec44d1847))
* Multi exploit, functions and definitions detection [#14](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues/14) ([d1035a](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/d1035a4fa6c6387871c67452592772399020350d))
* Simplify settings and fix notices ([75ac11](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/75ac117e126c774a980add4281eede479633a7b2))

---

## [0.7.2.125](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.2.123...vv0.7.2.125) (2020-10-25)


### Bug Fixes

* Disable cache getter ([34e438](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/34e43853b756c977ffe6cd84b51ebbdb3e146744))

---

## [0.7.2.123](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.1.113...vv0.7.2.123) (2020-10-25)


### Features

* Add cache system ([639fca](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/639fca63cd0f1bb449f5804098f396a938c281b8))
* Add disable cache flag ([ab1d45](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/ab1d451bd7c8c269c3d83925e18b2a7d2d6d0e8f))
* Add verifying status progress bar ([702ba1](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/702ba1de0e2007c337a32741c84300726d72ca5a))
* Implement cache system on Wordpress module ([87ce9d](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/87ce9d83b2ecedc4f8b9a3aafad288efbceb948a))

### Bug Fixes

* Wordpress plugin domain detection ([6d1b71](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/6d1b71d3691670f6d5dff7db5b0df9d6cbeabd8a))

---

## [0.7.1.113](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.7.0.110...vv0.7.1.113) (2020-10-24)


### Features

* Wordpress plugins checksum verifier ([e420a5](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/e420a582d23ddd9b389fdf6b82e1d67183a7163c))

---

## [0.7.0.110](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.6.0.109...vv0.7.0.110) (2020-10-24)


### Features

* Add autoload ([d9cfbe](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/d9cfbe2ce2580e06c9c0c0b75a06e4417fcce77d))
* New wordpress detector and checksum file path verifier ([21d76f](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/21d76f0b0b8e6ea9bd7bd57d8c846f20e025a250))

### Bug Fixes

* Autoload for sub namespace classes ([31ace4](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/31ace4544c28d5f9c1e5bc70ce573ffaa6ef0536))
* Deprecations and split some workflows ([d82bb3](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/d82bb3ccca4a0e2f59150c68d7153ea08f82ed1e))

---

## [0.6.0.109](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.6.0.107...vv0.6.0.109) (2020-10-08)


### Features

* Add disable colors flag and fix update and header not showed bug ([650778](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/6507785cda987a5dd430e937b9a9fdb2afb88f12))

---

## [0.6.0.107](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.5.3.93...vv0.6.0.107) (2020-10-07)


### Features

* Add getters and setter for programmatically usage ([4441b8](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/4441b85c2688e566ebe2eb4d897278dcd8640cff))
* Add offset and limit flags ([9cf7b2](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/9cf7b221d74608733995e903852cd3ac0cb63ef4))
* Add path setters and backups flags ([010a87](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/010a87584b72cbf7958f54f5c5b61a14fd4133c7))
* Auto silent and auto skip on programmatically mode ([f38c78](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/f38c78d6de8f99a1b11cea15eff1ee3aa6d853bb))

### Bug Fixes

* Report quarantine ([2d8582](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/2d858267927bb169e30e21699b5555fccdd3f0cf))
* Summary whitelist ([f2a7d6](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/f2a7d67bea668e49ce8002feae9cd9e0fd063841))

---

## [0.5.3.93](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.5.2.75...vv0.5.3.93) (2020-10-06)


### Features

* Add auto-whitelist ([cb6ac4](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/cb6ac47002ccf91b5ac07851df57d23f43f65503))
* Improve whitelist system and some code improvement ([beeff0](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/beeff013bb5bf3a2a910dc8850d30441f064b515))

---

## [0.5.2.75](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.5.1.72...vv0.5.2.75) (2020-10-05)


### Features

* Add auto prompt flags ([95ce35](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/95ce35298ac1e00dcbdf0118cefcb1758c990d25))
* Add ignore and filter paths flag ([3be18d](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/3be18d313bb4b2324515c4913608d18bba4fef85))

---

## [0.5.1.72](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/0.5.0.71...vv0.5.1.72) (2020-10-05)


---

## [0.5.0.71](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/0.5.0.70...v0.5.0.71) (2020-03-12)


### Features

* Add exploit detail log of infected files ([d15815](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/d15815934625912d7bc9d8b5ef9c3fcf32054300))

### Bug Fixes

* Directory with phar host prefix issue and partial revert of previuos commit ([1de045](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/1de045af070dc38bb3b7073b3f48527da90a1a98))

---

## [0.5.0.70](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/v0.5.0.69...v0.5.0.70) (2020-03-11)


### Bug Fixes

* Directory with phar host prefix issue ([8ddcc8](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/8ddcc85f07707cad4693a68054e86a923745559d))

---

## [0.5.0.69](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/0.5.0.68...vv0.5.0.69) (2020-01-30)


### Features

* Add htaccess on default file extensions scan check ([b06e64](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/b06e642abc44cdbf5b9ad51a7bab70636004b570))

---

## [0.5.0.68](https://github.com/marcocesarato/PHP-Antimalware-Scanner/compare/0.5.0.67...v0.5.0.68) (2019-12-18)


### Features

* Added bin folder with scripts and changed distribute to build ([80fde0](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/80fde02766fc9f125e8b4d9cd59c5b7daaf1818c))
* New build system and fixed some bugs ([47eaf9](https://github.com/marcocesarato/PHP-Antimalware-Scanner/commit/47eaf9739343d76bd0d204433080d42be86de79a))