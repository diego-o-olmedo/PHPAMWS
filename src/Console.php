<?php

/**
 * PHP Antimalware Scanner.
 *
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2019
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 *
 * @see https://github.com/marcocesarato/PHP-Antimalware-Scanner
 */

namespace marcocesarato\amwscan;

/**
 * Class Console
 * Console manager.
 */
class Console
{
    /**
     * Font colors.
     *
     * @var array
     */
    public static $foreground_colors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    );

    /**
     * Background colors.
     *
     * @var array
     */
    public static $background_colors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    );

    /**
     * Get new line char.
     *
     * @param $n
     *
     * @return string
     */
    public static function eol($n)
    {
        $eol = '';
        for ($i = 0; $i < $n; $i++) {
            $eol .= PHP_EOL;
        }

        return $eol;
    }

    /**
     * Print header.
     */
    public static function header()
    {
        $version = Application::$VERSION;
        self::displayBreak(2);
        $header = <<<EOD
 █████╗ ███╗   ███╗██╗    ██╗███████╗ ██████╗ █████╗ ███╗   ██╗
██╔══██╗████╗ ████║██║    ██║██╔════╝██╔════╝██╔══██╗████╗  ██║
███████║██╔████╔██║██║ █╗ ██║███████╗██║     ███████║██╔██╗ ██║
██╔══██║██║╚██╔╝██║██║███╗██║╚════██║██║     ██╔══██║██║╚██╗██║
██║  ██║██║ ╚═╝ ██║╚███╔███╔╝███████║╚██████╗██║  ██║██║ ╚████║
╚═╝  ╚═╝╚═╝     ╚═╝ ╚══╝╚══╝ ╚══════╝ ╚═════╝╚═╝  ╚═╝╚═╝  ╚═══╝
Github: https://github.com/marcocesarato/PHP-Antimalware-Scanner
EOD;
        self::displayLine($header, 2, 'green');
        self::display(self::title('version ' . $version), 'green');
        self::displayBreak(2);
        self::display(self::title(''), 'black', 'green');
        self::displayBreak();
        self::display(self::title('PHP Antimalware Scanner'), 'black', 'green');
        self::displayBreak();
        self::display(self::title('Created by Marco Cesarato'), 'black', 'green');
        self::displayBreak();
        self::display(self::title(''), 'black', 'green');
        self::displayBreak(2);
    }

    /**
     * Print title.
     *
     * @param $text
     * @param string $char
     * @param int $length
     *
     * @return string
     */
    public static function title($text, $char = ' ', $length = 64)
    {
        $result = '';
        $str_length = strlen($text);
        $spaces = $length - $str_length;
        $spaces_len_half = $spaces / 2;
        $spaces_len_left = round($spaces_len_half);
        $spaces_len_right = round($spaces_len_half);

        if ((round($spaces_len_half) - $spaces_len_half) >= 0.5) {
            $spaces_len_left--;
        }

        for ($i = 0; $i < $spaces_len_left; $i++) {
            $result .= $char;
        }

        $result .= $text;

        for ($i = 0; $i < $spaces_len_right; $i++) {
            $result .= $char;
        }

        return $result;
    }

    /**
     * Print progress.
     *
     * @param $done
     * @param $total
     * @param int $size
     */
    public static function progress($done, $total, $size = 30)
    {
        static $start_time;
        if ($done > $total) {
            return;
        }
        if (empty($start_time)) {
            $start_time = time();
        }
        $now = time();
        $perc = (float)($done / $total);
        $bar = floor($perc * $size);
        $status_bar = "\r[";
        $status_bar .= str_repeat('=', $bar);
        if ($bar < $size) {
            $status_bar .= '>';
            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }
        $disp = number_format($perc * 100, 0);
        $status_bar .= "] $disp%";
        $rate = ($now - $start_time) / $done;
        $left = $total - $done;

        $eta = round($rate * $left, 2);
        $eta_type = 'sec';
        $elapsed = $now - $start_time;
        $elapsed_type = 'sec';

        if ($eta > 59) {
            $eta_type = 'min';
            $eta = round($eta / 60);
        }

        if ($elapsed > 59) {
            $elapsed_type = 'min';
            $elapsed = round($elapsed / 60);
        }

        self::display("$status_bar ", 'black', 'green');
        self::display(' ');
        self::display("$done/$total", 'green');
        self::display(' [' . number_format($elapsed) . ' ' . $elapsed_type . '/' . number_format($eta) . ' ' . $eta_type . ']');
        ob_flush();
        flush();
        if ($done == $total) {
            self::displayBreak();
        }
    }

    /**
     * Display title bar.
     *
     * @param $string
     */
    public static function displayTitle($string, $foreground_color, $background_color)
    {
        self::display(self::title(''), $foreground_color, $background_color);
        self::displayBreak();
        self::display(self::title(strtoupper($string)), $foreground_color, $background_color);
        self::displayBreak();
        self::display(self::title(''), $foreground_color, $background_color);
        self::displayBreak();
    }

    /**
     * Print break without writing logs.
     *
     * @param int $eol
     */
    public static function displayBreak($eol = 1)
    {
        self::write(self::eol($eol), 'white', null, false, true);
    }

    /**
     * Print message without writing logs.
     *
     * @param $string
     * @param int $eol
     * @param string $foreground_color
     * @param null $background_color
     * @param bool $escape
     */
    public static function displayLine($string, $eol = 1, $foreground_color = 'white', $background_color = null, $escape = true)
    {
        self::write($string . self::eol($eol), $foreground_color, $background_color, false, $escape);
    }

    /**
     * Print option.
     *
     * @param $num
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     * @param bool $escape
     */
    public static function displayOption($num, $string, $foreground_color = 'white', $background_color = null, $escape = true)
    {
        self::write('    [' . $num . '] ' . $string . self::eol(1), $foreground_color, $background_color, false, $escape);
    }

    /**
     * Print message without writing logs.
     *
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     */
    public static function display($string, $foreground_color = 'white', $background_color = null, $escape = true)
    {
        self::write($string, $foreground_color, $background_color, false, $escape);
    }

    /**
     * Print break.
     *
     * @param int $eol
     */
    public static function writeBreak($eol = 1)
    {
        self::write(self::eol($eol));
    }

    /**
     * Print message and print eol.
     *
     * @param string $string
     * @param int $eol
     * @param string $foreground_color
     * @param null $background_color
     * @param null $log
     * @param bool $escape
     */
    public static function writeLine($string, $eol = 1, $foreground_color = 'white', $background_color = null, $log = null, $escape = true)
    {
        self::write($string . self::eol($eol), $foreground_color, $background_color, $log, $escape);
    }

    /**
     * Print message.
     *
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     * @param null $log
     */
    public static function write($string, $foreground_color = 'white', $background_color = null, $log = null, $escape = true)
    {
        $return_string = $string;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $foreground_color = null;
            $background_color = null;
        }
        if (isset($_REQUEST['log']) && $log === null) {
            $log = true;
        }
        if ($escape) {
            $return_string = self::escape($return_string);
        }
        $colored_string = '';
        if (isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . 'm';
        }
        if (isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . 'm';
        }
        $colored_string .= $return_string . "\033[0m";

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo $return_string;
        } else {
            echo $colored_string;
        }

        if ($log) {
            self::log($string, $foreground_color);
        }
    }

    /**
     * Read input.
     *
     * @param $string
     * @param string $foreground_color
     * @param null $background_color
     *
     * @return string
     */
    public static function read($string, $foreground_color = 'white', $background_color = null)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $foreground_color = null;
            $background_color = null;
        }
        $colored_string = '';
        if (isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . 'm';
        }
        if (isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . 'm';
        }
        $colored_string .= $string . "\033[0m";

        $read = null;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo Application::$NAME . ' > ' . trim($string) . ' ';
        } else {
            echo Application::$NAME . ' > ' . trim($colored_string) . ' ';
        }

        while (stream_select($in = array(STDIN), $out = array(), $oob = array(), 0)) {
            fgets(STDIN);
        }

        $read = chop(fgets(STDIN));

        return $read;
    }

    /**
     * Print code.
     *
     * @param $string
     * @param null $log
     */
    public static function code($string, $errors = array(), $log = false)
    {
        $code = $string;
        if (count($errors) > 0) {
            foreach ($errors as $pattern) {
                $escaped = self::escape($pattern['match']);
                $code = str_replace($pattern['match'], "\033[" . self::$foreground_colors['red'] . 'm' . $escaped . "\033[" . self::$foreground_colors['white'] . 'm', $code);
            }
        }
        $lines = explode("\n", $code);
        for ($i = 0; $i < count($lines); $i++) {
            if ($i != 0) {
                self::displayBreak();
            }
            self::display('  ' . str_pad((string)($i + 1), strlen((string)count($lines)), ' ', STR_PAD_LEFT) . ' | ', 'yellow');
            self::display($lines[$i], 'white', null, false);
        }
        if ($log) {
            self::log($string);
        }
    }

    /**
     * Write logs.
     *
     * @param $string
     */
    public static function log($string, $color = '')
    {
        $string = trim($string);
        if (!empty($string)) {
            $string = trim($string, '.');
            $string = str_replace(self::eol(1), ' ', $string);
            $string = preg_replace("/[\s]+/m", ' ', $string);
            $type = 'INFO';
            switch ($color) {
                case 'green':
                    $type = 'SUCCESS';
                    break;
                case 'yellow':
                    $type = 'WARNING';
                    break;
                case 'red':
                    $type = 'DANGER';
                    break;
            }
            $string = '[' . date('Y-m-d H:i:s') . '] [' . $type . '] ' . $string . PHP_EOL;
            file_put_contents(Application::$PATH_LOGS, $string, FILE_APPEND);
        }
    }

    /**
     * Escape colors string.
     *
     * @param $string
     *
     * @return string
     */
    public static function escape($string)
    {
        return mb_convert_encoding(preg_replace('/(e|\x1B|[[:cntrl:]]|\033)\[([0-9]{1,2}(;[0-9]{1,2})?)?[mGKc]/', '', $string), 'utf-8', 'auto');
    }

    /**
     * Print lists.
     */
    public static function helplist($type = null)
    {
        $list = '';
        if (empty($type) || $type == 'exploits') {
            $exploit_list = implode(self::eol(1) . '- ', array_keys(Definitions::$EXPLOITS));
            $list .= self::eol(1) . 'Exploits:' . self::eol(1) . "- $exploit_list";
        }
        if (empty($type)) {
            $list .= self::eol(1);
        }
        if (empty($type) || $type == 'functions') {
            $functions_list = implode(self::eol(1) . '- ', Definitions::$FUNCTIONS);
            $list .= self::eol(1) . 'Functions:' . self::eol(1) . "- $functions_list";
        }
        self::displayTitle(trim($type . ' List'), 'black', 'cyan');
        self::displayLine($list, 2);
        die();
    }

    /**
     * Print Helper.
     */
    public static function helper()
    {
        self::displayTitle('Help', 'black', 'cyan');
        $dir = __DIR__;
        $help = <<<EOD

Arguments:
<path>                       - Define the path to scan (default: current directory)
                               ($dir)

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
          php -d disable_functions='' scanner --agile --only-exploits
          php -d disable_functions='' scanner --exploits="double_var2" --functions="eval, str_replace"
EOD;
        self::displayLine($help . self::eol(2) . Application::$ARGV->usage(), 2);
        die();
    }
}