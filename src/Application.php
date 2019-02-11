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
 * Class Application
 * @package marcocesarato\amwscan
 */
class Application {

	public static $NAME = "amwscan";
	public static $VERSION = "0.4.0.35";
	public static $ROOT = "./";
	public static $PATH_QUARANTINE = "/quarantine/";
	public static $PATH_LOGS = "/scanner.log";
	public static $PATH_WHITELIST = "/scanner_whitelist.csv";
	public static $PATH_LOGS_INFECTED = "/scanner_infected.log";

	public static $SCAN_PATH = "./";
	public static $SCAN_EXTENSIONS = array(
		'php',
		'php3',
		'ph3',
		'php4',
		'ph4',
		'php5',
		'ph5',
		'php7',
		'ph7',
		'phtm',
		'phtml',
		'ico'
	);
	public static $ARGV = array();
	public static $WHITELIST = array();

	// Definitions
	public static $FUNCTIONS = array();
	public static $EXPLOITS = array();

	// Summaries
	public static $summary_scanned = 0;
	public static $summary_detected = 0;
	public static $summary_removed = array();
	public static $summary_ignored = array();
	public static $summary_edited = array();
	public static $summary_quarantine = array();
	public static $summary_whitelist = array();

	// Default exploits definitions
	public static $DEFAULT_EXPLOITS = array(
		"eval_chr"                => '/chr[\s\r\n]*\([\s\r\n]*101[\s\r\n]*\)[\s\r\n]*\.[\s\r\n]*chr[\s\r\n]*\([\s\r\n]*118[\s\r\n]*\)[\s\r\n]*\.[\s\r\n]*chr[\s\r\n]*\([\s\r\n]*97[\s\r\n]*\)[\s\r\n]*\.[\s\r\n]*chr[\s\r\n]*\([\s\r\n]*108[\s\r\n]*\)/i',
		"eval_preg"               => '/(preg_replace(_callback)?|mb_ereg_replace|preg_filter)\s*\(.+(\/|\\\\x2f)(e|\\\\x65)[\\\'\"].*?(?=\))\)/i',
		"eval_base64"             => '/eval[\s\r\n]*\([\s\r\n]*base64_decode[\s\r\n]*\((?<=\().*?(?=\))\)/i',
		"eval_comment"            => '/(eval|preg_replace|system|assert|passthru|(pcntl_)?exec|shell_exec|call_user_func(_array)?)\/\*[^\*]*\*\/\((?<=\().*?(?=\))\)/',
		"align"                   => '/(\$\w+=[^;]*)*;\$\w+=@?\$\w+\((?<=\().*?(?=\))\)/si',
		"b374k"                   => '/(\\\'|\")ev(\\\'|\")\.(\\\'|\")al(\\\'|\")\.(\\\'|\")\(\"\?>/i', // b374k shell
		"weevely3"                => '/\$\w=\$[a-zA-Z]\(\'\',\$\w\);\$\w\(\);/i', // weevely3 launcher
		"c99_launcher"            => '/;\$\w+\(\$\w+(,\s?\$\w+)+\);/i', // http://bartblaze.blogspot.fr/2015/03/c99shell-not-dead.html
		"too_many_chr"            => '/(chr\([\d]+\)\.){8}/i', // concatenation of more than eight `chr()`
		"concat"                  => '/(\$[\w\[\]\\\'\"]+\\.[\n\r]*){10}/i', // concatenation of vars array
		"concat_vars_with_spaces" => '/(\$([a-zA-Z0-9]+)[\s\r\n]*\.[\s\r\n]*){6}/', // concatenation of more than 6 words, with spaces
		"concat_vars_array"       => '/(\$([a-zA-Z0-9]+)(\{|\[)([0-9]+)(\}|\])[\s\r\n]*\.[\s\r\n]*){6}.*?(?=\})\}/i', // concatenation of more than 6 words, with spaces
		"var_as_func"             => '/\$_(GET|POST|COOKIE|REQUEST|SERVER)[\s\r\n]*\[[^\]]+\][\s\r\n]*\((?<=\().*?(?=\))\)/i',
		"global_var_string"       => '/\$\{[\s\r\n]*(\\\'|\")_(GET|POST|COOKIE|REQUEST|SERVER)(\\\'|\")[\s\r\n]*\}/i',
		"extract_global"          => '/extract\([\s\r\n]*\$_(GET|POST|COOKIE|REQUEST|SERVER).*?(?=\))\)/i',
		"escaped_path"            => '/(\\x[0-9abcdef]{2}[a-z0-9.-\/]{1,4}){4,}/i',
		"include_icon"            => '/include\(?[\s\r\n]*(\"|\\\')(.*?)(\.|\\056\\046\\2E)(\i|\\\\151|\\x69|\\105)(c|\\143\\099\\x63)(o|\\157\\111|\\x6f)(\"|\\\')\)?/mi',  // Icon inclusion
		"backdoor_code"           => '/eva1fYlbakBcVSir/i',
		"infected_comment"        => '/\/\*[a-z0-9]{5}\*\//i', // usually used to detect if a file is infected yet
		"hex_char"                => '/\\[Xx](5[Ff])/i',
		"hacked_by"               => '/hacked[\s\r\n]*by/i',
		"killall"                 => '/killall[\s\r\n]*\-9/i',
		"download_remote_code"    => '/echo\s+file_get_contents[\s\r\n]*\([\s\r\n]*base64_url_decode[\s\r\n]*\([\s\r\n]*@*\$_(GET|POST|SERVER|COOKIE|REQUEST).*?(?=\))\)/i',
		"globals_concat"          => '/\$GLOBALS\[[\s\r\n]*\$GLOBALS[\\\'[a-z0-9]{4,}\\\'\]/i',
		"globals_assign"          => '/\$GLOBALS\[\\\'[a-z0-9]{5,}\\\'\][\s\r\n]*=[\s\r\n]*\$[a-z]+\d+\[\d+\]\.\$[a-z]+\d+\[\d+\]\.\$[a-z]+\d+\[\d+\]\.\$[a-z]+\d+\[\d+\]\./i',
		/*"php_long"                => '/^.*<\?php.{800,}\?>.*$/i',*/
		//"base64_long"             => '/[\\\'\"][A-Za-z0-9+\/]{260,}={0,3}[\\\'\"]/',
		"clever_include"          => '/include[\s\r\n]*\([\s\r\n]*[^\.]+\.(png|jpe?g|gif|bmp|ico).*?(?=\))\)/i',
		"basedir_bypass"          => '/curl_init[\s\r\n]*\([\s\r\n]*[\"\\\']file:\/\/.*?(?=\))\)/i',
		"basedir_bypass2"         => '/file\:file\:\/\//i', // https://www.intelligentexploit.com/view-details.html?id=8719
		"non_printable"           => '/(function|return|base64_decode).{,256}[^\\x00-\\x1F\\x7F-\\xFF]{3}/i',
		"double_var"              => '/\${[\s\r\n]*\${.*?}(.*)?}/i',
		"double_var2"             => '/\${\$[0-9a-zA-z]+}/i',
		"global_save"             => '/\[\s\r\n]*=[\s\r\n]*\$GLOBALS[\s\r\n]*\;[\s\r\n]*\$[\s\r\n]*\{/i',
		"hex_var"                 => '/\$\{[\s\r\n]*(\\\'|\")\\\\x.*?(?=\})\}/i', // check for ${"\xFF"}, IonCube use this method ${"\x
		"register_function"       => '/register_[a-z]+_function[\s\r\n]*\([\s\r\n]*[\\\'\"][\s\r\n]*(eval|assert|passthru|exec|include|system|shell_exec|`).*?(?=\))\)/i',  // https://github.com/nbs-system/php-malware-finder/issues/41
		"safemode_bypass"         => '/\x00\/\.\.\/|LD_PRELOAD/i',
		"ioncube_loader"          => '/IonCube\_loader/i',
		"nano"                    => '/\$[a-z0-9-_]+\[[^]]+\]\((?<=\().*?(?=\))\)/', //https://github.com/UltimateHackers/nano
		"ninja"                   => '/base64_decode[^;]+getallheaders/',
		"execution"               => '/\b(eval|assert|passthru|exec|include|system|pcntl_exec|shell_exec|base64_decode|`|array_map|ob_start|call_user_func(_array)?)\s*\(\s*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\?\$_(GET|REQUEST|POST|COOKIE|SERVER)).*?(?=\))\)/',  // function that takes a callback as 1st parameter
		"execution2"              => '/\b(array_filter|array_reduce|array_walk(_recursive)?|array_walk|assert_options|uasort|uksort|usort|preg_replace_callback|iterator_apply)\s*\(\s*[^,]+,\s*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\?\$_(GET|REQUEST|POST|COOKIE|SERVER)).*?(?=\))\)/',  // functions that takes a callback as 2nd parameter
		"execution3"              => '/\b(array_(diff|intersect)_u(key|assoc)|array_udiff)\s*\(\s*([^,]+\s*,?)+\s*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\?\$_(GET|REQUEST|POST|COOKIE|SERVER))\s*\[[^]]+\]\s*\)+\s*;/',  // functions that takes a callback as 2nd parameter
		"shellshock"              => "/\(\)\s*{\s*[a-z:]\s*;\s*}\s*;/",
		"silenced_eval"           => '/@eval\s*\((?<=\().*?(?=\))\)/',
		"various"                 => '/\<\!\-\-\#exec\s*cmd\=/i', //http://www.w3.org/Jigsaw/Doc/User/SSI.html#exec
		"htaccess_handler"        => '/SetHandler[\s\r\n]*application\/x\-httpd\-php/i',
		"htaccess_type"           => '/AddType\s+application\/x-httpd-(php|cgi)/i',
		"file_prepend"            => '/php_value\s*auto_prepend_file/i',
		"iis_com"                 => '/IIS\:\/\/localhost\/w3svc/i',
		"reversed"                => '/(noitcnuf\_etaerc|metsys|urhtssap|edulcni|etucexe\_llehs|ecalper\_rts|ecalper_rts)/i',
		"rawurlendcode_rot13"     => '/rawurldecode[\s\r\n]*\(str_rot13[\s\r\n]*\((?<=\().*?(?=\))\)/i',
		"serialize_phpversion"    => '/\@serialize[\s\r\n]*\([\s\r\n]*(Array\(|\[)(\\\'|\")php(\\\'|\")[\s\r\n]*\=\>[\s\r\n]*\@phpversion[\s\r\n]*\((?<=\().*?(?=\))\)/si',
		//"disable_magic_quotes"    => '/set_magic_quotes_runtime\s*\(\s*0\s*\)/',
		"md5_create_function"     => '/\$md5\s*=\s*.*create_function\s*\(.*?\);\s*\$.*?\)\s*;/si',
		"god_mode"                => '/\/\*god_mode_on\*\/eval\(base64_decode\([\"\\\'][^\"\\\']{255,}[\"\\\']\)\);\s*\/\*god_mode_off\*\//si',
		"wordpress_filter"        => '/\$md5\s*=\s*[\"|\\\']\w+[\"|\\\'];\s*\$wp_salt\s*=\s*[\w\(\),\"\\\'\;$]+\s*\$wp_add_filter\s*=\s*create_function\(.*?\);\s*\$wp_add_filter\(.*?\);/si',
		"password_protection_md5" => '/md5\s*\(\s*\$_(GET|REQUEST|POST|COOKIE|SERVER)[^)]+\)\s*===?\s*[\\\'\"][0-9a-f]{32}[\\\'\"]/si',
		"password_protection_sha" => '/sha1\s*\(\s*\$_(GET|REQUEST|POST|COOKIE|SERVER)[^)]+\)\s*===?\s*[\\\'\"][0-9a-f]{40}[\\\'\"]/si',
	);

