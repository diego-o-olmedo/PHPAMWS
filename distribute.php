<?php

/**
 * Antimalware Scanner
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2018
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/marcocesarato/PHP-Antimalware-Scanner
 * @version 0.4.0.41
 */

require 'vendor/autoload.php';

$input  = 'src/scanner';
$output = 'dist/scanner';

$jc               = new JuggleCode();
$jc->masterfile   = $input;
$jc->outfile      = $output;
$jc->mergeScripts = true;
$jc->run();

$comment = <<<EOD
<?php

/**
 * Antimalware Scanner
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2018
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/marcocesarato/PHP-Antimalware-Scanner
 * @version 0.4.0.41
 */
 
EOD;


$file = php_strip_whitespace($output);
$file = preg_replace('/\s*\<\?php/si', $comment, $file);
$file = preg_replace('/namespace marcocesarato\\\\amwscan\;\s*/si', '', $file);
$file = "#!/usr/bin/php" . PHP_EOL . $file;
$file = file_put_contents($output, $file);