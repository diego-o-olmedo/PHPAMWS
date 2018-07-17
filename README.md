# ![amwscan](amwscan.png)

# AMWSCAN - PHP Antimalware Scanner

**Version:** 0.3.11.8 beta

**Github:** https://github.com/marcocesarato/PHP-Antimalware-Scanner

**Author:** Marco Cesarato

This is a php antimalware/antivirus scanner console script written in php for scan your project.
This can work on php projects and a lot of others platform.
Use this command `php -d disable_functions` for run the program without issues

## Requirements

- php 5+
- PS: a Python 3.6 version is in progress

## Wordpress and others

This can work on wordpress and a lot of others platform but need the following suggestion.
__Suggestion:__ if you run the scanner on a Wordpress project type _--exploits_ as argument for a check without false positive.

## Usage

```
OPTIONS:

    -e   --exploits    Check only exploits and not the functions
    -h   --help        Show the available options
    -l   --log         Write a log file 'scanner.log' with all the operations done
    -p   --path <dir>  Define the path to scan
    -s   --scan        Scan only mode without check and remove malware. It also write
                       all malware paths found to 'scanner_infected.log' file

NOTES: Better if your run with php -d disable_functions=''
USAGE: php -d disable_functions='' scanner -p ./mywebsite/http/ -l
```