	// Default functions definitions
	public static $DEFAULT_FUNCTIONS = array(
		"il_exec",
		"shell_exec",
		"eval",
		//"system",
		"create_function",
		/*"str_rot13",
		"exec",
		"assert",
		"syslog",
		"passthru",
		"dl",
		"define_syslog_variables",
		"debugger_off",
		"debugger_on",
		"stream_select",
		"parse_ini_file",
		"show_source",
		"symlink",
		"popen",*/
		"posix_kill",/*
        "posix_getpwuid",
        "posix_mkfifo",
        "posix_setpgid",
        "posix_setsid",
        "posix_setuid",
        "posix_uname",*/
		"proc_close",
		"proc_get_status",
		"proc_nice",
		"proc_open",/*
        "proc_terminate",
        "ini_alter",
        "ini_get_all",
        "ini_restore",
        "parse_ini_file",*/
		"inject_code",
		"apache_child_terminate",
		//"apache_setenv",
		"apache_note",
		"define_syslog_variables",/*
        "escapeshellarg",
        "escapeshellcmd",
        "ob_start",*/
	);

	/**
	 * Application constructor.
	 */
	public function __construct() {

	}

	/**
	 * Initialize
	 */
	private function init(){
		if(self::$ROOT == "./") {
			self::$ROOT = dirname(__FILE__);
		}

		if(self::$SCAN_PATH == "./") {
			self::$SCAN_PATH = dirname(__FILE__);
		}
		self::$PATH_QUARANTINE    = self::$ROOT . self::$PATH_QUARANTINE;
		self::$PATH_LOGS          = self::$ROOT . self::$PATH_LOGS;
		self::$PATH_WHITELIST     = self::$ROOT . self::$PATH_WHITELIST;
		self::$PATH_LOGS_INFECTED = self::$ROOT . self::$PATH_LOGS_INFECTED;

		// Prepare whitelist
		self::$WHITELIST = CSV::read(self::$PATH_WHITELIST);

		// Remove logs
		@unlink(self::$PATH_LOGS);
	}

