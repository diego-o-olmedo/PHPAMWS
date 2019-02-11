<?php

/**
 * Antimalware Scanner
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2018
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/marcocesarato/PHP-Antimalware-Scanner
 * @version 0.4.0.35
 */
namespace marcocesarato\amwscan;

/**
 * Class Console
 * Console manager
 * @package marcocesarato\amwscan
 */
class Console {

    // Newlines
    public static $PHP_EOL2 = PHP_EOL . PHP_EOL;
    public static $PHP_EOL3 = PHP_EOL . PHP_EOL . PHP_EOL;

    /**
     * Font colors
     * @var array
     */
    public static $foreground_colors = array(
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    );

    /**
     * Background colors
     * @var array
     */
    public static $background_colors = array(
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    );

    /**
     * Print header
     */
    public static function header(){
        $version = Application::$VERSION;
        $header  = <<<EOD


 █████╗ ███╗   ███╗██╗    ██╗███████╗ ██████╗ █████╗ ███╗   ██╗
██╔══██╗████╗ ████║██║    ██║██╔════╝██╔════╝██╔══██╗████╗  ██║
███████║██╔████╔██║██║ █╗ ██║███████╗██║     ███████║██╔██╗ ██║
██╔══██║██║╚██╔╝██║██║███╗██║╚════██║██║     ██╔══██║██║╚██╗██║
██║  ██║██║ ╚═╝ ██║╚███╔███╔╝███████║╚██████╗██║  ██║██║ ╚████║
╚═╝  ╚═╝╚═╝     ╚═╝ ╚══╝╚══╝ ╚══════╝ ╚═════╝╚═╝  ╚═╝╚═╝  ╚═══╝
Github: https://github.com/marcocesarato/PHP-Antimalware-Scanner

                      version $version

EOD;
        Console::display($header, "green");
        Console::display(PHP_EOL);
        Console::display("                                                               ", 'black', 'green');
        Console::display(PHP_EOL);
        Console::display("                   PHP Antimalware Scanner                     ", 'black', 'green');
        Console::display(PHP_EOL);
        Console::display("                  Created by Marco Cesarato                    ", 'black', 'green');
        Console::display(PHP_EOL);
        Console::display("                                                               ", 'black', 'green');
        Console::display(self::$PHP_EOL2);
    }

    /**
     * Print title
     * @param $text
     */
    public static function title($text){

    }

    /**
     * Print progress
     * @param $done
     * @param $total
     * @param int $size
     */
    public static function progress($done, $total, $size = 30) {
        static $start_time;
        if($done > $total) {
            return;
        }
        if(empty($start_time)) {
            $start_time = time();
        }
        $now        = time();
        $perc       = (double) ($done / $total);
        $bar        = floor($perc * $size);
        $status_bar = "\r[";
        $status_bar .= str_repeat("=", $bar);
        if($bar < $size) {
            $status_bar .= ">";
            $status_bar .= str_repeat(" ", $size - $bar);
        } else {
            $status_bar .= "=";
        }
        $disp       = number_format($perc * 100, 0);
        $status_bar .= "] $disp%";
        $rate       = ($now - $start_time) / $done;
        $left       = $total - $done;

        $eta        = round($rate * $left, 2);
        $eta_type = "sec.";
        $elapsed    = $now - $start_time;
        $elapsed_type = "sec.";

        if($eta > 59){
            $eta_type = "min.";
            $eta = round($eta / 60);
        }

        if($elapsed > 59){
            $elapsed_type = "min.";
            $elapsed = round($elapsed / 60);
        }

        self::display("$status_bar ", "black", "green");
        self::display(" ");
        self::display("$done/$total", "green");
        self::display(" remaining: " . number_format($eta) . " ". $eta_type ."  elapsed: " . number_format($elapsed) . " " . $elapsed_type);
        flush();
        if($done == $total) {
            self::display(PHP_EOL);
        }
    }

    /**
     * Print message without writing logs
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     */
    public static function display($string, $foreground_color = "white", $background_color = null, $escape = true) {
        self::write($string, $foreground_color, $background_color, false, $escape);
    }

