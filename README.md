# ![amwscan](amwscan.png)

# AMWSCAN - PHP Antimalware Scanner

**Version:** 0.3.14.27 beta

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
Arguments:

<path>                  Define the path to scan (default: current directory)
<functions>             Set some specific functions to search [ex. func1,func2,...] 
                        -- Functions must be separated by comma
                        -- Don't use spaces or use between quotes

Flags:

-a   --agile           Help to have less false positive on WordPress and others platforms
                       enabling exploits mode and removing some common exploit pattern
                       but this method could not find some malware
-e   --only-exploits   Check only exploits and not the functions 
                       -- Recommended for WordPress or others platforms
-f   --only-functions  Check only functions and not the exploits 
-h   --help            Show the available flags and arguments
-l   --log             Write a log file 'scanner.log' with all the operations done
-s   --scan            Scan only mode without check and remove malware. It also write
                       all malware paths found to 'scanner_infected.log' file

NOTES: Better if your run with php -d disable_functions=''
EXAMPLE: php -d disable_functions='' scanner ./mywebsite/http/ -l
```

## Screenshots

![Screen 1](screenshots/screenshot_1.png)![Screen 2](screenshots/screenshot_2.png)![Screen 3](screenshots/screenshot_3.png)