	/**
	 * Run application
	 */
	public function run() {

		Console::header();

		$this->init();
		$this->arguments();

		// Start scanning
		Console::display("Start scanning..." . PHP_EOL);

		Console::write("Scan date: " . date("d-m-Y H:i:s") . PHP_EOL);
		Console::write("Scanning " . self::$SCAN_PATH . Console::$PHP_EOL2);

		// Initialize modes
		$this->modes();

		Console::write(PHP_EOL . "Mapping files..." . PHP_EOL);

		try {
			$iterator = $this->mapping();
			// Counting files
			$files_count = iterator_count($iterator);
		} catch(\Exception $e) {
			Console::write(PHP_EOL);
			Console::write($e->getMessage(), 'red');
			Console::write(PHP_EOL);
			die();
		}

		Console::write("Found " . $files_count . " files" . Console::$PHP_EOL2);
		Console::write("Checking files..." . Console::$PHP_EOL2);
		Console::progress(0, $files_count);

		// Scan all files
		$this->scan($iterator);

		Console::write(Console::$PHP_EOL2);
		Console::write("Scan finished!", 'green');
		Console::write(Console::$PHP_EOL3);

		// Print summary
		$this->summary();
	}

	/**
	 * Initialize application arguments
	 */
	private function arguments() {

		// Define Arguments
		self::$ARGV = new Argv();
		self::$ARGV->addFlag("agile", ["alias" => "-a", "default" => false]);
		self::$ARGV->addFlag("help", ["alias" => "-h", "default" => false]);
		self::$ARGV->addFlag("log", ["alias" => "-l", "default" => false]);
		self::$ARGV->addFlag("scan", ["alias" => "-s", "default" => false]);
		self::$ARGV->addFlag("exploits", ["default" => false, "has_value" => true]);
		self::$ARGV->addFlag("functions", ["default" => false, "has_value" => true]);
		self::$ARGV->addFlag("only-exploits", ["alias" => "-e", "default" => false]);
		self::$ARGV->addFlag("only-functions", ["alias" => "-f", "default" => false]);
		self::$ARGV->addFlag("whitelist-only-path", ["default" => false]);
		self::$ARGV->addArgument("path", ["var_args" => true, "default" => ""]);
		self::$ARGV->parse();

		// Help
		if(isset(self::$ARGV['help']) && self::$ARGV['help']) {
			Console::helper();
		}

		// Check if only scanner
		if(isset(self::$ARGV['scan']) && self::$ARGV['scan']) {
			$_REQUEST['scan'] = true;
		} else {
			$_REQUEST['scan'] = false;
		}

		// Write logs
		if(isset(self::$ARGV['log']) && self::$ARGV['log']) {
			$_REQUEST['log'] = true;
		} else {
			$_REQUEST['log'] = false;
		}

		// Check on whitelist only file path and not line number
		if(isset(self::$ARGV['whitelist-only-path']) && self::$ARGV['whitelist-only-path']) {
			$_REQUEST['whitelist-only-path'] = true;
		} else {
			$_REQUEST['whitelist-only-path'] = false;
		}

		// Check Filter exploits
		if(isset(self::$ARGV['exploits']) && self::$ARGV['exploits']) {
			if(is_string(self::$ARGV['exploits'])) {
				$filtered = str_replace(array("\n", "\r", "\t", " "), "", self::$ARGV['exploits']);
				$filtered = @explode(',', $filtered);
				if(!empty($filtered) && count($filtered) > 0) {
					foreach(self::$DEFAULT_EXPLOITS as $key => $value) {
						if(in_array($key, $filtered)) {
							self::$EXPLOITS[$key] = $value;
						}
					}
					if(!empty(self::$EXPLOITS) && count(self::$EXPLOITS) > 0) {
						Console::write("Exploit to search: " . implode(', ', array_keys(self::$EXPLOITS)) . PHP_EOL);
					} else {
						self::$EXPLOITS = array();
					}
				}
			}
		}

		// Check if exploit mode is enabled
		if(isset(self::$ARGV['only-exploits']) && self::$ARGV['only-exploits']) {
			$_REQUEST['exploits'] = true;
		} else {
			$_REQUEST['exploits'] = false;
		}

		// Check functions to search
		if(isset(self::$ARGV['functions']) && self::$ARGV['functions']) {
			if(is_string(self::$ARGV['functions'])) {
				self::$FUNCTIONS = str_replace(array("\n", "\r", "\t", " "), "", self::$ARGV['functions']);
				self::$FUNCTIONS = @explode(',', self::$FUNCTIONS);
				if(!empty(self::$FUNCTIONS) && count(self::$FUNCTIONS) > 0) {
					Console::write("Functions to search: " . implode(', ', self::$FUNCTIONS) . PHP_EOL);
				} else {
					$FUNCTIONS = array();
				}
			}
		}

		// Check if functions mode is enabled
		if(isset(self::$ARGV['only-functions']) && self::$ARGV['only-functions']) {
			$_REQUEST['functions'] = true;
		} else {
			$_REQUEST['functions'] = false;
		}

		// Check if agile scan is enabled
		if(isset(self::$ARGV['agile']) && self::$ARGV['agile']) {
			self::$EXPLOITS                            = self::$DEFAULT_EXPLOITS;
			$_REQUEST['exploits']                      = true;
			self::$EXPLOITS['execution']               = '/\b(eval|assert|passthru|exec|include|system|pcntl_exec|shell_exec|`|array_map|ob_start|call_user_func(_array)?)\s*\(\s*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\?\$_(GET|REQUEST|POST|COOKIE|SERVER)).*?(?=\))\)/';
			self::$EXPLOITS['concat_vars_with_spaces'] = '/(\$([a-zA-Z0-9]+)[\s\r\n]*\.[\s\r\n]*){8}/';  // concatenation of more than 8 words, with spaces
			self::$EXPLOITS['concat_vars_array']       = '/(\$([a-zA-Z0-9]+)(\{|\[)([0-9]+)(\}|\])[\s\r\n]*\.[\s\r\n]*){8}.*?(?=\})\}/i'; // concatenation of more than 8 words, with spaces
			unset(self::$EXPLOITS['nano'], self::$EXPLOITS['double_var2']);
		}

		// Check if logs and scan at the same time
		if(isset(self::$ARGV['log']) && self::$ARGV['log'] && isset(self::$ARGV['scan']) && self::$ARGV['scan']) {
			unset($_REQUEST['log']);
		}

		// Check for path or functions as first argument
		if(!empty(self::$ARGV->arg(0))) {
			$path = trim(self::$ARGV->arg(0));
			if(file_exists(realpath($path))) {
				self::$SCAN_PATH = realpath($path);
			}
		}

		// Check path
		if(!is_dir(self::$SCAN_PATH)) {
			self::$SCAN_PATH = pathinfo(self::$SCAN_PATH, PATHINFO_DIRNAME);
		}

	}

