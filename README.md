<div align="center">

![Version](images/logo.png)

<h1 align="center">PHP Antimalware Scanner</h1>

![Version](https://img.shields.io/badge/version-0.7.5.177-brightgreen?style=for-the-badge)
![Requirements](https://img.shields.io/badge/php-%3E%3D%205.5-4F5D95?style=for-the-badge)
![Code Style](https://img.shields.io/badge/code%20style-PSR-blue?style=for-the-badge)
![License](https://img.shields.io/github/license/marcocesarato/PHP-Antimalware-Scanner?style=for-the-badge)
[![GitHub](https://img.shields.io/badge/GitHub-Repo-6f42c1?style=for-the-badge)](https://github.com/marcocesarato/PHP-Antimalware-Scanner)

#### If this project helped you out, please support us with a star :star:

</div>

## Description

PHP Antimalware Scanner is a free tool to scan PHP files and analyze your project to find any malicious code inside it.

It provides an interactive text terminal console interface to scan a file, or all files in a given directory (file paths can be also be managed using `--filter-paths` or `--ignore-paths`), and find PHP code files that seem contain malicious code.
When a probable malware is detected, will be asked what action to take (like add to whitelist, delete files, try clean infected code etc...). 

The package can also scan the PHP files in a report mode (`--report|-r`), so without interact and outputting anything to the terminal console. In that case the results will stored in a report file in html (default) or txt format (`--report-format <format>`).

This scanner can work on your own php projects and on a lot of others platform using the right combinations of configurations (ex. using `--lite|-l` flag can help to find less false positivity).

:warning: *Remember that you will be solely responsible for any damage to your computer system or loss of data that results from such activities.
You are solely responsible for adequate protection and backup of the data before execute the scanner.*

### How to contribute

Have an idea? Found a bug? Please raise to [ISSUES](https://github.com/marcocesarato/PHP-Antimalware-Scanner/issues) or [PULL REQUEST](https://github.com/marcocesarato/PHP-Antimalware-Scanner/pulls).
Contributions are welcome and are greatly appreciated! Every little bit helps.

## :blue_book: Requirements

- php 5.5+

## :book: Install

### Release

You can use one of this method for install the scanner downloading it from github or directly from console.

#### Download

Go on GitHub page and press on Releases tab or download the raw file from:

[![Download](https://img.shields.io/badge/Download-Latest%20Build-important?style=for-the-badge)](https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/scanner)

#### Console

1. Run this command from console (scanner will be download on your current directory):

   `wget https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/scanner --no-check-certificate`

2. Run the scanner:

   `php scanner ./dir-to-scan -l ...`

3. *(Optional)* Install as bin command (Unix Bash)

    Run this command:
    
    ```sh
    wget https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/scanner --no-check-certificate -O /usr/bin/awscan.phar && \
    printf "#!/bin/bash\nphp /usr/bin/awscan.phar \$@" > /usr/bin/awscan && \
    chmod u+x,g+x /usr/bin/awscan.phar && \
    chmod u+x,g+x /usr/bin/awscan && \
    export PATH=$PATH":/usr/bin"
    ```
   
   Now you can run the scanner simply with this command: `awscan ./dir-to-scan -l...`

### Source

##### Download

Click on GitHub page "Clone or download" or download from:

[![Download](https://img.shields.io/badge/Download-Source-important?style=for-the-badge)](https://codeload.github.com/marcocesarato/PHP-Antimalware-Scanner/zip/master)

##### Composer

1. Install composer
2. Type `composer require marcocesarato/amwscan`
3. Go on `vendor/marcocesarato/amwscan/` for have source
4. Enjoy

##### Git

1. Install git
2. Copy the command and link from below in your terminal:
   `git clone https://github.com/marcocesarato/PHP-Antimalware-Scanner`
3. Change directories to the new `~/PHP-Antimalware-Scanner` directory:
   `cd ~/PHP-Antimalware-Scanner/`
4. To ensure that your master branch is up-to-date, use the pull command:
   `git pull https://github.com/marcocesarato/PHP-Antimalware-Scanner`
5. Enjoy

## :hammer: Build

For compile `/src/` folder to single file `/dist/scanner` you need to do this:

1. Install composer requirements:
   `composer install`
2. Run command
   `composer build`

## :microscope: Test

For test detection of malware you can try detect use this malware collection:

[![Malware Repo](https://img.shields.io/badge/GitHub-marcocesarato%20%2F%20PHP--Malware--Collection-blueviolet?style=for-the-badge)](https://github.com/marcocesarato/PHP-Malware-Collection)

## :whale: Docker

1. Build command
   `docker build --tag amwscan-docker .`
2. Run command
   `docker run -it --rm amwscan-docker bash`

## :mag_right: Scanning mode

You could find some false positive during scanning. For this you can choice the aggression level as following:

| Flags                       | :rocket:            | Description                                                                                                                                                                       |
| --------------------------- | ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| None (`default`)            | :red_circle:        | Search for all functions, exploits and malware signs without any restrictions                                                                                                     |
| `--only-exploits` or `-e`   | :orange_circle:     | Search only for exploits definitions                                                                                                                                              |
| `--lite` or `-l`            | :yellow_circle:     | Search for exploits with some restrictions and malware signs *(on Wordpress and others platform could detect less false positivity)*                                              |
| `--only-functions` or `-f`  | :yellow_circle:     | Search only for functions *(on some obfuscated code functions couldn't be detected)*                                                                                              |
| `--only-signatures` or `-s` | :green_circle:      | Search only for malware signatures *(could be a good solution for Wordpress and others platform to detect less false positivity)*                                                 |

### Suggestions

If you are running the scanner on a Wordpress project or other popular platform use `--only-signatures` or `--lite` flag for have check with less false positive but 
this could miss some dangerous exploits like `nano`.

#### Examples:

```
php -d disable_functions='' scanner -s
php -d disable_functions='' scanner -l
```

## Detection Options

When a malware is detected you will have the following choices (except when scanner is running in report mode `--report`):

- Delete file [`--auto-delete`]
- Move to quarantine `(move to ./scanner-quarantine)` [`--auto-quarantine`]
- Dry run evil code fixer `(try to infected fix code and confirm after a visual check)` [`--auto-clean`]
- Dry run evil line code fixer `(try to fix infected code and confirm after a visual check)` [`--auto-clean-line`]
- Open with vim `(need php -d disable_functions='')`
- Open with nano `(need php -d disable_functions='')`
- Add to whitelist `(add to ./scanner-whitelist.json)`
- Show source
- Ignore [`--auto-skip`]

## :computer: Usage

### Command line
```
Arguments:

<path>   - Define the path of the file or directory to scan

Flags:

--auto-clean                                   - Auto clean code (without confirmation, use with caution)
--auto-clean-line                              - Auto clean line code (without confirmation, use with caution)
--auto-delete                                  - Auto delete infected (without confirmation, use with caution)
--auto-prompt <prompt>                         - Set auto prompt command .
                                                 ex. --auto-prompt="delete" or --auto-prompt="1" (alias of auto-delete)
--auto-quarantine                              - Auto quarantine
--auto-skip                                    - Auto skip
--auto-whitelist                               - Auto whitelist (if you sure that source isn't compromised)
--backup|-b                                    - Make a backup of every touched files
--defs                                         - Get default definitions exploit and functions list
--defs-exploits                                - Get default definitions exploits list
--defs-functions                               - Get default definitions functions lists
--defs-functions-encoded                       - Get default definitions functions encoded lists
--disable-cache|--no-cache                     - Disable Cache
--disable-checksum|--no-checksum|--no-verify   - Disable checksum verifying for platforms/frameworks
--disable-colors|--no-colors|--no-color        - Disable CLI colors
--disable-report|--no-report                   - Disable report generation
--exploits <exploits>                          - Filter exploits
--filter-paths|--filter-path <paths>           - Filter path/s, for multiple value separate with comma.
                                                 Wildcards are enabled ex. /path/*/htdocs or /path/*.php
--functions <functions>                        - Define functions to search
--help|-h|-?                                   - Check only functions and not the exploits
--ignore-paths|--ignore-path <paths>           - Ignore path/s, for multiple value separate with comma.
                                                 Wildcards are enabled ex. /path/*/cache or /path/*.log
--limit <limit>                                - Set file mapping limit
--lite|-l                                      - Running on lite mode help to have less false positive on WordPress and others
                                                 platforms enabling exploits mode and removing some common exploit pattern
--log <path>                                   - Write a log file on the specified file path
                                                 [default: ./scanner.log]
--max-filesize <filesize>                      - Set max filesize to scan
                                                 [default: -1]
--offset <offset>                              - Set file mapping offset
--only-exploits|-e                             - Check only exploits and not the functions
--only-functions|-f                            - Check only functions and not the exploits
--only-signatures|-s                           - Check only functions and not the exploits.
                                                 This is recommended for WordPress or others platforms
--path-backups <path>                          - Set backups path directory.
                                                 Is recommended put files outside the public document path
                                                 [default: /scanner-backups/]
--path-logs <path>                             - Set quarantine log file
                                                 [default: ./scanner.log]
--path-quarantine <path>                       - Set quarantine path directory.
                                                 Is recommended put files outside the public document path
                                                 [default: ./scanner-quarantine/]
--path-report <path>                           - Set report log file
                                                 [default: ./scanner-report.html]
--path-whitelist <path>                        - Set whitelist file
                                                 [default: ./scanner-whitelist.json]
--report-format <format>                       - Report format (html|txt)
--report|-r                                    - Report scan only mode without check and remove malware (like --auto-skip).
                                                 It also write a report with all malware paths found
--silent                                       - No output and prompt
--update|-u                                    - Update to last version
--version|-v                                   - Get version number
--whitelist-only-path                          - Check on whitelist only file path and not line number

Usage: amwscan [--lite|-a] [--help|-h|-?] [--log|-l <path>] [--backup|-b] [--offset
        <offset>] [--limit <limit>] [--report|-r] [--report-format <format>]
        [--version|-v] [--update|-u] [--only-signatures|-s] [--only-exploits|-e]
        [--only-functions|-f] [--defs] [--defs-exploits] [--defs-functions]
        [--defs-functions-enc] [--exploits <exploits>] [--functions <functions>]
        [--whitelist-only-path] [--max-filesize <filesize>] [--silent]
        [--ignore-paths|--ignore-path <paths>] [--filter-paths|--filter-path <paths>]
        [--auto-clean] [--auto-clean-line] [--auto-delete] [--auto-quarantine]
        [--auto-skip] [--auto-whitelist] [--auto-prompt <prompt>] [--path-whitelist
        <path>] [--path-backups <path>] [--path-quarantine <path>] [--path-logs <path>]
        [--path-report <path>] [--disable-colors|--no-colors|--no-color]
        [--disable-cache|--no-cache] [--disable-report|--no-report] [<path>]

Examples:

php amwscan ./mywebsite/http/ -l -s --only-exploits
php amwscan -s --max-filesize="5MB"
php amwscan -s -logs="/user/marco/scanner.log"
php amwscan --lite --only-exploits
php amwscan --exploits="double_var2" --functions="eval, str_replace"
php amwscan --ignore-paths="/my/path/*.log,/my/path/*/cache/*"

Notes:
For open files with nano or vim run the scripts with "php -d disable_functions=''"
```

### Programmatically

On programmatically silent mode and auto skip are automatically enabled.

```php
use AMWScan\Scanner;

$app = new Scanner();
$report = $app->setPathScan("my/path/to/scan")
              ->enableBackups()
              ->enableLiteMode(true)
              ->setPathBackups("/my/path/backups")
              ->setAutoClean(true)
              ->run();
```

##### Report Object

```php
object(stdClass) (7) {
  ["scanned"]    => int(0)
  ["detected"]   => int(0)
  ["removed"]    => array(0) {}
  ["ignored"]    => array(0) {}
  ["edited"]     => array(0) {}
  ["quarantine"] => array(0) {}
  ["whitelist"]  => array(0) {}
}
```

## :art: Screenshots

### Report

> HTML report format (`default`)

![Screen Report](images/screenshot_report.png)

### Interactive CLI
![Screen Full](images/screenshot_full.png)