<?php

/**
 * PHP Antimalware Scanner.
 *
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 *
 * @see https://github.com/marcocesarato/PHP-Antimalware-Scanner
 */

namespace AMWScan;

use AMWScan\Console\Argv;
use AMWScan\Console\CLI;
use AMWScan\Templates\Report;
use ArrayIterator;
use ArrayObject;
use CallbackFilterIterator;
use Exception;
use LimitIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/**
 * Class Application.
 */
class Scanner
{
    /**
     * App name.
     *
     * @var string
     */
    public static $name = 'AMWScan';

    /**
     * App full name.
     *
     * @var string
     */
    public static $fullname = 'PHP Antimalware Scanner';

    /**
     * App description.
     *
     * @var string
     */
    public static $description = 'A tool to scan PHP files and analyze your project to find any malicious code inside it.';

    /**
     * App author.
     *
     * @var string
     */
    public static $author = 'Marco Cesarato';

    /**
     * Version.
     *
     * @var string
     */
    public static $version = '0.10.4';

    /**
     * Repo url.
     *
     * @var string
     */
    public static $repoUrl = 'https://github.com/marcocesarato/PHP-Antimalware-Scanner';

    /**
     * Version url.
     *
     * @var string
     */
    public static $latestVersionUrl = 'https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/version';

    /**
     * Latest release url.
     *
     * @var string
     */
    protected static $latestReleaseUrl = 'https://raw.githubusercontent.com/marcocesarato/PHP-Antimalware-Scanner/master/dist/scanner';

    /**
     * @var string
     */
    public static $dateFormat = 'Y-m-d';

    /**
     * Root path.
     *
     * @var string
     */
    public static $root = './';

    /**
     * Quarantine path.
     *
     * @var string
     */
    public static $pathQuarantine = '/scanner-quarantine/';

    /**
     * Backup path.
     *
     * @var string
     */
    public static $pathBackups = '/scanner-backups/';

    /**
     * Deobfuscate path.
     *
     * @var string
     */
    public static $pathDeobfuscate = '/deobfuscated/';

    /**
     * Logs Path.
     *
     * @var string
     */
    public static $pathLogs = '/scanner.log';

    /**
     * Infected logs path.
     *s.
     *
     * @var string
     */
    public static $pathReport = '/scanner-report_{DIRNAME}_{DATE}';

    /**
     * Whitelist path.
     *
     * @var string
     */
    public static $pathWhitelist = '/scanner-whitelist.json';

    /**
     * Path to scan.
     *
     * @var string
     */
    public static $pathScan = './';

    /**
     * Max filesize.
     *
     * @var int
     */
    public static $maxFilesize = -1;

    /**
     * File extensions to scan.
     *
     * @var array
     */
    public static $extensions = [
        'htaccess',
        'php',
        'php3',
        'ph3',
        'php4',
        'ph4',
        'php5',
        'ph5',
        'php7',
        'ph7',
        'php8',
        'ph8',
        'phtm',
        'phtml',
        'ico',
    ];

    /**
     * Default no to scan all files.
     */
    public static $scanAll = false;

    /**
     * Arguments.
     *
     * @var Argv
     */
    public static $argv = [];

    /**
     * Whitelist.
     *
     * @var array
     */
    public static $whitelist = [];

    /**
     * Functions.
     *
     * @var array
     */
    public static $functions = [];

    /**
     * Functions encoded.
     *
     * @var array
     */
    public static $functionsEncoded = [];

    /**
     * Functions encoded values.
     *
     * @var array
     */
    protected static $functionsEncodedValues = [];

    /**
     * Exploits.
     *
     * @var array
     */
    public static $exploits = [];

    /**
     * Settings.
     *
     * @var array
     */
    public static $settings = [
        'scan-exploits' => true,
        'scan-functions' => true,
        'scan-signatures' => true,
    ];

    /**
     * Report.
     *
     * @var array
     */
    protected static $report = [
        'scanned' => 0,
        'detected' => 0,
        'removed' => [],
        'ignored' => [],
        'edited' => [],
        'quarantine' => [],
        'whitelist' => [],
        'infectedFound' => [],
    ];

    /**
     * Report file format.
     *
     * @var string
     */
    protected static $reportFormat = 'html';

    /**
     * Ignore paths.
     *
     * @var array
     */
    public static $ignorePaths = [];

    /**
     * Filter paths.
     *
     * @var array
     */
    public static $filterPaths = [];

    /**
     * Prompt.
     *
     * @var string
     */
    public static $prompt;

    /**
     * Interrupt.
     *
     * @var bool
     */
    public $interrupt = false;

    /**
     * @var string
     */
    public $lastError;