	/**
	 * Init application modes
	 */
	private function modes() {

		if($_REQUEST['functions'] && $_REQUEST['exploits']) {
			Console::write('Can\'t be set both flags --only-functions and --only-functions together!');
			die(Console::$PHP_EOL2);
		}

		// Malware Definitions
		if($_REQUEST['functions'] || !$_REQUEST['exploits'] && empty(self::$FUNCTIONS)) {
			// Functions to search
			self::$FUNCTIONS = self::$DEFAULT_FUNCTIONS;
		} else if($_REQUEST['exploits']) {
			self::$FUNCTIONS = array();
			if(!self::$ARGV['agile']) {
				Console::write("Exploits mode enabled" . PHP_EOL);
			}
		} else {
			Console::write("No functions to search" . PHP_EOL);
		}

		// Exploits to search
		if(!$_REQUEST['functions'] && empty(self::$EXPLOITS)) {
			self::$EXPLOITS = self::$DEFAULT_EXPLOITS;
		}

		if(self::$ARGV['agile']) {
			Console::write("Agile mode enabled" . PHP_EOL);
		}

		if($_REQUEST['scan']) {
			Console::write("Scan mode enabled" . PHP_EOL);
		}

		if($_REQUEST['functions']) {
			self::$EXPLOITS = array();
		}

		if($_REQUEST['exploits']) {
			self::$FUNCTIONS = array();
		}
	}