    /**
     * Print message
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     * @param null $log
     */
    public static function write($string, $foreground_color = "white", $background_color = null, $log = null, $escape = true) {
        $return_string = $string;
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $foreground_color = null;
            $background_color = null;
        }
        if(isset($_REQUEST['log']) && $log === null) {
            $log = true;
        }
        if($escape) {
            $return_string = self::escape($return_string);
        }
        $colored_string = "";
        if(isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . "m";
        }
        if(isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . "m";
        }
        $colored_string .= $return_string . "\033[0m";

        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo $return_string;
        } else {
            echo $colored_string;
        }

        if($log) {
            self::log($string);
        }
    }

    /**
     * Read input
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     * @return string
     */
    public static function read($string, $foreground_color = "white", $background_color = null) {
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $foreground_color = null;
            $background_color = null;
        }
        $colored_string = "";
        if(isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . "m";
        }
        if(isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . "m";
        }
        $colored_string .= $string . "\033[0m";

        $read = null;

        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo Application::$NAME . " > " . trim($string) . " ";
            $read = trim(stream_get_line(STDIN, 1024, PHP_EOL));
        } else {
            $read = readline(Application::$NAME . " > " . trim($colored_string) . " ");
        }

        return $read;
    }

    /**
     * Print code
     * @param $string
     * @param null $log
     */
    public static function code($string, $errors = array(), $log = null) {
        if(isset($_REQUEST['log']) && $log === null) {
            $log = true;
        }
        $code = $string;
        if(count($errors) > 0) {
            foreach($errors as $pattern) {
                preg_match($pattern, $code, $string_match);
                $escaped = self::escape($string_match[0]);
                $code = str_replace($string_match[0],  "\033[" . self::$foreground_colors['red'] . "m" . $escaped . "\033[" . self::$foreground_colors['white'] . "m", $code);
            }
        }
        $lines = explode("\n", $code);
        for($i = 0; $i < count($lines); $i ++) {
            if($i != 0) {
                self::display(PHP_EOL);
            }
            self::display("  " . str_pad((string) ($i + 1), strlen((string) count($lines)), " ", STR_PAD_LEFT) . ' | ', 'yellow');
            self::display($lines[$i], 'white', null, false);
        }
        if($log) {
            self::log($string);
        }
    }

    /**
     * Write logs
     * @param $string
     */
    public static function log($string) {
        file_put_contents(Application::$PATH_LOGS, $string, FILE_APPEND);
    }

    /**
     * Escape colors string
     * @param $string
     * @return string
     */
    public static function escape($string) {
        return mb_convert_encoding(preg_replace('/(e|\x1B|[[:cntrl:]]|\033)\[([0-9]{1,2}(;[0-9]{1,2})?)?[mGKc]/','',$string), "utf-8", "auto");
    }

    /**
     * Print Helper
     */
    public static function helper() {
        $exploit_list = implode(PHP_EOL.'- ', array_keys(Application::$EXPLOITS));
        $functions_list = implode(PHP_EOL.'- ', Application::$DEFAULT_FUNCTIONS);
        Console::display("                                                               ", 'black', 'cyan');
        Console::display(PHP_EOL);
        Console::display("                             HELP                              ", 'black', 'cyan');
        Console::display(PHP_EOL);
        Console::display("                                                               ", 'black', 'cyan');
        Console::display(PHP_EOL);
        $help = <<<EOD

Exploits: 
- $exploit_list
    
Functions: 
- $functions_list

Arguments:
<path>                       Define the path to scan (default: current directory)

Flags:
-a   --agile                 Help to have less false positive on WordPress and others platforms
                             enabling exploits mode and removing some common exploit pattern
                             but this method could not find some malware
-e   --only-exploits         Check only exploits and not the functions
                             -- Recommended for WordPress or others platforms
-f   --only-functions        Check only functions and not the exploits
-h   --help                  Show the available flags and arguments
-l   --log                   Write a log file 'scanner.log' with all the operations done
-s   --scan                  Scan only mode without check and remove malware. It also write
                             all malware paths found to 'scanner_infected.log' file
                             
     --exploits="..."        Filter exploits
     --functions="..."       Define functions to search
     --whitelist-only-path   Check on whitelist only file path and not line number
     
Notes: For open files with nano or vim run the scripts with "-d disable_functions=''"
       examples: php -d disable_functions='' scanner ./mywebsite/http/ --log --agile --only-exploits
                 php -d disable_functions='' scanner --agile --only-exploits
                 php -d disable_functions='' scanner --exploits="double_var2" --functions="eval, str_replace"
EOD;
        self::display($help . self::$PHP_EOL2 . Application::$ARGV->usage() . self::$PHP_EOL2);
        die();
    }
}