<?php
require 'vendor/autoload.php';

$input  = 'src/scanner';
$output = 'dist/scanner';

$jc               = new JuggleCode();
$jc->masterfile   = $input;
$jc->outfile      = $output;
$jc->mergeScripts = true;
$jc->run();

$file = php_strip_whitespace($output);
$file = preg_replace('/namespace marcocesarato\\\\amwscan\;\s*/si', '', $file);
$file = "#!/usr/bin/php" . PHP_EOL . $file;
$file = file_put_contents($output, $file);