	/**
	 * Map files
	 * @return \CallbackFilterIterator
	 */
	public function mapping() {
		// Mapping files
		$directory = new \RecursiveDirectoryIterator(self::$SCAN_PATH);
		$files     = new \RecursiveIteratorIterator($directory);
		$iterator  = new \CallbackFilterIterator($files, function($cur, $key, $iter) {
			return ($cur->isFile() && in_array($cur->getExtension(), Application::$SCAN_EXTENSIONS));
		});

		return $iterator;
	}

	/**
	 * Detect infected favicon
	 * @param $file
	 * @return bool
	 */
	public static function isInfectedFavicon($file) {
		// Case favicon_[random chars].ico
		$_FILE_NAME      = $file->getFilename();
		$_FILE_EXTENSION = $file->getExtension();

		return (((strpos($_FILE_NAME, 'favicon_') === 0) && ($_FILE_EXTENSION === 'ico') && (strlen($_FILE_NAME) > 12)) || preg_match('/^\.[\w]+\.ico/i', trim($_FILE_NAME)));
	}

	/**
	 * Scan file
	 * @param $info
	 * @return array
	 */
	public function scanFile($info) {

		$_FILE_PATH = $info->getPathname();

		$is_favicon    = self::isInfectedFavicon($info);
		$pattern_found = array();

		$fc          = file_get_contents($_FILE_PATH);
		$fc_clean    = php_strip_whitespace($_FILE_PATH);
		$fc_filtered = $this->filterCode($fc_clean);

		// Scan exploits
		foreach(self::$EXPLOITS as $key => $pattern) {
			$last_match        = null;
			$match_description = null;
			if(@preg_match($pattern, $fc, $match, PREG_OFFSET_CAPTURE) || // Original
			   @preg_match($pattern, $fc_clean, $match, PREG_OFFSET_CAPTURE) || // No comments
			   @preg_match($pattern, $fc_filtered, $match, PREG_OFFSET_CAPTURE)) { // Filtered
				$last_match        = $match[0][0];
				$match_description = $key . "\n => " . $last_match;
			}
			if(!empty($last_match) && @preg_match('/' . preg_quote($last_match, '/') . '/i', $fc, $match, PREG_OFFSET_CAPTURE)) {
				$lineNumber        = count(explode("\n", substr($fc, 0, $match[0][1])));
				$match_description = $key . " [line " . $lineNumber . "]\n => " . $last_match;
			}
			if(!empty($match_description)) {
				$pattern_found[$match_description] = $pattern;
			}
		}
		unset($last_match, $match_description, $lineNumber, $match);

		// Scan php commands
		foreach(self::$FUNCTIONS as $_func) {
			$last_match        = null;
			$match_description = null;
			$func              = preg_quote(trim($_func), '/');
			// Search on filtered content
			$regex_pattern        = "/(?:^|[^a-zA-Z0-9_]+)(" . $func . "[\s\r\n]*\((?<=\().*?(?=\))\))/si";
			$regex_pattern_base64 = "/" . base64_encode($_func) . "/s";
			if(@preg_match($regex_pattern, $fc_filtered, $match, PREG_OFFSET_CAPTURE) ||
			   @preg_match($regex_pattern, $fc_clean, $match, PREG_OFFSET_CAPTURE) ||
			   @preg_match($regex_pattern_base64, $fc_filtered, $match, PREG_OFFSET_CAPTURE) ||
			   @preg_match($regex_pattern_base64, $fc_clean, $match, PREG_OFFSET_CAPTURE)) {
				$last_match        = explode($_func, $match[0][0]);
				$last_match        = $_func . $last_match[1];
				$match_description = $func . "\n => " . $last_match;
			}
			if(!empty($last_match) && @preg_match('/' . preg_quote($last_match, '/') . '/', $fc, $match, PREG_OFFSET_CAPTURE)) {
				$lineNumber        = count(explode("\n", substr($fc, 0, $match[0][1])));
				$match_description = $func . " [line " . $lineNumber . "]\n => " . $last_match;
			}
			if(!empty($match_description)) {
				$pattern_found[$match_description] = $regex_pattern;
			}
			/*$field = bin2hex($pattern);
			$field = chunk_split($field, 2, '\x');
			$field = '\x' . substr($field, 0, -2);
			$regex_pattern = "/(" . preg_quote($field) . ")/i";
			if (@preg_match($regex_pattern, $contents, $match, PREG_OFFSET_CAPTURE)) {
				$found = true;
				$lineNumber = count(explode("\n", substr($fc, 0, $match[0][1])));
				$pattern_found[$pattern . " [line " . $lineNumber . "]"] = $regex_pattern;
			}*/
			unset($last_match, $match_description, $lineNumber, $regex_pattern, $regex_pattern_base64, $match);
		}
		unset($fc_filtered, $fc_clean);

		if($is_favicon) {
			$pattern_found['infected_icon'] = '';
		}

		return $pattern_found;
	}

