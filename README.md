# ![amwscan](amwscan.png)

# AMWSCAN - PHP Antimalware Scanner

**Version:** 0.5.0.67 beta

**Github:** https://github.com/marcocesarato/PHP-Antimalware-Scanner

**Author:** Marco Cesarato

## PHP Malware Scanner Free Tool

This package, written in php, can scan PHP files and analyze your project for find malicious code inside it.
It provides a text terminal console interface to scan files in a given directory and find PHP code files the seem to contain malicious code.
The package can also scan the PHP files without outputting anything to the terminal console. In that case the results are stored in a log file.
This scanner can work on your own php projects and on a lot of others platform.
Use this command `php -d disable_functions` for run the program without issues.

## Requirements

- php 5+

## Install
 
### Release

You can use one of this method for install the scanner downloading it from github or directly from console.

#### Download

Go on GitHub page and press on Releases tab or download the raw file from:
https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/scanner

#### Console

1. Run this command from console (scanner will be download on your current directory): 

   `wget https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/scanner --no-check-certificate`

2. Run the scanner:

   `php scanner ./dir-to-scan -a` ...

### Source

##### Download

Click on GitHub page "Clone or download" or download from: 
https://codeload.github.com/marcocesarato/PHP-Antimalware-Scanner/zip/master

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

## Distribute

For compile `/src/` folder to single file `/dist/scanner` you need to do this:

1. Install composer requirements:
   `composer install`
2. Run distribute script *(replace 0.5.x.x with your version number)*:
   `php distribute 0.5.x.x`

## Test

For test detection of malwares you can try detect they from this collection:

https://github.com/marcocesarato/PHP-Malware-Collection

## Scanning mode

You could find some false positive during scanning. For this you can choice the aggression level as following:

| Param               | Abbr | Aggressivity      | Description                                                                                                                                                                     |
|---------------------|------|-------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|                     |      | VERY AGGRESSIVE   | Search for all functions/exploits on lists and all malware signatures without restrictions                                                                                      |
| `--agile`           | -a   | MEDIUM            | Search for some specific exploits on lists with some restrictions and all malware signatures (on Wordpress and others platform could find more malware and more false positive) |
| `--only-signatures` | -s   | NORMAL            | Search for all malware signatures (could be perfect for Wordpress and others platform for have less false positive)                                                             |
| `--only-exploits`   | -e   | AGGRESSIVE        | Search for exploits on lists                                                                                                                                                    |
| `--only-functions`  | -f   | MEDIUM            | Search for all functions on lists (on some obfuscated code can't be detected)                                                                                                   |

### Suggestions

`no params (default) (not recommended)` **VERY AGGRESSIVE**

`--agile | -a (recommended)` **MEDIUM AGGRESSION**

`--only-signatures | -s (recommended)` **LESS AGGRESSIVE**`

`--only-exploits | -e` **AGGRESSIVE**

`--only-functions | -f (not recommended)` **MEDIUM AGGRESSION**

Then if you run the scanner on a Wordpress project or others common platforms type `--only-signatures` or `--agile` as argument for a check with less false positive.

#### Examples:
```
php -d disable_functions='' scanner -s
php -d disable_functions='' scanner -a
```

## Detection Options

When a malware is detected you will have the following choices (except when scanner is in report scan mode `--report`):
- Delete file
- Move to quarantine `(move to ./quarantine)`
- Try remove evil code
- Try remove evil line code
- Open/Edit with vim `(need php -d disable_functions='')`
- Open/Edit with nano `(need php -d disable_functions='')`
- Add to whitelist `(add to ./scanner_whitelist.csv)`
- Show source
- Ignore

## Usage

```		
Arguments:
<path>                       - Define the path to scan (default: current directory)

Flags:
-a   --agile                 - Help to have less false positive on WordPress and others platforms
                               enabling exploits mode and removing some common exploit pattern
-f   --only-functions        - Check only functions and not the exploits
-e   --only-exploits         - Check only exploits and not the functions,
                               this is recommended for WordPress or others platforms
-s   --only-signatures       - Check only virus signatures (like exploit but more specific)
-h   --help                  - Show the available flags and arguments
-l   --log=""                - Write a log file on 'scanner.log' or the specified filepath
-r   --report                - Report scan only mode without check and remove malware. It also write
                               a report with all malware paths found to 'scanner_infected.log'
-u   --update                - Update scanner to last version
-v   --version               - Get version number

--max-filesize=""            - Set max filesize to scan (default: -1)

--exploits=""                - Filter exploits
--functions=""               - Define functions to search
--whitelist-only-path        - Check on whitelist only file path and not line number

--list                       - Get default exploit and functions list
--list-exploits              - Get default exploits list
--list-functions             - Get default functions lists
     
Notes: 
For open files with nano or vim run the scripts with "-d disable_functions=''"

Examples: php -d disable_functions='' scanner ./mywebsite/http/ -l -s --only-exploits
          php -d disable_functions='' scanner -s --max-filesize="5MB"
          php -d disable_functions='' scanner -s -logs="/user/marco/scanner.log"
          php -d disable_functions='' scanner --agile --only-exploits
          php -d disable_functions='' scanner --exploits="double_var2" --functions="eval, str_replace"
                         
Usage: php scanner [--agile|-a] [--help|-h] [--log|-l] [--report|-r] [--version|-v] [--update|-u] [--only-signatures|-s] [--only-exploits|-e] [--only-functions|-f] [--list] [--list-exploits] [--list-functions] [--exploits <exploits>] [--functions <functions>] [--whitelist-only-path] [<path>]
```

### Exploits and Functions List

#### Exploits
- `eval_chr`, `eval_preg`, `eval_base64`, `eval_comment`, `eval_execution`, `align`, `b374k`, `weevely3`, `c99_launcher`, `too_many_chr`, `concat`, `concat_vars_with_spaces`, `concat_vars_array`, `var_as_func`, `global_var_string`, `extract_global`, `escaped_path`, `include_icon`, `backdoor_code`, `infected_comment`, `hex_char`, `hacked_by`, `killall`, `globals_concat`, `globals_assign`, `base64_long`, `base64_inclusion`, `clever_include`, `basedir_bypass`, `basedir_bypass2`, `non_printable`, `double_var`, `double_var2`, `global_save`, `hex_var`, `register_function`, `safemode_bypass`, `ioncube_loader`, `nano`, `ninja`, `execution`, `execution2`, `execution3`, `shellshock`, `silenced_eval`, `silence_inclusion`, `ssi_exec`, `htaccess_handler`, `htaccess_type`, `file_prepend`, `iis_com`, `reversed`, `rawurlendcode_rot13`, `serialize_phpversion`, `md5_create_function`, `god_mode`, `wordpress_filter`, `password_protection_md5`, `password_protection_sha`, `custom_math`, `custom_math2`, `uncommon_function`, `download_remote_code`, `download_remote_code2`, `download_remote_code3`, `php_uname`, `etc_passwd`, `etc_shadow`, `explode_chr`

#### Functions
- `il_exec`, `shell_exec`, `eval`, `system`, `create_function`, `exec`, `assert`, `syslog`, `passthru`, `define_syslog_variables`, `posix_kill`, `posix_uname`, `proc_close`, `proc_get_status`, `proc_nice`, `proc_open`, `proc_terminate`, `inject_code`, `apache_child_terminate`, `apache_note`, `define_syslog_variables`

## Screenshots

![Screen 1](screenshots/screenshot_1.png)![Screen 2](screenshots/screenshot_2.png)![Screen 3](screenshots/screenshot_3.png)