    protected static $inited = false;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        if (!self::isInitialized()) {
            if (function_exists('gc_enable') && (function_exists('gc_enable') && !gc_enabled())) {
                gc_enable();
            }

            if (self::$root === './') {
                self::$root = Path::getCurrentDir();
            }

            if (self::$pathScan === './') {
                self::$pathScan = Path::getCurrentDir();
            }

            self::$pathQuarantine = Path::get(self::$root . self::$pathQuarantine);
            self::$pathLogs = Path::get(self::$root . self::$pathLogs);
            self::$pathWhitelist = Path::get(self::$root . self::$pathWhitelist);
            self::$pathReport = Path::get(self::$root . self::$pathReport);
            self::$pathBackups = Path::get(self::$root . self::$pathBackups);
            self::$pathDeobfuscate = Path::get(self::$root . self::$pathDeobfuscate);

            self::$inited = true;
        }
    }

    /**
     * Initialize.
     */
    private function init()
    {
        // Load whitelist
        if (file_exists(self::$pathWhitelist)) {
            self::$whitelist = file_get_contents(self::$pathWhitelist);
            self::$whitelist = @json_decode(self::$whitelist, true);
            if (!is_array(self::$whitelist)) {
                self::$whitelist = [];
            }
        }
    }

    /**
     * Run application.
     *
     * @param null $args
     *
     * @return bool|object
     */
    public function run($args = [])
    {
        $this->interrupt = false;

        try {
            // Print header
            CLI::header();
            // Initialize arguments
            $this->arguments($args);
            // Initialize
            $this->init();
            // Initialize modes
            $this->modes();

            // Start scanning
            CLI::displayLine('Start scanning...');

            CLI::writeLine('Scan date: ' . date(self::$dateFormat . ' H:i:s'));
            CLI::writeLine('Scanning ' . self::$pathScan, 2);

            // Mapping files
            if (self::isVerifierEnabled()) {
                CLI::writeLine('Mapping and retrieving checksums, please wait...', 2);
            } else {
                CLI::writeLine('Mapping, please wait...', 2);
            }

            $iterator = $this->mapping();

            // Counting files
            $filesCount = iterator_count($iterator);
            CLI::writeLine('Found ' . $filesCount . ' files to check', 2);
            CLI::writeLine('Checking files...', 2);
            CLI::progress(0, $filesCount);

            if ($this->interrupt) {
                return false;
            }

            // Scan all files
            $this->scan($iterator);

            // Scan finished
            CLI::writeBreak(2);
            CLI::write('Scan finished!', 'green');
            CLI::writeBreak(3);

            // Print summary
            $this->summary();

            return self::getReport();
        } catch (Exception $e) {
            $this->interrupt = true;
            $this->setLastError($e->getMessage());
            CLI::writeBreak();
            CLI::writeLine($e->getMessage(), 1, 'red');
        }

        return true;
    }

    /**
     * Initialize application arguments.
     *
     * @param array $args
     */
    private function arguments($args = [])
    {
        $isCLI = self::isCli();
        self::$argv = new Argv(self::getLowerName(), self::getDescription());

        // Arguments
        self::$argv->addArgument('path', ['var_args' => true, 'default' => Path::getCurrentDir(), 'help' => 'Define the path of the file or directory to scan']);

        // Flags
        self::$argv->addFlag('lite', ['alias' => '-l', 'default' => false, 'help' => 'Running on lite mode help to have less false positive on WordPress and others platforms enabling exploits mode and removing some common exploit pattern']);
        self::$argv->addFlag('help', ['alias' => ['-h', '-?'], 'default' => false, 'help' => 'Check only functions and not the exploits']);
        self::$argv->addFlag('log', ['default' => self::$pathLogs, 'has_value' => true, 'value_name' => 'path', 'help' => 'Write a log file on the specified file path']);
        self::$argv->addFlag('backup', ['alias' => '-b', 'default' => false, 'help' => 'Make a backup of every touched files']);
        self::$argv->addFlag('offset', ['default' => null, 'has_value' => true, 'help' => 'Set file mapping offset']);
        self::$argv->addFlag('limit', ['default' => null, 'has_value' => true, 'help' => 'Set file mapping limit']);
        self::$argv->addFlag('report', ['alias' => '-r', 'default' => false, 'help' => "Report scan only mode without check and remove malware (like --auto-skip).\nIt also write a report with all malware paths found"]);
        self::$argv->addFlag('report-format', ['default' => false, 'has_value' => true, 'value_name' => 'format', 'help' => 'Report format (html|txt)']);
        self::$argv->addFlag('version', ['alias' => '-v', 'default' => false, 'help' => 'Get version number']);
        self::$argv->addFlag('update', ['alias' => '-u', 'default' => false, 'help' => 'Update to last version']);
        self::$argv->addFlag('only-signatures', ['alias' => '-s', 'default' => false, 'help' => "Check only functions and not the exploits.\nThis is recommended for WordPress or others platforms"]);
        self::$argv->addFlag('only-exploits', ['alias' => '-e', 'default' => false, 'help' => 'Check only exploits and not the functions']);
        self::$argv->addFlag('only-functions', ['alias' => '-f', 'default' => false, 'help' => 'Check only functions and not the exploits']);
        self::$argv->addFlag('defs', ['default' => false, 'help' => 'Get default definitions exploit and functions list']);
        self::$argv->addFlag('defs-exploits', ['default' => false, 'help' => 'Get default definitions exploits list']);
        self::$argv->addFlag('defs-functions', ['default' => false, 'help' => 'Get default definitions functions lists']);
        self::$argv->addFlag('defs-functions-encoded', ['default' => false, 'help' => 'Get default definitions functions encoded lists']);
        self::$argv->addFlag('exploits', ['default' => false, 'has_value' => true, 'help' => 'Filter exploits']);
        self::$argv->addFlag('functions', ['default' => false, 'has_value' => true, 'help' => 'Define functions to search']);
        self::$argv->addFlag('whitelist-only-path', ['default' => false, 'help' => 'Check on whitelist only file path and not line number']);
        self::$argv->addFlag('max-filesize', ['default' => -1, 'has_value' => true, 'value_name' => 'filesize', 'help' => 'Set max filesize to scan']);
        self::$argv->addFlag('silent', ['default' => !$isCLI, 'help' => 'No output and prompt']);
        self::$argv->addFlag('ignore-paths', ['alias' => '--ignore-path', 'default' => null, 'has_value' => true, 'value_name' => 'paths', 'help' => "Ignore path/s, for multiple value separate with comma.\nWildcards are enabled ex. /path/*/cache or /path/*.log"]);
        self::$argv->addFlag('filter-paths', ['alias' => '--filter-path', 'default' => null, 'has_value' => true, 'value_name' => 'paths', 'help' => "Filter path/s, for multiple value separate with comma.\nWildcards are enabled ex. /path/*/htdocs or /path/*.php"]);
        self::$argv->addFlag('auto-clean', ['default' => false, 'help' => 'Auto clean code (without confirmation, use with caution)']);
        self::$argv->addFlag('auto-clean-line', ['default' => false, 'help' => 'Auto clean line code (without confirmation, use with caution)']);
        self::$argv->addFlag('auto-delete', ['default' => false, 'help' => 'Auto delete infected (without confirmation, use with caution)']);
        self::$argv->addFlag('auto-quarantine', ['default' => false, 'help' => 'Auto quarantine']);
        self::$argv->addFlag('auto-skip', ['default' => false, 'help' => 'Auto skip']);
        self::$argv->addFlag('auto-whitelist', ['default' => false, 'help' => 'Auto whitelist (if you sure that source isn\'t compromised)']);
        self::$argv->addFlag('auto-prompt', ['default' => null, 'has_value' => true, 'value_name' => 'prompt', 'help' => "Set auto prompt command .\nex. --auto-prompt=\"delete\" or --auto-prompt=\"1\" (alias of auto-delete)"]);
        self::$argv->addFlag('path-whitelist', ['default' => self::$pathWhitelist, 'has_value' => true, 'value_name' => 'path', 'help' => 'Set whitelist file']);
        self::$argv->addFlag('path-deobfuscate', ['default' => self::$pathDeobfuscate, 'has_value' => true, 'value_name' => 'path', 'help' => "Set debofuscated files path directory.\nIs recommended put files outside the public document path"]);
        self::$argv->addFlag('path-backups', ['default' => self::$pathBackups, 'has_value' => true, 'value_name' => 'path', 'help' => "Set backups path directory.\nIs recommended put files outside the public document path"]);
        self::$argv->addFlag('path-quarantine', ['default' => self::$pathQuarantine, 'has_value' => true, 'value_name' => 'path', 'help' => "Set quarantine path directory.\nIs recommended put files outside the public document path"]);
        self::$argv->addFlag('path-logs', ['default' => self::$pathLogs, 'has_value' => true, 'value_name' => 'path', 'help' => 'Set quarantine log file']);
        self::$argv->addFlag('path-report', ['default' => self::$pathReport, 'has_value' => true, 'value_name' => 'path', 'help' => "Set report log file path and name.\nNote that name will be appended with .log or .html extension."]);
        self::$argv->addFlag('disable-colors', ['alias' => ['--no-colors', '--no-color'], 'default' => false, 'help' => 'Disable CLI colors']);
        self::$argv->addFlag('disable-cache', ['alias' => '--no-cache', 'default' => false, 'help' => 'Disable Cache']);
        self::$argv->addFlag('disable-report', ['alias' => '--no-report', 'default' => !$isCLI, 'help' => 'Disable report generation']);
        self::$argv->addFlag('disable-checksum', ['alias' => ['--no-checksum', '--no-verify'], 'default' => false, 'help' => 'Disable checksum verifying for platforms/frameworks']);
        self::$argv->addFlag('scan-all', ['alias' => ['--all'], 'default' => false, 'help' => 'Check all files, regardless of extension']);
        //self::$argv->addFlag('deobfuscate', ['default' => false, 'help' => 'Deobfuscate directory']);

        self::$argv->parse($args);

        // Version
        if (isset(self::$argv['version']) && self::$argv['version']) {
            $this->interrupt();
        }

        // Help
        if (isset(self::$argv['help']) && self::$argv['help']) {
            CLI::helper();
            $this->interrupt();
        }

        // List exploits
        if (isset(self::$argv['defs']) && self::$argv['defs']) {
            CLI::helplist();
            $this->interrupt();
        }

        // List exploits
        if (isset(self::$argv['defs-exploits']) && self::$argv['defs-exploits']) {
            CLI::helplist('exploits');
        }

        // List functions
        if (isset(self::$argv['defs-functions']) && self::$argv['defs-functions']) {
            CLI::helplist('functions');
        }

        // List functions encoded
        if (isset(self::$argv['defs-functions-encoded']) && self::$argv['defs-functions-encoded']) {
            CLI::helplist('functions-encoded');
        }

        // Update
        if (isset(self::$argv['update']) && self::$argv['update']) {
            $this->update();
            $this->interrupt();
        }

        // Silent
        if (isset(self::$argv['silent']) && self::$argv['silent']) {
            self::setSilentMode();
        }

        // Backups
        if ((isset(self::$argv['backup']) && self::$argv['backup'])) {
            self::enableBackups();
        }

        // Colors
        if (self::$argv['disable-colors']) {
            self::setColors(false);
        } elseif (function_exists('ncurses_has_colors')) {
            self::setColors(ncurses_has_colors());
        }

        // Cache
        if (isset(self::$argv['disable-cache']) && self::$argv['disable-cache']) {
            self::disableCache();
        }

        // Verifier
        if (isset(self::$argv['disable-checksum']) && self::$argv['disable-checksum']) {
            self::disableChecksum();
        }

        // Max filesize
        if (isset(self::$argv['max-filesize']) && is_numeric(self::$argv['max-filesize'])) {
            self::setMaxFilesize(self::$argv['max-filesize']);
        }

        // Write logs
        if (isset(self::$argv['log']) && !empty(self::$argv['log'])) {
            self::enableLogs();
            if (is_string(self::$argv['log'])) {
                self::setPathLogs(self::$argv['log']);
            }
        }

        // Offset
        self::setOffset(0);
        if (isset(self::$argv['offset']) && is_numeric(self::$argv['offset'])) {
            self::setOffset((int)self::$argv['offset']);
        }

        // Limit
        if (isset(self::$argv['limit']) && is_numeric(self::$argv['limit'])) {
            self::setLimit((int)self::$argv['limit']);
        }

        // Path quarantine
        if (isset(self::$argv['path-quarantine']) && !empty(self::$argv['path-quarantine'])) {
            self::setPathQuarantine(self::$argv['path-quarantine']);
        }

        // Path backups
        if (isset(self::$argv['path-backups']) && !empty(self::$argv['path-backups'])) {
            self::setPathBackups(self::$argv['path-backups']);
        }

        // Path deobfuscate
        if (isset(self::$argv['path-deobfuscate']) && !empty(self::$argv['path-deobfuscate'])) {
            self::setPathDeobfuscate(self::$argv['path-deobfuscate']);
        }

        // Path Whitelist
        if (isset(self::$argv['path-whitelist']) && !empty(self::$argv['path-whitelist'])) {
            self::setPathWhitelist(self::$argv['path-whitelist']);
        }

        // Report mode
        self::setReportMode(isset(self::$argv['report']) && self::$argv['report']);

        // Report
        if (isset(self::$argv['disable-report']) && self::$argv['disable-report']) {
            self::setReport(!self::$argv['disable-report']);
        }

        // Deobfuscate mode
        if (isset(self::$argv['deobfuscate']) && self::$argv['deobfuscate']) {
            self::enableDeobfuscateMode();
            self::disableReport();
        }

        // Path report
        if (isset(self::$argv['path-report']) && !empty(self::$argv['path-report'])) {
            self::setPathReport(self::$argv['path-report']);
        }

        // Report format
        if (isset(self::$argv['report-format']) && !empty(self::$argv['report-format'])) {
            self::setReportFormat(self::$argv['report-format']);
        }

        // Path logs
        if (isset(self::$argv['path-logs']) && !empty(self::$argv['path-logs'])) {
            self::setPathLogs(self::$argv['path-logs']);
        }

        // Ignore paths
        if (isset(self::$argv['ignore-paths']) && !empty(self::$argv['ignore-paths'])) {
            $paths = explode(',', self::$argv['ignore-paths']);
            $ignorePaths = [];
            foreach ($paths as $path) {
                $ignorePaths[] = Path::get($path);
            }
            self::setIgnorePaths($ignorePaths);
        }

        // Filter paths
        if (isset(self::$argv['filter-paths']) && !empty(self::$argv['filter-paths'])) {
            $paths = explode(',', self::$argv['filter-paths']);
            $filterPaths = [];
            foreach ($paths as $path) {
                $filterPaths[] = Path::get($path);
            }
            self::setFilterPaths($filterPaths);
        }

        // Check on whitelist only file path and not line number
        if (isset(self::$argv['whitelist-only-path']) && self::$argv['whitelist-only-path']) {
            self::setOnlyPathWhitelistMode();
        }

        // Check Filter exploits
        if (isset(self::$argv['exploits']) && self::$argv['exploits'] && is_string(self::$argv['exploits'])) {
            $exploits = [];
            $filtered = str_replace(["\n", "\r", "\t", ' '], '', self::$argv['exploits']);
            $filtered = @explode(',', $filtered);
            $filtered = array_map('trim', $filtered);
            if (!empty($filtered) && count($filtered) > 0) {
                foreach (Exploits::getAll() as $key => $value) {
                    if (in_array($key, $filtered, true)) {
                        $exploits[$key] = $value;
                    }
                }
                if (!empty($exploits) && count($exploits) > 0) {
                    CLI::writeLine('Exploit to search: ' . implode(', ', array_keys($exploits)));
                } else {
                    $exploits = [];
                }
            }
            self::setExploits($exploits);
        }

        // Check functions to search
        if (isset(self::$argv['functions']) && self::$argv['functions'] && is_string(self::$argv['functions'])) {
            $functions = str_replace(["\n", "\r", "\t", ' '], '', self::$argv['functions']);
            $functions = @explode(',', $functions);
            $functions = array_map('trim', $functions);
            if (!empty($functions) && count($functions) > 0) {
                CLI::writeLine('Functions to search: ' . implode(', ', $functions));
            } else {
                $functions = [];
            }
            self::setFunctions($functions);
        }

        // Check if exploit mode is enabled
        if (isset(self::$argv['only-exploits']) && self::$argv['only-exploits']) {
            self::setOnlyExploitsMode();
        }

        // Check if functions mode is enabled
        if (isset(self::$argv['only-functions']) && self::$argv['only-functions']) {
            self::setOnlyFunctionsMode();
        }

        // Check if only signatures mode is enabled
        if (isset(self::$argv['only-signatures'])) {
            self::setOnlySignaturesMode();
        }

        // Check if lite scan is enabled
        if (isset(self::$argv['lite']) && self::$argv['lite']) {
            self::enableLiteMode();
        }

        // Prompt
        if (isset(self::$argv['auto-clean']) && self::$argv['auto-clean']) {
            self::setAutoClean();
        }

        if (isset(self::$argv['auto-clean-line']) && self::$argv['auto-clean-line']) {
            self::setAutoCleanLine();
        }

        if (isset(self::$argv['auto-delete']) && self::$argv['auto-delete']) {
            self::setAutoDelete();
        }

        if (isset(self::$argv['auto-quarantine']) && self::$argv['auto-quarantine']) {
            self::setAutoQuarantine();
        }

        if (isset(self::$argv['auto-whitelist']) && self::$argv['auto-whitelist']) {
            self::setAutoWhitelist();
        }

        if (isset(self::$argv['auto-skip']) && self::$argv['auto-skip']) {
            self::setAutoSkip();
        }

        if (isset(self::$argv['auto-prompt']) && !empty(self::$argv['auto-prompt'])) {
            self::setPrompt(self::$argv['auto-prompt']);
        }

        // Check if logs and scan at the same time
        if (self::isLogEnabled() && self::isReportMode()) {
            self::disableLogs();
        }

        // Check for path or functions as first argument
        $arg = self::$argv->arg(0);
        if (!empty($arg)) {
            $path = trim($arg);
            if (file_exists(realpath($path))) {
                self::setPathScan(realpath($path));
            }
        }
        // Should we scan each and any file?
        if (isset(self::$argv['scan-all']) && self::$argv['scan-all']) {
            self::setScanAll(true);
        }
    }

    /**
     * Init application modes.
     */
    private function modes()
    {
        if (self::isOnlyFunctionsMode() && self::isOnlyExploitsMode() && self::isOnlySignaturesMode()) {
            $error = 'Can\'t be set flags --only-signatures, --only-functions and --only-exploits together!';
            CLI::writeLine($error, 2);
            $this->interrupt();
            $this->setLastError($error);
        }

        if (self::isOnlyFunctionsMode() && self::isOnlySignaturesMode()) {
            $error = 'Can\'t be set both flags --only-signatures and --only-functions together!';
            CLI::writeLine($error, 2);
            $this->interrupt();
            $this->setLastError($error);
        }

        if (self::isOnlySignaturesMode() && self::isOnlyExploitsMode()) {
            $error = 'Can\'t be set both flags --only-signatures and --only-exploits together!';
            CLI::writeLine($error, 2);
            $this->interrupt();
            $this->setLastError($error);
        }

        if (self::isOnlyFunctionsMode() && self::isOnlyExploitsMode()) {
            $error = 'Can\'t be set both flags --only-functions and --only-exploits together!';
            CLI::writeLine($error, 2);
            $this->interrupt();
            $this->setLastError($error);
        }

        // Malware Definitions
        if (self::isOnlyFunctionsMode() || (!self::isOnlyExploitsMode() && empty(self::$functions))) {
            // Functions to search
            self::setFunctions(Functions::getDefault());
        } elseif (!self::isOnlyExploitsMode() && !self::isLiteMode() && empty(self::$functions)) {
            CLI::writeLine('No functions to search');
        }

        if (self::$argv['max-filesize'] > 0) {
            CLI::writeLine('Max filesize: ' . self::getMaxFilesize() . ' bytes', 2);
        }

        // Exploits to search
        if (!self::isOnlyFunctionsMode() && empty(self::$exploits)) {
            self::setExploits(Exploits::getAll());
        }

        if (self::isLiteMode()) {
            CLI::writeLine('Agile mode enabled');
        }

        if (self::isReportMode()) {
            CLI::writeLine('Report scan mode enabled');
        }

        if (self::isOnlyFunctionsMode()) {
            CLI::writeLine('Only function mode enabled');
            self::setExploits([]);
        }

        if (self::isOnlyExploitsMode() && !self::isLiteMode()) {
            CLI::writeLine('Only exploit mode enabled');
            self::setFunctions([]);
            self::setFunctionsEncoded(Functions::getDangerous());
        }

        if (self::isOnlySignaturesMode()) {
            CLI::writeLine('Only signatures mode enabled');
            self::setExploits([]);
            self::setFunctions([]);
        }
    }

    /**
     * Map files.
     *
     * @return ArrayIterator
     */
    public function mapping()
    {
        // Mapping files
        if (is_dir(self::$pathScan)) {
            $directory = new RecursiveDirectoryIterator(self::$pathScan);
            $files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
            $filtered = new CallbackFilterIterator($files, function ($cur) {
                $ignore = false;
                $wildcard = '.*?'; // '[^\\\\\\/]*'
                // Ignore
                foreach (self::$ignorePaths as $ignorePath) {
                    $ignorePath = preg_quote($ignorePath, ';');
                    $ignorePath = str_replace('\*', $wildcard, $ignorePath);
                    if (preg_match(';' . $ignorePath . ';i', $cur->getPath())) {
                        $ignore = true;
                    }
                }
                // Filter
                foreach (self::$filterPaths as $filterPath) {
                    $filterPath = preg_quote($filterPath, ';');
                    $filterPath = str_replace('\*', $wildcard, $filterPath);
                    if (!preg_match(';' . $filterPath . ';i', $cur->getPath())) {
                        $ignore = true;
                    }
                }

                if (!$ignore &&
                    $cur->isDir()) {
                    if (self::isVerifierEnabled()) {
                        Modules::init($cur->getPath());
                    }

                    return false;
                }

                return
                    !$ignore &&
                    $cur->isFile() &&
                    (self::isScanAll() || in_array($cur->getExtension(), self::getExtensions(), true));
            });
            $mapping = [];

            $mapped = 0;
            $count = iterator_count($filtered);

            $iterator = $filtered;

            if (self::isVerifierEnabled()) {
                unset($iterator);

                CLI::writeBreak();
                CLI::writeLine('Verifying files checksum...', 2);

                foreach ($filtered as $cur) {
                    CLI::progress($mapped++, $count);
                    if ($cur->isFile() && !Modules::isVerified($cur->getPathname())) {
                        $mapping[] = $cur;
                    }
                    CLI::progress($mapped, $count);
                }

                $object = new ArrayObject($mapping);
                $iterator = $object->getIterator();

                CLI::writeBreak();
            }

            return $iterator;
        }

        $file = new SplFileInfo(self::$pathScan);
        $obj = new ArrayObject([$file]);

        return $obj->getIterator();
    }

    /**
     * Detect infected favicon.
     *
     * @param $file
     *
     * @return bool
     */
    public static function isInfectedFavicon($file)
    {
        // Case favicon_[random chars].ico
        $fileName = $file->getFilename();
        $fileExtension = $file->getExtension();

        return ((strpos($fileName, 'favicon_') === 0) && ($fileExtension === 'ico') && (strlen($fileName) > 12)) || preg_match('/^\.[\w]+\.ico/i', trim($fileName));
    }

    /**
     * Scan file.
     *
     * @param $info
     *
     * @return array
     */
    public function scanFile($info)
    {
        $patternFound = [];
        $filePath = $info->getPathname();

        $deobfuscator = new Deobfuscator();
        $contentRaw = file_get_contents($filePath);
        $contentClean = php_strip_whitespace($filePath);
        $contentDeobfuscated = $deobfuscator->deobfuscate($contentClean);
        $contentDecoded = $deobfuscator->decode($contentDeobfuscated);

        $contents = [
            'raw' => $contentRaw, // Original content
            'cleaned' => $contentClean, // Cleaned content
            'deobfuscated' => $contentDeobfuscated, // Deobfuscated content
            'decoded' => $contentDecoded, // Decoded content
        ];

        /**
         * Scan exploits.
         */
        if (self::shouldScanExploits()) {
            foreach (self::$exploits as $key => $exploit) {
                $lastMatch = null;
                $pattern = $exploit['pattern'];
                $checkExploit = function ($match) use ($contentRaw, $exploit, $pattern, $key, &$patternFound) {
                    $type = 'exploit';
                    $lastMatch = $match[0];
                    $patternFoundKey = $type . $key;
                    $lineNumber = CodeMatch::getLineNumber($lastMatch, $contentRaw);
                    if ($lineNumber !== null) {
                        $patternFoundKey .= $lineNumber;
                    }
                    if (!empty($lastMatch)) {
                        $patternFound[$patternFoundKey] = [
                            'type' => $type,
                            'key' => $key,
                            'level' => $exploit['level'],
                            'output' => CodeMatch::getText($type, $key, $exploit['description'], $lastMatch, $lineNumber),
                            'description' => $exploit['description'],
                            'line' => $lineNumber,
                            'pattern' => $pattern,
                            'match' => $lastMatch,
                        ];
                        if (isset($exploit['link'])) {
                            $patternFound[$patternFoundKey]['link'] = $exploit['link'];
                        }
                    }
                };
                // Check exploits
                foreach ($contents as $content) {
                    if (@preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($matches[0] as $match) {
                            $checkExploit($match);
                        }
                    }
                }
            }
        }

        /**
         * Scan definitions.
         */
        if (self::shouldScanSignatures()) {
            foreach (Signatures::getAll() as $key => $pattern) {
                $lastMatch = null;
                $regexPattern = '#' . $pattern . '#smiS';
                $checkDefinitions = function ($match) use ($contentRaw, $key, $regexPattern, &$patternFound) {
                    $type = 'sign';
                    $key = hash('crc32b', $key);
                    $lastMatch = $match[0];
                    $patternFoundKey = $type . $key;
                    $lineNumber = CodeMatch::getLineNumber($lastMatch, $contentRaw);
                    if ($lineNumber !== null) {
                        $patternFoundKey .= $lineNumber;
                    }
                    if (!empty($lastMatch)) {
                        $descriptionPrefix = 'Signature';
                        $description = 'Malware Signature (hash: ' . $key . ')';
                        $patternFound[$patternFoundKey] = [
                            'type' => $type,
                            'key' => $key,
                            'level' => CodeMatch::DANGEROUS,
                            'output' => CodeMatch::getText($descriptionPrefix, $key, $description, $lastMatch, $lineNumber),
                            'description' => $description,
                            'line' => $lineNumber,
                            'pattern' => $regexPattern,
                            'match' => $lastMatch,
                        ];
                    }
                };
                // Check definitions
                foreach ($contents as $content) {
                    if (@preg_match_all($regexPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($matches[0] as $match) {
                            $checkDefinitions($match);
                        }
                    }
                }
            }
        }

        /**
         * Scan functions.
         */
        if (self::shouldScanFunctions()) {
            // Comments are no needed to check function usage so remove raw content type
            unset($contents['raw']);
            $functions = array_merge(self::$functionsEncoded, self::$functions);
            foreach ($functions as $funcRaw) {
                $lastMatch = null;
                $func = preg_quote(trim($funcRaw), '/');
                $checkFunction = function (
                    $match,
                    $pattern,
                    $level = CodeMatch::WARNING,
                    $descriptionPrefix = '',
                    $functionType = ''
                ) use ($contentRaw, $funcRaw, &$patternFound) {
                    $type = 'function';
                    $suffix = '';
                    if (!empty($functionType)) {
                        $suffix = '_' . $functionType;
                    }
                    $lastMatch = CodeMatch::cleanFunctionResult($match[0]); // Clean match
                    $funcKey = $funcRaw . $suffix;
                    $patternFoundKey = $type . $funcKey;
                    $lineNumber = CodeMatch::getLineNumber($lastMatch, $contentRaw);
                    if ($lineNumber !== null) {
                        $patternFoundKey .= $lineNumber;
                    }
                    if (!empty($lastMatch) && empty($patternFound[$patternFoundKey])) {
                        $description = $descriptionPrefix . ' `' . $funcRaw . '`';
                        $patternFound[$patternFoundKey] = [
                            'type' => trim($type . ' ' . $functionType),
                            'key' => $funcKey,
                            'level' => $level,
                            'output' => CodeMatch::getText($type, $funcRaw, $description, $lastMatch, $lineNumber),
                            'description' => $description,
                            'line' => $lineNumber,
                            'pattern' => $pattern,
                            'match' => $lastMatch,
                            'link' => 'https://www.php.net/' . $funcRaw,
                        ];
                    }
                };

                /**
                 * Functions.
                 */
                if (in_array($funcRaw, self::$functionsEncoded, true)) {
                    $encoders = [
                        'str_rot13',
                        'base64_decode',
                        'strrev',
                    ];
                    $regexPattern = CodeMatch::patternFunction($func);
                    foreach ($contents as $contentType => $content) {
                        $codeParts = CodeMatch::getCode($content);
                        foreach ($codeParts as $codePart) {
                            /**
                             * Raw functions.
                             */
                            if (@preg_match_all($regexPattern, $codePart[0], $matches, PREG_OFFSET_CAPTURE)) {
                                foreach ($matches[0] as $match) {
                                    $descriptionPrefix = 'Potentially dangerous function';
                                    $severity = CodeMatch::WARNING;
                                    if ($contentType === 'decoded') {
                                        $severity = CodeMatch::DANGEROUS;
                                        $descriptionPrefix = 'Encoded Function';
                                    }
                                    $checkFunction(
                                        $match,
                                        $regexPattern,
                                        $severity,
                                        $descriptionPrefix
                                    );
                                }
                            }

                            /**
                             * Encoded functions.
                             */
                            foreach ($encoders as $encoder) {
                                $key = $funcRaw . $encoder;
                                if (isset(self::$functionsEncodedValues[$key])) {
                                    $value = self::$functionsEncodedValues[$key];
                                } else {
                                    $value = @$encoder($funcRaw);
                                    self::$functionsEncodedValues[$key] = $value;
                                }
                                $regexPatternEncoded = '/' . preg_quote($value, '/') . '/si';
                                if (@preg_match_all($regexPatternEncoded, $codePart[0], $matches, PREG_OFFSET_CAPTURE)) {
                                    foreach ($matches[0] as $match) {
                                        $checkFunction(
                                            $match,
                                            $regexPatternEncoded,
                                            CodeMatch::DANGEROUS,
                                            'Encoded Function',
                                            $encoder
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (self::shouldScanExploits() &&
            self::isInfectedFavicon($info)) {
            $type = 'exploit';
            $key = 'infected_icon';
            $description = 'LFI (Local File Inclusion), through an infected file with icon, allow remote attackers to inject and execute arbitrary commands or code on the target machine';
            $patternFound[$key] = [
                'type' => $type,
                'key' => $key,
                'level' => CodeMatch::DANGEROUS,
                'output' => CodeMatch::getText($type, $key, $description, ''),
                'description' => $description,
                'line' => '',
                'pattern' => '',
                'match' => '',
            ];
        }

        // Remove duplicated without line number
        $result = $patternFound;
        foreach ($patternFound as $itemKey => $item) {
            foreach ($patternFound as $value) {
                $match1 = preg_replace('/\s+/', '', $item['match']);
                $match2 = preg_replace('/\s+/', '', $value['match']);
                if ($match1 === $match2 &&
                    $item['type'] === $value['type'] &&
                    $item['key'] === $value['key'] &&
                    empty($item['line']) &&
                    !empty($value['line'])
                ) {
                    unset($result[$itemKey]);
                }
            }
        }

        return $result;
    }

    /**
     * Deobfuscate file.
     *
     * @param $info
     */
    public function deobfuscateFile($info)
    {
        $filePath = $info->getPathname();

        $deobfuscator = new Deobfuscator();
        $content = php_strip_whitespace($filePath);
        $content = $deobfuscator->deobfuscate($content);
        $content = $deobfuscator->decode($content);

        // TODO: format code

        $scanPath = realpath(self::getPathScan());
        if (is_file($scanPath)) {
            $scanPath = dirname($scanPath);
        }
        $filePath = self::getPathDeobfuscate() . DIRECTORY_SEPARATOR . str_replace($scanPath, '', realpath($filePath));
        $filePath = Path::get($filePath);
        if (!is_dir(dirname($filePath)) &&
            (!mkdir($concurrentDirectory = dirname($filePath), 0755, true) && !is_dir($concurrentDirectory))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        Actions::putContents($filePath, $content);
    }

    /**
     * Run index.php.
     *
     * @param $iterator
     */
    private function scan($iterator)
    {
        $filesCount = iterator_count($iterator);
        $limit = empty(self::$settings['limit']) ? null : self::$settings['limit'];
        if (!empty(self::$settings['offset'])) {
            self::$report['scanned'] = self::$settings['offset'];
            $iterator = new LimitIterator($iterator, self::$settings['offset'], $limit);
        } elseif (!empty($limit)) {
            $iterator = new LimitIterator($iterator, 0, $limit);
        }

        // Scanning
        foreach ($iterator as $info) {
            CLI::progress(self::$report['scanned'], $filesCount);

            $filePath = $info->getPathname();
            $fileExtension = $info->getExtension();
            $fileSize = filesize($filePath);

            if (self::isDeobfuscateMode()) {
                self::$report['scanned']++;
                usleep(10);
                $this->deobfuscateFile($info);
                continue;
            }

            $isFavicon = self::isInfectedFavicon($info);

            if ((
                (self::isScanAll() || in_array($fileExtension, self::$extensions, true)) &&
                    (self::$maxFilesize < 1 || $fileSize <= self::$maxFilesize) &&
                    (!file_exists(self::$pathQuarantine) || strpos(realpath($filePath), realpath(self::$pathQuarantine)) === false)
                    /*&& (strpos($fileName, '-') === FALSE)*/
            ) ||
                $isFavicon) {
                $patternFound = $this->scanFile($info);

                // Check whitelist
                $inWhitelist = 0;
                foreach (self::$whitelist as $item) {
                    foreach ($patternFound as $pattern) {
                        $lineNumber = $pattern['line'];
                        $exploit = $pattern['key'];
                        $match = $pattern['match'];

                        if (strpos($filePath, $item['file']) !== false &&
                            $match === $item['match'] &&
                            $exploit === $item['exploit'] &&
                            (self::isOnlyPathWhitelistMode() || (!self::isOnlyPathWhitelistMode() && $lineNumber === $item['line']))) {
                            $inWhitelist++;
                        }
                    }
                }

                // Scan finished

                self::$report['scanned']++;
                usleep(10);

                if (realpath($filePath) !== realpath(__FILE__) && ($isFavicon || !empty($patternFound)) && ($inWhitelist === 0 || $inWhitelist !== count($patternFound))) {
                    self::$report['detected']++;
                    if (self::isReportMode()) {
                        // Scan mode only
                        self::$report['infectedFound'][$filePath] = $patternFound;
                        self::$report['ignored'][] = 'File: ' . $filePath . PHP_EOL .
                            'Exploits:' . PHP_EOL .
                            ' => ' . implode(PHP_EOL . ' => ', array_column($patternFound, 'output'));
                        continue;
                    }

                    // Scan with code check
                    $inLoop = true;
                    $fileContent = file_get_contents($filePath);

                    CLI::newLine(2);
                    CLI::writeBreak();
                    CLI::writeLine('PROBABLE MALWARE FOUND!', 1, 'red');

                    $previewLines = explode(CLI::eol(1), trim($fileContent));
                    $preview = implode(CLI::eol(1), array_slice($previewLines, 0, 1000));
                    CLI::displayLine($filePath, 2, 'yellow');

                    $title = CLI::title(' PREVIEW ', '=');
                    CLI::display($title, 'white', 'red');
                    CLI::newLine(2);

                    CLI::code($preview, $patternFound);
                    if (count($previewLines) > 1000) {
                        CLI::newLine(2);
                        CLI::display('  [ ' . (count($previewLines) - 1000) . ' rows more ]');
                    }
                    CLI::newLine(2);

                    $title = CLI::title('', '=');
                    CLI::display($title, 'white', 'red');
                    CLI::newLine(2);
                    CLI::writeLine('Checksum: ' . md5_file($filePath), 1, 'yellow');
                    CLI::writeLine('File path: ' . $filePath, 2, 'yellow');
                    CLI::writeLine('Evil code found: ' . CLI::eol(1) . implode(CLI::eol(1), array_column($patternFound, 'output')), 2, 'red');

                    while ($inLoop) {
                        $fileContent = file_get_contents($filePath);

                        CLI::displayLine('OPTIONS:', 2);

                        $confirmation = self::$prompt;
                        if (self::$prompt === null) {
                            $confirmation = CLI::choice('What is your choice? ', [
                                1 => 'Delete file',
                                2 => 'Move to quarantine',
                                3 => 'Dry run evil code fixer',
                                4 => 'Dry run evil line code fixer',
                                5 => 'Open with vim',
                                6 => 'Open with nano',
                                7 => 'Add to whitelist',
                                8 => 'Show source',
                                '-' => 'Ignore',
                            ]);
                            if (empty(trim($confirmation))) {
                                $confirmation = '0';
                            }
                        }
                        CLI::newLine();

                        unset($previewLines, $preview);

                        switch (true) {
                            // Remove file
                            case in_array($confirmation, ['1', 'delete']):
                                CLI::writeLine('File path: ' . $filePath, 1, 'yellow');
                                $confirm2 = 'y';
                                if (self::$prompt === null) {
                                    $confirm2 = CLI::read('Want delete this file [y|N]? ', 'purple');
                                }
                                CLI::newLine();
                                if ($confirm2 === 'y') {
                                    Actions::deleteFile($filePath);
                                    self::$report['removed'][] = $filePath;
                                    CLI::writeLine("File '$filePath' removed!", 2, 'green');
                                    $inLoop = false;
                                }
                                break;
                            // Move to quarantine
                            case in_array($confirmation, ['2', 'quarantine']):
                                $quarantine = Actions::moveToQuarantine($filePath);
                                self::$report['quarantine'][] = $quarantine;
                                CLI::writeLine("File '$filePath' moved to quarantine!", 2, 'green');
                                $inLoop = false;
                                break;
                            // Remove evil code
                            case in_array($confirmation, ['3', 'clean']) && count($patternFound) > 0:
                                $fileContent = Actions::cleanEvilCode($fileContent, $patternFound);
                                CLI::newLine();

                                $title = CLI::title(' SANITIZED ', '=');
                                CLI::display($title, 'black', 'green');
                                CLI::newLine(2);
                                CLI::code($fileContent);
                                CLI::newLine(2);

                                $title = CLI::title('', '=');
                                CLI::display($title, 'black', 'green');
                                CLI::newLine(2);
                                CLI::displayLine('File sanitized, now you must verify if has been fixed correctly.', 2, 'yellow');
                                $confirm2 = 'y';
                                if (self::$prompt === null) {
                                    $confirm2 = CLI::read('Confirm and save [y|N]? ', 'purple');
                                }
                                CLI::newLine();
                                if ($confirm2 === 'y') {
                                    CLI::writeLine("File '$filePath' sanitized!", 2, 'green');
                                    Actions::putContents($filePath, $fileContent);
                                    self::$report['removed'][] = $filePath;
                                    $inLoop = false;
                                } else {
                                    self::$report['ignored'][] = $filePath;
                                }
                                break;
                            // Remove evil line code
                            case in_array($confirmation, ['4', 'clean-line']) && count($patternFound) > 0:
                                $fileContent = Actions::cleanEvilCodeLine($fileContent, $patternFound);

                                CLI::newLine();

                                $title = CLI::title(' SANITIZED ', '=');
                                CLI::display($title, 'black', 'green');
                                CLI::newLine(2);
                                CLI::code($fileContent);
                                CLI::newLine(2);

                                $title = CLI::title('', '=');
                                CLI::display($title, 'black', 'green');
                                CLI::newLine(2);
                                CLI::displayLine('File sanitized, now you must verify if has been fixed correctly.', 2, 'yellow');
                                $confirm2 = 'y';
                                if (self::$prompt === null) {
                                    $confirm2 = CLI::read('Confirm and save [y|N]? ', 'purple');
                                }
                                CLI::newLine();
                                if ($confirm2 === 'y') {
                                    CLI::writeLine("File '$filePath' sanitized!", 2, 'green');
                                    Actions::putContents($filePath, $fileContent);
                                    self::$report['removed'][] = $filePath;
                                    $inLoop = false;
                                } else {
                                    self::$report['ignored'][] = $filePath;
                                }
                                break;
                            // Open with vim
                            case in_array($confirmation, ['5', 'vim']):
                                Actions::openWithVim($filePath);
                                self::$report['edited'][] = $filePath;
                                CLI::writeLine("File '$filePath' edited with vim!", 2, 'green');
                                self::$report['removed'][] = $filePath;
                                break;
                            // Open with nano
                            case in_array($confirmation, ['6', 'nano']):
                                Actions::openWithNano($filePath);
                                self::$report['edited'][] = $filePath;
                                CLI::writeLine("File '$filePath' edited with nano!", 2, 'green');
                                self::$report['removed'][] = $filePath;
                                break;
                            // Add to whitelist
                            case in_array($confirmation, ['7', 'whitelist']):
                                if (Actions::addToWhitelist($filePath, $patternFound)) {
                                    self::$report['whitelist'][] = $filePath;
                                    CLI::writeLine("Exploits of file '$filePath' added to whitelist!", 2, 'green');
                                    $inLoop = false;
                                } else {
                                    CLI::writeLine("Exploits of file '$filePath' failed adding file to whitelist! Check write permission of '" . self::$pathWhitelist . "' file!", 2, 'red');
                                }
                                break;
                            // Show source code
                            case in_array($confirmation, ['8', 'show']):
                                CLI::newLine();
                                CLI::displayLine("File path: $filePath", 2, 'yellow');

                                $title = CLI::title(' SOURCE ', '=');
                                CLI::display($title, 'white', 'red');
                                CLI::newLine(2);

                                CLI::code($fileContent, $patternFound);
                                CLI::newLine(2);

                                $title = CLI::title('', '=');
                                CLI::display($title, 'white', 'red');
                                CLI::newLine(2);
                                CLI::displayLine("File path: $filePath", 2, 'yellow');
                                break;
                            // Skip
                            case in_array($confirmation, ['0', '-', 'skip']):
                                CLI::writeLine("File '$filePath' skipped!", 2, 'green');
                                self::$report['ignored'][] = $filePath;
                                $inLoop = false;
                                break;
                            default:
                                CLI::writeLine('Option not found! Retry...', 1, 'red');
                                break;
                        }

                        CLI::writeBreak();
                    }
                    unset($fileContent);
                }
            }
        }
    }

    /**
     * Print summary.
     */
    private function summary()
    {
        if (!empty(self::$settings['offset'])) {
            self::$report['scanned'] -= self::$settings['offset'];
        }

        // Statistics
        CLI::displayTitle('SUMMARY', 'black', 'cyan');
        CLI::writeBreak();
        CLI::writeLine('Files scanned: ' . self::$report['scanned']);
        if (!self::isReportMode()) {
            self::$report['ignored'] = array_unique(self::$report['ignored']);
            self::$report['edited'] = array_unique(self::$report['edited']);
            CLI::writeLine('Files edited: ' . count(self::$report['edited']));
            CLI::writeLine('Files quarantined: ' . count(self::$report['quarantine']));
            CLI::writeLine('Files whitelisted: ' . count(self::$report['whitelist']));
            CLI::writeLine('Files ignored: ' . count(self::$report['ignored']), 2);
        }

        CLI::writeLine('Malware detected: ' . self::$report['detected']);
        if (!self::isReportMode()) {
            CLI::writeLine('Malware removed: ' . count(self::$report['removed']));
        }

        if (self::isReportEnabled()) {
            $report = new Report();
            $format = 'html';
            if (in_array(self::$reportFormat, ['html', 'htm'])) {
                $report->setData([
                    'count' => self::$report['scanned'],
                    'results' => self::$report['infectedFound'],
                ]);
            } else {
                $format = 'text';
                $report->setData([
                    'content' => 'Scan date: ' . date(self::$dateFormat . ' H:i:s') . CLI::eol(1) . implode(CLI::eol(2), self::$report['ignored']),
                ]);
            }
            $output = $report->save(self::$pathReport, $format);

            if (self::isReportMode()) {
                CLI::writeLine(CLI::eol(1) . "Report saved to '" . $output . "'", 1, 'red');
                CLI::writeBreak(2);
            }
        }

        if (!self::isReportMode()) {
            if (count(self::$report['removed']) > 0) {
                CLI::writeBreak();
                CLI::writeLine('Files removed:', 1, 'red');
                foreach (self::$report['removed'] as $un) {
                    CLI::writeLine($un);
                }
            }
            if (count(self::$report['edited']) > 0) {
                CLI::writeBreak();
                CLI::writeLine('Files edited:', 1, 'green');
                foreach (self::$report['edited'] as $un) {
                    CLI::writeLine($un);
                }
            }
            if (count(self::$report['quarantine']) > 0) {
                CLI::writeBreak();
                CLI::writeLine('Files quarantined:', 1, 'yellow');
                foreach (self::$report['ignored'] as $un) {
                    CLI::writeLine($un);
                }
            }
            if (count(self::$report['whitelist']) > 0) {
                CLI::writeBreak();
                CLI::writeLine('Files whitelisted:', 1, 'cyan');
                foreach (self::$report['whitelist'] as $un) {
                    CLI::writeLine($un);
                }
            }
            if (count(self::$report['ignored']) > 0) {
                CLI::writeBreak();
                CLI::writeLine('Files ignored:', 1, 'cyan');
                foreach (self::$report['ignored'] as $un) {
                    CLI::writeLine($un);
                }
            }
            CLI::writeBreak(2);
        }
    }

    /**
     * Update to last version.
     */
    public function update()
    {
        if (!self::isCli()) {
            return;
        }

        CLI::writeLine('Checking update...');
        $version = file_get_contents(self::getLatestVersionUrl());
        if (!empty($version)) {
            if (version_compare(self::$version, $version, '<')) {
                CLI::write('New version');
                CLI::write(' ' . $version . ' ');
                CLI::writeLine('of the scanner available!', 2);
                $confirm = CLI::read('You sure you want update the index.php to the last version [y|N]? ', 'purple');
                CLI::writeBreak();
                if (strtolower($confirm) === 'y') {
                    $newVersion = file_get_contents(self::getLatestReleaseUrl());
                    file_put_contents(Path::getCurrent(), $newVersion);
                    CLI::write('Updated to last version');
                    CLI::write(' (' . self::$version . ' => ' . $version . ') ');
                    CLI::writeLine('with SUCCESS!', 2);
                } else {
                    CLI::writeLine('Updated SKIPPED!', 2);
                }
            } else {
                CLI::writeLine('You have the last version of the index.php yet!', 2);
            }
        } else {
            CLI::writeLine('Update FAILED!', 2, 'red');
        }
    }

    /**
     * Is console instance.
     *
     * @return bool
     */
    public static function isCli()
    {
        return
            defined('STDIN') ||
            php_sapi_name() === 'cli' ||
            (
                empty($_SERVER['REMOTE_ADDR']) &&
                !isset($_SERVER['HTTP_USER_AGENT']) &&
                count($_SERVER['argv']) > 0
            );
    }

    /**
     * Interrupt.
     */
    protected function interrupt()
    {
        $this->interrupt = true;
        if (self::isCli()) {
            exit();
        }
    }

    /**
     * @return bool
     */
    public static function isLogEnabled()
    {
        return isset(self::$settings['log']) ? self::$settings['log'] : false;
    }

    /**
     * @return self
     */
    public static function enableLogs()
    {
        self::$settings['log'] = true;

        return new self();
    }

    /**
     * @return self
     */
    public static function disableLogs()
    {
        self::$settings['log'] = false;

        return new self();
    }

    /**
     * @param int $offset
     *
     * @return self
     */
    public static function setOffset($offset)
    {
        self::$settings['offset'] = $offset;

        return new self();
    }

    /**
     * @param int $limit
     *
     * @return self
     */
    public static function setLimit($limit)
    {
        self::$settings['limit'] = $limit;

        return new self();
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setSilentMode($mode = true)
    {
        self::$settings['silent'] = $mode;
        if ($mode) {
            if (self::$prompt === null) {
                self::setAutoSkip();
            }
            self::disableReportMode();
        }

        return new self();
    }

    /**
     * @return bool
     */
    public static function isSilentMode()
    {
        return isset(self::$settings['silent']) ? self::$settings['silent'] : false;
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setVerifier($mode = true)
    {
        self::$settings['verifier'] = $mode;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isVerifierEnabled()
    {
        return isset(self::$settings['verifier']) ? self::$settings['verifier'] : true;
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setColors($mode = true)
    {
        self::$settings['colors'] = $mode;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isColorEnabled()
    {
        return isset(self::$settings['colors']) ? self::$settings['colors'] : true;
    }

    /**
     * @return bool
     */
    public static function isCacheEnabled()
    {
        return isset(self::$settings['cache']) ? self::$settings['cache'] : true;
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setDeobfuscateMode($mode = true)
    {
        self::$settings['deobfuscate-mode'] = $mode;
        self::enableReport();

        return new self();
    }

    /**
     * @return bool
     */
    public static function isDeobfuscateMode()
    {
        return isset(self::$settings['deobfuscate-mode']) ? self::$settings['deobfuscate-mode'] : false;
    }

    /**
     * @return self
     */
    public static function enableDeobfuscateMode()
    {
        self::setDeobfuscateMode();

        return new self();
    }

    /**
     * @return self
     */
    public static function enableChecksum()
    {
        self::setVerifier();

        return new self();
    }

    /**
     * @return self
     */
    public static function disableChecksum()
    {
        self::setVerifier(false);

        return new self();
    }

    /**
     * @return self
     */
    public static function enableCache()
    {
        self::$settings['cache'] = true;

        return new self();
    }

    /**
     * @return self
     */
    public static function disableCache()
    {
        self::$settings['cache'] = false;

        return new self();
    }

    /**
     * @return self
     */
    public static function disableDeobfuscateMode()
    {
        self::setDeobfuscateMode(false);

        return new self();
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setOnlyFunctionsMode($mode = true)
    {
        self::$settings['only-functions'] = $mode;
        self::$settings['scan-exploits'] = false;
        self::$settings['scan-functions'] = true;
        self::$settings['scan-signatures'] = true;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isOnlyFunctionsMode()
    {
        return isset(self::$settings['only-functions']) ? self::$settings['only-functions'] : false;
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setOnlyExploitsMode($mode = true)
    {
        self::$settings['only-exploits'] = $mode;
        self::$settings['scan-exploits'] = true;
        self::$settings['scan-functions'] = false;
        self::$settings['scan-signatures'] = false;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isOnlyExploitsMode()
    {
        return isset(self::$settings['only-exploits']) ? self::$settings['only-exploits'] : false;
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setOnlySignaturesMode($mode = true)
    {
        self::$settings['only-signatures'] = $mode;
        self::$settings['scan-exploits'] = false;
        self::$settings['scan-functions'] = false;
        self::$settings['scan-signatures'] = true;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isOnlySignaturesMode()
    {
        return isset(self::$settings['only-signatures']) ? self::$settings['only-signatures'] : false;
    }

    /**
     * @return self
     */
    public static function enableLiteMode()
    {
        self::setLiteMode();

        return new self();
    }

    /**
     * @return self
     */
    public static function disableLiteMode()
    {
        self::setLiteMode(false);

        return new self();
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    protected static function setLiteMode($mode = true)
    {
        self::$settings['lite'] = $mode;
        if ($mode) {
            self::$settings['exploits'] = true;
            self::$exploits = Exploits::getLite();
        } else {
            self::$exploits = Exploits::getAll();
        }

        return new self();
    }

    /**
     * @return bool
     */
    public static function isLiteMode()
    {
        return isset(self::$settings['lite']) ? self::$settings['lite'] : false;
    }

    /**
     * @return self
     */
    public static function setReportMode($mode = true)
    {
        self::$settings['report-mode'] = $mode;
        if (self::isCli()) {
            self::enableReport();
        } else {
            self::setReport($mode);
        }

        return new self();
    }

    /**
     * @return bool
     */
    public static function isReportMode()
    {
        return isset(self::$settings['report-mode']) ? self::$settings['report-mode'] : false;
    }

    /**
     * @return self
     */
    public static function enableReportMode()
    {
        self::setReportMode();

        return new self();
    }

    /**
     * @return self
     */
    public static function disableReportMode()
    {
        self::setReportMode(false);

        return new self();
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    protected static function setReport($mode = true)
    {
        self::$settings['report'] = $mode;

        return new self();
    }

    /**
     * @return self
     */
    public static function enableReport()
    {
        self::setReport();

        return new self();
    }

    /**
     * @return self
     */
    public static function disableReport()
    {
        self::setReport(false);

        return new self();
    }

    /**
     * @return bool
     */
    public static function isReportEnabled()
    {
        return isset(self::$settings['report']) ? self::$settings['report'] : true;
    }

    /**
     * @return self
     */
    public static function enableBackups()
    {
        self::$settings['backup'] = true;

        return new self();
    }

    /**
     * @return self
     */
    public static function disableBackups()
    {
        self::$settings['backup'] = false;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isBackupEnabled()
    {
        return isset(self::$settings['backup']);
    }

    /**
     * @param  bool  $mode
     *
     * @return self
     */
    public static function setOnlyPathWhitelistMode($mode = true)
    {
        self::$settings['whitelist-only-path'] = $mode;

        return new self();
    }

    /**
     * @return bool
     */
    public static function isOnlyPathWhitelistMode()
    {
        return isset(self::$settings['whitelist-only-path']) ? self::$settings['whitelist-only-path'] : false;
    }

    /**
     * @return array
     */
    public static function getIgnorePaths()
    {
        return self::$ignorePaths;
    }

    /**
     * @param array $ignorePaths
     *
     * @return self
     */
    public static function setIgnorePaths($ignorePaths)
    {
        self::$ignorePaths = $ignorePaths;

        return new self();
    }

    /**
     * @return array
     */
    public static function getFilterPaths()
    {
        return self::$filterPaths;
    }

    /**
     * @param array $filterPaths
     *
     * @return self
     */
    public static function setFilterPaths($filterPaths)
    {
        self::$filterPaths = $filterPaths;

        return new self();
    }

    /**
     * @return string
     */
    public static function getPrompt()
    {
        return self::$prompt;
    }

    /**
     * @param string $prompt
     *
     * @return self
     */
    public static function setPrompt($prompt)
    {
        if (!empty($prompt)) {
            self::disableReportMode();
        }
        self::$prompt = $prompt;

        return new self();
    }

    /**
     * @param string $mode
     *
     * @return self
     */
    public static function setAutoDelete($mode = true)
    {
        self::setPrompt($mode !== '' ? 'delete' : null);

        return new self();
    }

    /**
     * @param string $mode
     *
     * @return self
     */
    public static function setAutoClean($mode = true)
    {
        self::setPrompt($mode !== '' ? 'clean' : null);

        return new self();
    }

    /**
     * @param string $mode
     *
     * @return self
     */
    public static function setAutoCleanLine($mode = true)
    {
        self::setPrompt($mode !== '' ? 'clean-line' : null);

        return new self();
    }

    /**
     * @param string $mode
     *
     * @return self
     */
    public static function setAutoQuarantine($mode = true)
    {
        self::setPrompt($mode !== '' ? 'quarantine' : null);

        return new self();
    }

    /**
     * @param string $mode
     *
     * @return self
     */
    public static function setAutoWhitelist($mode = true)
    {
        self::setPrompt($mode !== '' ? 'whitelist' : null);

        return new self();
    }

    /**
     * @param string $mode
     *
     * @return self
     */
    public static function setAutoSkip($mode = true)
    {
        self::setPrompt($mode !== '' ? 'skip' : null);

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathBackups()
    {
        return self::$pathBackups;
    }

    /**
     * @param string $pathBackups
     *
     * @return self
     */
    public static function setPathBackups($pathBackups)
    {
        self::$pathBackups = Path::get($pathBackups);

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathDeobfuscate()
    {
        return self::$pathDeobfuscate;
    }

    /**
     * @param string $pathDeobfuscate
     *
     * @return self
     */
    public static function setPathDeobfuscate($pathDeobfuscate)
    {
        self::$pathDeobfuscate = Path::get($pathDeobfuscate);

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathQuarantine()
    {
        return self::$pathQuarantine;
    }

    /**
     * @param string $pathQuarantine
     *
     * @return self
     */
    public static function setPathQuarantine($pathQuarantine)
    {
        self::$pathQuarantine = Path::get($pathQuarantine);

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathLogs()
    {
        return self::$pathLogs;
    }

    /**
     * @param string $pathLogs
     *
     * @return self
     */
    public static function setPathLogs($pathLogs)
    {
        self::$pathLogs = Path::get($pathLogs);

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathReport()
    {
        return self::$pathReport;
    }

    /**
     * @param string $pathReport
     *
     * @return self
     */
    public static function setPathReport($pathReport)
    {
        self::$pathReport = Path::get($pathReport);

        return new self();
    }

    /**
     * @param string $reportFormat
     *
     * @return self
     */
    public static function setReportFormat($reportFormat)
    {
        if (in_array($reportFormat, ['txt', 'log', 'logs', 'html', 'htm'])) {
            self::$reportFormat = $reportFormat;
        }

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathWhitelist()
    {
        return self::$pathWhitelist;
    }

    /**
     * @param string $pathWhitelist
     *
     * @return self
     */
    public static function setPathWhitelist($pathWhitelist)
    {
        self::$pathWhitelist = Path::get($pathWhitelist);

        return new self();
    }

    /**
     * @return string
     */
    public static function getPathScan()
    {
        return self::$pathScan;
    }

    /**
     * @param string $pathScan
     *
     * @return self
     */
    public static function setPathScan($pathScan)
    {
        self::$pathScan = Path::get($pathScan);

        return new self();
    }

    /**
     * @return int
     */
    public static function getMaxFilesize()
    {
        return self::$maxFilesize;
    }

    /**
     * @param mixed $maxFilesize
     *
     * @return self
     */
    public static function setMaxFilesize($maxFilesize)
    {
        $maxFilesize = trim($maxFilesize);
        if (!is_numeric(self::$argv['max-filesize'])) {
            $maxFilesize = Path::sizeToBytes($maxFilesize);
        }
        self::$maxFilesize = $maxFilesize;

        return new self();
    }

    /**
     * @return array
     */
    public static function getExtensions()
    {
        return self::$extensions;
    }

    /**
     * @return self
     */
    public static function setFunctions($functions)
    {
        self::$functions = $functions;
        self::$functionsEncoded = $functions;

        return new self();
    }

    /**
     * @param array $functions
     *
     * @return self
     */
    public static function setFunctionsEncoded($functions)
    {
        $dangerousFunctions = Functions::getDangerous();
        $encodedFunc = array_unique(array_merge($functions, $dangerousFunctions));
        self::$functionsEncoded = $encodedFunc;

        return new self();
    }

    /**
     * @param array $exploits
     *
     * @return self
     */
    public static function setExploits($exploits)
    {
        self::$exploits = $exploits;

        return new self();
    }

    /**
     * @param array $extensions
     *
     * @return self
     */
    public static function setExtensions($extensions)
    {
        self::$extensions = $extensions;

        return new self();
    }

    /**
     * @return string
     */
    public static function getReportFormat()
    {
        return self::$reportFormat;
    }

    /**
     * @return int
     */
    public static function getReportFilesScanned()
    {
        return self::$report['scanned'];
    }

    /**
     * @return int
     */
    public static function getReportMalwareDetected()
    {
        return self::$report['detected'];
    }

    /**
     * @return array
     */
    public static function getReportMalwareRemoved()
    {
        return self::$report['removed'];
    }

    /**
     * @return array
     */
    public static function getReportFilesIgnored()
    {
        return self::$report['ignored'];
    }

    /**
     * @return array
     */
    public static function getReportFilesEdited()
    {
        return self::$report['edited'];
    }

    /**
     * @return array
     */
    public static function getReportQuarantine()
    {
        return self::$report['quarantine'];
    }

    /**
     * @return array
     */
    public static function getReportWhitelist()
    {
        return self::$report['quarantine'];
    }

    /**
     * @return string
     */
    public static function getLowerName()
    {
        return strtolower(self::$name);
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return self::$name;
    }

    /**
     * @return string
     */
    public static function getFullName()
    {
        return self::$fullname;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return self::$version;
    }

    /**
     * @return string
     */
    public static function getRoot()
    {
        return self::$root;
    }

    /**
     * @return Argv
     */
    public static function getArgv()
    {
        return self::$argv;
    }

    /**
     * @return array
     */
    public static function getWhitelist()
    {
        return self::$whitelist;
    }

    /**
     * @param array $whitelist
     *
     * @return self
     */
    public static function setWhitelist($whitelist)
    {
        self::$whitelist = $whitelist;

        return new self();
    }

    /**
     * @return array
     */
    public static function getFunctions()
    {
        return self::$functions;
    }

    /**
     * @return array
     */
    public static function getFunctionsEncoded()
    {
        return self::$functionsEncoded;
    }

    /**
     * @return array
     */
    public static function getExploits()
    {
        return array_keys(self::$exploits);
    }

    /**
     * @return array
     */
    public static function getSettings()
    {
        return self::$settings;
    }

    /**
     * @return object
     */
    public static function getReport()
    {
        return (object)self::$report;
    }

    /**
     * @return bool
     */
    public function isInterrupted()
    {
        return $this->interrupt;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param string $lastError
     *
     * @return self
     */
    protected function setLastError($lastError)
    {
        $this->lastError = $lastError;

        return $this;
    }

    /**
     * @return bool
     */
    protected static function shouldScanExploits()
    {
        return isset(self::$settings['scan-exploits']) ? self::$settings['scan-exploits'] : true;
    }

    /**
     * @return bool
     */
    protected static function shouldScanFunctions()
    {
        return isset(self::$settings['scan-functions']) ? self::$settings['scan-functions'] : true;
    }

    /**
     * @return bool
     */
    protected static function shouldScanSignatures()
    {
        return isset(self::$settings['scan-signatures']) ? self::$settings['scan-signatures'] : true;
    }

    /**
     * @return string
     */
    public static function getDescription()
    {
        return self::$description;
    }

    /**
     * @return string
     */
    public static function getRepoUrl()
    {
        return self::$repoUrl;
    }

    /**
     * @return string
     */
    public static function getAuthor()
    {
        return self::$author;
    }

    /**
     * @return bool
     */
    public function isInterrupt()
    {
        return $this->interrupt;
    }

    /**
     * @return bool
     */
    protected static function isInitialized()
    {
        return self::$inited;
    }

    /**
     * @return string
     */
    public static function getLatestVersionUrl()
    {
        return self::$latestVersionUrl;
    }

    /**
     * @return string
     */
    public static function getLatestReleaseUrl()
    {
        return self::$latestReleaseUrl;
    }

    /**
     * @return bool
     */
    public static function isScanAll()
    {
        return self::$scanAll;
    }

    /**
     * @param bool $scanAll
     */
    public static function setScanAll($scanAll)
    {
        self::$scanAll = $scanAll;
    }
}