	/**
	 * Filter clean and improve file content
	 * @param $fc
	 * @return string
	 */
	private function filterCode($fc) {
		$fc_filtered = preg_replace("/<\?php(.*?)(?!\B\"[^\"]*)\?>(?![^\"]*\"\B)/si", "$1", $fc); // Only php code
		$fc_filtered = preg_replace("/(\\'|\\\")[\s\r\n]*\.[\s\r\n]*('|\")/si", "", $fc_filtered); // Remove "ev"."al"
		$fc_filtered = preg_replace("/([\s]+)/i", " ", $fc_filtered); // Remove multiple spaces

		// Convert hex
		$fc_filtered = preg_replace_callback('/\\\\x[A-Fa-f0-9]{2}/si', function($match) {
			return @hex2bin(str_replace('\\x', '', $match));
		}, $fc_filtered);

		// Convert dec and oct
		$fc_filtered = preg_replace_callback('/\\\\[0-9]{3}/si', function($match) {
			return chr(intval($match));
		}, $fc_filtered);

		// Decode strings
		$decoders        = array(
			'str_rot13',
			'gzinflate',
			'base64_decode',
			'rawurldecode',
			'gzuncompress',
			'strrev',
			'convert_uudecode',
			'urldecode'
		);
		$pattern_decoder = array();
		foreach($decoders as $decoder) {
			$pattern_decoder[] = preg_quote($decoder, '/');
		}
		$last_match     = null;
		$recursive_loop = true;
		do {
			// Check decode functions
			$regex_pattern = '/((' . implode($pattern_decoder, '|') . ')[\s\r\n]*\((([^()]|(?R))*)?\))/si';
			preg_match($regex_pattern, $fc_filtered, $match);
			// Get value inside function
			if($recursive_loop && preg_match('/(\((?:\"|\\\')(([^\\\'\"]|(?R))*?)(?:\"|\\\')\))/si', $match[0], $encoded_match)) {
				$value          = $encoded_match[3];
				$last_match     = $match;
				$decoders_found = array_reverse(explode('(', $match[0]));
				foreach($decoders_found as $decoder) {
					if(in_array($decoder, $decoders)) {
						if(is_string($value) && !empty($value)) {
							$value = $decoder($value); // Decode
						}
					}
				}
				if(is_string($value) && !empty($value)) {
					$value       = str_replace('"', "'", $value);
					$value       = '"' . $value . '"';
					$fc_filtered = str_replace($match[0], $value, $fc_filtered);
				} else {
					$recursive_loop = false;
				}
			} else {
				$recursive_loop = false;
			}
		} while(!empty($match[0]) && $recursive_loop);
		unset($last_match, $recursive_loop, $value, $match, $decoders_found, $decoders, $pattern_decoder, $encoded_match);

		return $fc_filtered;
	}

	/**
	 * Run scanner
	 * @param $iterator
	 */
	private function scan($iterator) {

		$files_count = iterator_count($iterator);

		// Scanning
		foreach($iterator as $info) {

			Console::progress(self::$summary_scanned, $files_count);

			$_FILE_PATH      = $info->getPathname();
			$_FILE_EXTENSION = $info->getExtension();

			$is_favicon = self::isInfectedFavicon($info);

			if((in_array($_FILE_EXTENSION, self::$SCAN_EXTENSIONS) &&
			    (!file_exists(self::$PATH_QUARANTINE) || strpos(realpath($_FILE_PATH), realpath(self::$PATH_QUARANTINE)) === false)
				   /*&& (strpos($filename, '-') === FALSE)*/)
			   || $is_favicon) {

				$pattern_found = $this->scanFile($info);

				// Check whitelist
				$pattern_found = array_unique($pattern_found);
				$in_whitelist  = 0;
				foreach(self::$WHITELIST as $item) {
					foreach($pattern_found as $key => $pattern) {
						$exploit           = preg_replace("/^(\S+) \[line [0-9]+\].*/si", "$1", trim($key));
						$exploit_whitelist = preg_replace("/^(\S+).*/si", "$1", trim($item[1]));
						$lineNumber        = preg_replace("/^\S+ \[line ([0-9]+)\].*/si", "$1", trim($key));
						if(realpath($_FILE_PATH) == realpath($item[0]) && $exploit == $exploit_whitelist &&
						   ($_REQUEST['whitelist-only-path'] || !$_REQUEST['whitelist-only-path'] && $lineNumber == $item[2])) {
							$in_whitelist ++;
						}
					}
				}

				// Scan finished

				self::$summary_scanned ++;
				usleep(10);

				if(realpath($_FILE_PATH) != realpath(__FILE__) && ($is_favicon || !empty($pattern_found)) && ($in_whitelist === 0 || $in_whitelist != count($pattern_found))) {
					self::$summary_detected ++;
					if($_REQUEST['scan']) {

						// Scan mode only

						self::$summary_ignored[] = $_FILE_PATH;
						continue;
					} else {

						// Scan with code check

						$_WHILE       = true;
						$last_command = '0';
						Console::display(Console::$PHP_EOL2);
						Console::write(PHP_EOL);
						Console::write("PROBABLE MALWARE FOUND!", 'red');

						while($_WHILE) {
							$fc            = file_get_contents($_FILE_PATH);
							$preview_lines = explode(PHP_EOL, trim($fc));
							$preview       = implode(PHP_EOL, array_slice($preview_lines, 0, 1000));
							if(!in_array($last_command, array('4', '5', '7'))) {
								Console::write(PHP_EOL . "$_FILE_PATH", 'yellow');
								Console::write(Console::$PHP_EOL2);
								Console::write("========================================== PREVIEW ===========================================", 'white', 'red');
								Console::write(Console::$PHP_EOL2);
								Console::code($preview, $pattern_found);
								if(count($preview_lines) > 1000) {
									Console::write(Console::$PHP_EOL2);
									Console::write('  [ ' . (count($preview_lines) - 1000) . ' More lines ]');
								}
								Console::write(Console::$PHP_EOL2);
								Console::write("==============================================================================================", 'white', 'red');
							}
							Console::write(Console::$PHP_EOL2);
							Console::write("File path: " . $_FILE_PATH, 'yellow');
							Console::write("\n");
							Console::write("Exploit: " . PHP_EOL . implode(PHP_EOL, array_keys($pattern_found)), 'red');
							Console::display(Console::$PHP_EOL2);
							Console::display("OPTIONS:" . Console::$PHP_EOL2);
							Console::display("    [1] Delete file" . PHP_EOL);
							Console::display("    [2] Move to quarantine" . PHP_EOL);
							Console::display("    [3] Try remove evil code" . PHP_EOL);
							Console::display("    [4] Open with vim" . PHP_EOL);
							Console::display("    [5] Open with nano" . PHP_EOL);
							Console::display("    [6] Add to whitelist" . PHP_EOL);
							Console::display("    [7] Show source" . PHP_EOL);
							Console::display("    [-] Ignore" . Console::$PHP_EOL2);
							$confirmation = Console::read("What is your choice? ", "purple");
							Console::display(PHP_EOL);

							$last_command = $confirmation;
							unset($preview_lines, $preview);

							if(in_array($confirmation, array('1'))) {

								// Remove file

								Console::write("File path: " . $_FILE_PATH . PHP_EOL, 'yellow');
								$confirm2 = Console::read("Want delete this file [y|N]? ", "purple");
								Console::display(PHP_EOL);
								if($confirm2 == 'y') {
									unlink($_FILE_PATH);
									self::$summary_removed[] = $_FILE_PATH;
									Console::write("File '$_FILE_PATH' removed!" . Console::$PHP_EOL2, 'green');
									$_WHILE = false;
								}
							} else if(in_array($confirmation, array('2'))) {

								// Move to quarantine

								$quarantine = self::$PATH_QUARANTINE . str_replace(realpath(__DIR__), '', $_FILE_PATH);

								if(!is_dir(dirname($quarantine))) {
									mkdir(dirname($quarantine), 0755, true);
								}
								rename($_FILE_PATH, $quarantine);
								self::$summary_quarantine[] = $quarantine;
								Console::write("File '$_FILE_PATH' moved to quarantine!" . Console::$PHP_EOL2, 'green');
								$_WHILE = false;
							} else if(in_array($confirmation, array('3')) && count($pattern_found) > 0) {

								// Remove evil code

								foreach($pattern_found as $pattern) {
									preg_match($pattern, $fc, $string_match);
									preg_match('/(<\?php)(.*?)(' . preg_quote($string_match[0], '/') . '\s*\;?)(.*?)((?!\B"[^"]*)\?>(?![^"]*"\B)|.*?$)/si', $fc, $match);
									if(!empty(trim($match[2])) || !empty(trim($match[4]))) {
										$fc = str_replace($match[0], $match[1] . $match[2] . $match[4] . $match[5], $fc);
									} else {
										$fc = str_replace($match[0], '', $fc);
									}
									$fc = preg_replace('/<\?php[\s\r\n]*\?\>/si', '', $fc);
								}
								Console::write(PHP_EOL);
								Console::write("========================================== SANITIZED ==========================================", 'black', 'green');
								Console::write(Console::$PHP_EOL2);
								Console::code($fc);
								Console::write(Console::$PHP_EOL2);
								Console::write("===============================================================================================", 'black', 'green');
								Console::display(Console::$PHP_EOL2);
								Console::display("File sanitized, now you must verify if has been fixed correctly." . Console::$PHP_EOL2, "yellow");
								$confirm2 = Console::read("Confirm and save [y|N]? ", "purple");
								Console::display(PHP_EOL);
								if($confirm2 == 'y') {
									Console::write("File '$_FILE_PATH' sanitized!" . Console::$PHP_EOL2, 'green');
									file_put_contents($_FILE_PATH, $fc);
									self::$summary_removed[] = $_FILE_PATH;
									$_WHILE                  = false;
								} else {
									self::$summary_ignored[] = $_FILE_PATH;
								}
							} else if(in_array($confirmation, array('4'))) {

								// Edit with vim

								$descriptors = array(
									array('file', '/dev/tty', 'r'),
									array('file', '/dev/tty', 'w'),
									array('file', '/dev/tty', 'w')
								);
								$process     = proc_open("vim '$_FILE_PATH'", $descriptors, $pipes);
								while(true) {
									if(proc_get_status($process)['running'] == false) {
										break;
									}
								}
								self::$summary_edited[] = $_FILE_PATH;
								Console::write("File '$_FILE_PATH' edited with vim!" . Console::$PHP_EOL2, 'green');
								self::$summary_removed[] = $_FILE_PATH;
							} else if(in_array($confirmation, array('5'))) {

								// Edit with nano

								$descriptors = array(
									array('file', '/dev/tty', 'r'),
									array('file', '/dev/tty', 'w'),
									array('file', '/dev/tty', 'w')
								);
								$process     = proc_open("nano -c '$_FILE_PATH'", $descriptors, $pipes);
								while(true) {
									if(proc_get_status($process)['running'] == false) {
										break;
									}
								}
								$summary_edited[] = $_FILE_PATH;
								Console::write("File '$_FILE_PATH' edited with nano!" . Console::$PHP_EOL2, 'green');
								self::$summary_removed[] = $_FILE_PATH;
							} else if(in_array($confirmation, array('6'))) {

								// Add to whitelist

								foreach($pattern_found as $key => $pattern) {
									$exploit           = preg_replace("/^(\S+) \[line [0-9]+\].*/si", "$1", $key);
									$lineNumber        = preg_replace("/^\S+ \[line ([0-9]+)\].*/si", "$1", $key);
									self::$WHITELIST[] = array(realpath($_FILE_PATH), $exploit, $lineNumber);
								}
								self::$WHITELIST = array_map("unserialize", array_unique(array_map("serialize", self::$WHITELIST)));
								if(CSV::write(self::$PATH_WHITELIST, self::$WHITELIST)) {
									self::$summary_whitelist[] = $_FILE_PATH;
									Console::write("Exploits of file '$_FILE_PATH' added to whitelist!" . Console::$PHP_EOL2, 'green');
									$_WHILE = false;
								} else {
									Console::write("Exploits of file '$_FILE_PATH' failed adding file to whitelist! Check write permission of '" . self::$PATH_WHITELIST . "' file!" . Console::$PHP_EOL2, 'red');
								}
							} else if(in_array($confirmation, array('7'))) {

								// Show source code

								Console::write(PHP_EOL . "$_FILE_PATH", 'yellow');
								Console::write(Console::$PHP_EOL2);
								Console::write("=========================================== SOURCE ===========================================", 'white', 'red');
								Console::write(Console::$PHP_EOL2);
								Console::code($fc, $pattern_found);
								Console::write(Console::$PHP_EOL2);
								Console::write("==============================================================================================", 'white', 'red');
							} else {

								// None

								Console::write("File '$_FILE_PATH' skipped!" . Console::$PHP_EOL2, 'green');
								self::$summary_ignored[] = $_FILE_PATH;
								$_WHILE                  = false;
							}

							Console::write(PHP_EOL);
						}
						unset($fc);
					}
				}
			}
		}
	}

	/**
	 * Print summary
	 */
	private function summary() {
		// Statistics
		Console::write("                SUMMARY                ", 'black', 'cyan');
		Console::write(Console::$PHP_EOL2);
		Console::write("Files scanned: " . self::$summary_scanned . PHP_EOL);
		if(!$_REQUEST['scan']) {
			self::$summary_ignored = array_unique(self::$summary_ignored);
			self::$summary_edited  = array_unique(self::$summary_edited);
			Console::write("Files edited: " . count(self::$summary_edited) . PHP_EOL);
			Console::write("Files quarantined: " . count(self::$summary_quarantine) . PHP_EOL);
			Console::write("Files whitelisted: " . count(self::$summary_whitelist) . PHP_EOL);
			Console::write("Files ignored: " . count(self::$summary_ignored) . Console::$PHP_EOL2);
		}
		Console::write("Malware detected: " . self::$summary_detected . PHP_EOL);
		if(!$_REQUEST['scan']) {
			Console::write("Malware removed: " . count(self::$summary_removed) . PHP_EOL);
		}

		if($_REQUEST['scan']) {
			Console::write(PHP_EOL . "Files infected: '" . __PATH_LOGS_INFECTED__ . "'" . PHP_EOL, 'red');
			file_put_contents(__PATH_LOGS_INFECTED__, "Log date: " . date("d-m-Y H:i:s") . PHP_EOL . implode(PHP_EOL, self::$summary_ignored));
			Console::write(Console::$PHP_EOL2);
		} else {
			if(count(self::$summary_removed) > 0) {
				Console::write(PHP_EOL . "Files removed:" . PHP_EOL, 'red');
				foreach(self::$summary_removed as $un) {
					Console::write($un . PHP_EOL);
				}
			}
			if(count(self::$summary_edited) > 0) {
				Console::write(PHP_EOL . "Files edited:" . PHP_EOL, 'green');
				foreach(self::$summary_edited as $un) {
					Console::write($un . PHP_EOL);
				}
			}
			if(count(self::$summary_quarantine) > 0) {
				Console::write(PHP_EOL . "Files quarantined:" . PHP_EOL, 'yellow');
				foreach(self::$summary_ignored as $un) {
					Console::write($un . PHP_EOL);
				}
			}
			if(count(self::$summary_whitelist) > 0) {
				Console::write(PHP_EOL . "Files whitelisted:" . PHP_EOL, 'cyan');
				foreach(self::$summary_whitelist as $un) {
					Console::write($un . PHP_EOL);
				}
			}
			if(count(self::$summary_ignored) > 0) {
				Console::write(PHP_EOL . "Files ignored:" . PHP_EOL, 'cyan');
				foreach(self::$summary_ignored as $un) {
					Console::write($un . PHP_EOL);
				}
			}
			Console::write(Console::$PHP_EOL2);
		}
	}
}