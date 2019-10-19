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
 * Class Deobfuscator.
 */
class Deobfuscator
{
    private $full_code = '';

    /**
     * Deobfuscate code.
     *
     * @param $str
     *
     * @return bool|mixed|string|string[]|null
     */
    public function deobfuscate($str)
    {
        $str = str_replace(array(".''", "''.", '.""', '"".'), '', $str);

        $this->full_code = $str;

        $type = $this->getObfuscateType($str);
        if (empty($type)) {
            $str_decoded = $this->decode($str);
            $type = $this->getObfuscateType($str_decoded);
            if (!empty($type)) {
                $str = $str_decoded;
            }
        }

        switch ($type) {
            case '_GLOBALS_':
                $str = $this->deobfuscate_bitrix(($str));
                break;
            case 'eval':
                $str = $this->deobfuscate_eval(($str));
                break;
            case 'ALS-Fullsite':
                $str = $this->deobfuscate_als(($str));
                break;
            case 'LockIt!':
                $str = $this->deobfuscate_lockit($str);
                break;
            case 'FOPO':
                $str = $this->deobfuscate_fopo(($str));
                break;
            case 'ByteRun':
                $str = $this->deobfuscate_byterun(($str));
                break;
            case 'urldecode_globals':
                $str = $this->deobfuscate_urldecode(($str));
                break;
        }

        return $str;
    }

    /**
     * Decode.
     *
     * @param $code
     *
     * @return mixed
     */
    public function decode($code)
    {
        preg_match_all("/<\?php(.*?)(?!\B\"[^\"]*)(\?>|$|)(?![^\"]*\"\B)/si", $code, $matches_php);
        foreach ($matches_php as $match_php) {
            $match_php = preg_replace("/<\?php(.*?)(?!\B\"[^\"]*)(\?>|$)(?![^\"]*\"\B)/si", '$1', $match_php[0]);
            $str = preg_replace("/(\\'|\\\")[\s\r\n]*\.[\s\r\n]*('|\")/si", '', $match_php); // Remove "ev"."al"
            $str = preg_replace("/([\s]+)/i", ' ', $str); // Remove multiple spaces

            // Convert hex
            $str = preg_replace_callback('/\\\\x[A-Fa-f0-9]{2}/si', function ($match) {
                return @hex2bin(str_replace('\\x', '', $match[0]));
            }, $str);

            // Convert dec and oct
            $str = preg_replace_callback('/\\\\[0-9]{3}/si', function ($match) {
                return chr(intval($match[0]));
            }, $str);

            // Decode strings
            $decoders = array(
                'str_rot13',
                'gzinflate',
                'base64_decode',
                'rawurldecode',
                'gzuncompress',
                'strrev',
                'convert_uudecode',
                'urldecode',
            );
            $pattern_decoder = array();
            foreach ($decoders as $decoder) {
                $pattern_decoder[] = preg_quote($decoder, '/');
            }
            $last_match = null;
            $recursive_loop = true;
            do {
                // Check decode functions
                $regex_pattern = '/((' . implode($pattern_decoder, '|') . ')[\s\r\n]*\((([^()]|(?R))*)?\))/si';
                preg_match($regex_pattern, $str, $match);
                // Get value inside function
                if ($recursive_loop && preg_match('/(\((?:\"|\\\')(([^\\\'\"]|(?R))*?)(?:\"|\\\')\))/si', $match[0], $encoded_match)) {
                    $value = $encoded_match[3];
                    $decoders_found = array_reverse(explode('(', $match[0]));
                    foreach ($decoders_found as $decoder) {
                        if (in_array($decoder, $decoders)) {
                            if (is_string($value) && !empty($value)) {
                                $value = $decoder($value); // Decode
                            }
                        }
                    }
                    if (is_string($value) && !empty($value)) {
                        $value = str_replace('"', "'", $value);
                        $value = '"' . $value . '"';
                        $str = str_replace($match[0], $value, $str);
                    } else {
                        $recursive_loop = false;
                    }
                } else {
                    $recursive_loop = false;
                }
            } while (!empty($match[0]) && $recursive_loop);

            $code = str_replace($match_php, $str, $code);
        }

        return $code;
    }

    /**
     * Deobfuscate bitrix.
     *
     * @param $str
     *
     * @return string|string[]|null
     */
    private function deobfuscate_bitrix($str)
    {
        $res = $str;
        $funclist = array();
        $res = preg_replace("|[\"']\s*\.\s*['\"]|smi", '', $res);
        $res = preg_replace_callback('~(?:min|max)\(\s*\d+[\,\|\s\|+\|\-\|\*\|\/][\d\s\.\,\+\-\*\/]+\)~ms', array(
            $this,
            'calc',
        ), $res);
        $res = preg_replace_callback('|(round\((.+?)\))|smi', function ($matches) {
            return round($matches[2]);
        }, $res);
        $res = preg_replace_callback('|base64_decode\(["\'](.*?)["\']\)|smi', function ($matches) {
            return "'" . base64_decode($matches[1]) . "'";
        }, $res);

        $res = preg_replace_callback('|["\'](.*?)["\']|sm', function ($matches) {
            $temp = base64_decode($matches[1]);
            if (base64_encode($temp) === $matches[1] && preg_match('#^[ -~]*$#', $temp)) {
                return "'" . $temp . "'";
            } else {
                return "'" . $matches[1] . "'";
            }
        }, $res);

        if (preg_match_all('|\$(?:\{(?:"|\'))?GLOBALS(?:(?:"|\')\})?\[(?:"|\')(.+?)(?:"|\')\]\s*=\s*Array\((.+?)\);|smi', $res, $founds, PREG_SET_ORDER)) {
            foreach ($founds as $found) {
                $varname = $found[1];
                $funclist[$varname] = explode(',', $found[2]);
                $funclist[$varname] = array_map(function ($value) {
                    return trim($value, "'");
                }, $funclist[$varname]);

                $res = preg_replace_callback('|\$(?:\{(?:"|\'))?GLOBALS(?:(?:"|\')\})?\[\'' . $varname . '\'\]\[(\d+)\]|smi', function ($matches) use ($varname, $funclist) {
                    return $funclist[$varname][$matches[1]];
                }, $res);
            }
        }

        if (preg_match_all('|function\s*(\w{1,60})\(\$\w+\){\$\w{1,60}\s*=\s*Array\((.{1,30000}?)\);[^}]+}|smi', $res, $founds, PREG_SET_ORDER)) {
            foreach ($founds as $found) {
                $strlist = explode(',', $found[2]);
                $res = preg_replace_callback('|' . $found[1] . '\((\d+)\)|smi', function ($matches) use ($strlist) {
                    return $strlist[$matches[1]];
                }, $res);
            }
        }

        $res = preg_replace('~<\?(php)?\s*\?>~smi', '', $res);
        if (preg_match_all('~<\?\s*function\s*(_+(.{1,60}?))\(\$[_0-9]+\)\{\s*static\s*\$([_0-9]+)\s*=\s*(true|false);.{1,30000}?\$\3=array\((.*?)\);\s*return\s*base64_decode\(\$\3~smi', $res, $founds, PREG_SET_ORDER)) {
            foreach ($founds as $found) {
                $strlist = explode("',", $found[5]);
                $res = preg_replace_callback('|' . $found[1] . '\((\d+)\)|sm', function ($matches) use ($strlist) {
                    return $strlist[$matches[1]] . "'";
                }, $res);
            }
        }

        return $res;
    }

    /**
     * Calc.
     *
     * @param $expr
     *
     * @return mixed
     */
    private function calc($expr)
    {
        if (is_array($expr)) {
            $expr = $expr[0];
        }
        preg_match('~(min|max)?\(([^\)]+)\)~msi', $expr, $expr_arr);
        if ($expr_arr[1] == 'min' || $expr_arr[1] == 'max') {
            return $expr_arr[1](explode(',', $expr_arr[2]));
        } else {
            preg_match_all('~([\d\.]+)([\*\/\-\+])?~', $expr, $expr_arr);
            if (in_array('*', $expr_arr[2]) !== false) {
                $pos = array_search('*', $expr_arr[2]);
                $res = $expr_arr[1][$pos] * $expr_arr[1][$pos + 1];
                $expr = str_replace($expr_arr[1][$pos] . '*' . $expr_arr[1][$pos + 1], $res, $expr);
                $expr = $this->calc($expr);
            } elseif (in_array('/', $expr_arr[2]) !== false) {
                $pos = array_search('/', $expr_arr[2]);
                $res = $expr_arr[1][$pos] / $expr_arr[1][$pos + 1];
                $expr = str_replace($expr_arr[1][$pos] . '/' . $expr_arr[1][$pos + 1], $res, $expr);
                $expr = $this->calc($expr);
            } elseif (in_array('-', $expr_arr[2]) !== false) {
                $pos = array_search('-', $expr_arr[2]);
                $res = $expr_arr[1][$pos] - $expr_arr[1][$pos + 1];
                $expr = str_replace($expr_arr[1][$pos] . '-' . $expr_arr[1][$pos + 1], $res, $expr);
                $expr = $this->calc($expr);
            } elseif (in_array('+', $expr_arr[2]) !== false) {
                $pos = array_search('+', $expr_arr[2]);
                $res = $expr_arr[1][$pos] + $expr_arr[1][$pos + 1];
                $expr = str_replace($expr_arr[1][$pos] . '+' . $expr_arr[1][$pos + 1], $res, $expr);
                $expr = $this->calc($expr);
            } else {
                return $expr;
            }

            return $expr;
        }
    }

    /**
     * Eval.
     *
     * @param $matches
     *
     * @return bool|string
     */
    private function my_eval($matches)
    {
        $string = $matches[0];
        $string = substr($string, 5, strlen($string) - 7);

        return $this->decodeString($string);
    }

    /**
     * Decode string.
     *
     * @param $string
     * @param int $level
     *
     * @return bool|string
     */
    private function decodeString($string, $level = 0)
    {
        if (trim($string) == '') {
            return '';
        }
        if ($level > 100) {
            return '';
        }

        if (($string[0] == '\'') || ($string[0] == '"')) {
            return substr($string, 1, strlen($string) - 2);
        } elseif ($string[0] == '$') {
            $string = str_replace(')', '', $string);
            preg_match_all('~\\' . $string . '\s*=\s*(\'|")([^"\']+)(\'|")~msi', $this->full_code, $matches);

            return $matches[2][0];
        } else {
            $pos = strpos($string, '(');
            $function = substr($string, 0, $pos);

            $arg = $this->decodeString(substr($string, $pos + 1), $level + 1);
            if (strtolower($function) == 'base64_decode') {
                return @base64_decode($arg);
            } elseif (strtolower($function) == 'gzinflate') {
                return @gzinflate($arg);
            } elseif (strtolower($function) == 'gzuncompress') {
                return @gzuncompress($arg);
            } elseif (strtolower($function) == 'strrev') {
                return @strrev($arg);
            } elseif (strtolower($function) == 'str_rot13') {
                return @str_rot13($arg);
            } else {
                return $arg;
            }
        }
    }

    /**
     * Deobfuscate eval.
     *
     * @param $str
     *
     * @return mixed
     */
    private function deobfuscate_eval($str)
    {
        $res = preg_replace_callback('~eval\((base64_decode|gzinflate|strrev|str_rot13|gzuncompress).*?\);~msi', array(
            $this,
            'my_eval',
        ), $str);

        return str_replace($str, $res, $this->full_code);
    }

    /**
     * Get eval code.
     *
     * @param $string
     *
     * @return mixed|string
     */
    private function getEvalCode($string)
    {
        preg_match("/eval\((.*?)\);/", $string, $matches);

        return (empty($matches)) ? '' : end($matches);
    }

    /**
     * Get text inside quotes.
     *
     * @param $string
     *
     * @return mixed|string
     */
    private function getTextInsideQuotes($string)
    {
        if (preg_match_all('/("(.*?)")/', $string, $matches)) {
            return @end(end($matches));
        } elseif (preg_match_all('/(\'(.*?)\')/', $string, $matches)) {
            return @end(end($matches));
        } else {
            return '';
        }
    }

    /**
     * Deobfuscate lockit.
     *
     * @param $str
     *
     * @return string
     */
    private function deobfuscate_lockit($str)
    {
        $obfPHP = $str;
        $phpcode = base64_decode($this->getTextInsideQuotes($this->getEvalCode($obfPHP)));
        $hexvalues = $this->getHexValues($phpcode);
        $tmp_point = $this->getHexValues($obfPHP);
        $pointer1 = hexdec($tmp_point[0]);
        $pointer2 = hexdec($hexvalues[0]);
        $pointer3 = hexdec($hexvalues[1]);
        $needles = $this->getNeedles($phpcode);
        $needle = $needles[count($needles) - 2];
        $before_needle = end($needles);

        $phpcode = base64_decode(strtr(substr($obfPHP, $pointer2 + $pointer3, $pointer1), $needle, $before_needle));

        return "<?php {$phpcode} ?>";
    }

    /**
     * Get needles.
     *
     * @param $string
     *
     * @return array
     */
    private function getNeedles($string)
    {
        preg_match_all("/'(.*?)'/", $string, $matches);

        return (empty($matches)) ? array() : $matches[1];
    }

    /**
     * Get hex values.
     *
     * @param $string
     *
     * @return array
     */
    private function getHexValues($string)
    {
        preg_match_all('/0x[a-fA-F0-9]{1,8}/', $string, $matches);

        return (empty($matches)) ? array() : $matches[0];
    }

    /**
     * Deobfuscate als.
     *
     * @param $str
     *
     * @return string
     */
    private function deobfuscate_als($str)
    {
        preg_match('~__FILE__;\$[O0]+=[0-9a-fx]+;eval\(\$[O0]+\(\'([^\']+)\'\)\);return;~msi', $str, $layer1);
        preg_match('~\$[O0]+=(\$[O0]+\()+\$[O0]+,[0-9a-fx]+\),\'([^\']+)\',\'([^\']+)\'\)\);eval\(~msi', base64_decode($layer1[1]), $layer2);
        $res = explode('?>', $str);
        if (strlen(end($res)) > 0) {
            $res = substr(end($res), 380);
            $res = base64_decode(strtr($res, $layer2[2], $layer2[3]));
        }

        return "<?php {$res} ?>";
    }

    /**
     * Deobfuscate byterun.
     *
     * @param $str
     *
     * @return string
     */
    private function deobfuscate_byterun($str)
    {
        preg_match('~\$_F=__FILE__;\$_X=\'([^\']+)\';\s*eval\s*\(\s*\$?\w{1,60}\s*\(\s*[\'"][^\'"]+[\'"]\s*\)\s*\)\s*;~msi', $str, $matches);
        $res = base64_decode($matches[1]);
        $res = strtr($res, '123456aouie', 'aouie123456');

        return '<?php ' . str_replace($matches[0], $res, $this->full_code) . ' ?>';
    }

    /**
     * Deobfuscate urldecode.
     *
     * @param $str
     *
     * @return mixed
     */
    private function deobfuscate_urldecode($str)
    {
        preg_match('~(\$[O0_]+)=urldecode\("([%0-9a-f]+)"\);((\$[O0_]+=(\1\{\d+\}\.?)+;)+)~msi', $str, $matches);
        $alph = urldecode($matches[2]);
        $funcs = $matches[3];
        for ($i = 0; $i < strlen($alph); $i++) {
            $funcs = str_replace($matches[1] . '{' . $i . '}.', $alph[$i], $funcs);
            $funcs = str_replace($matches[1] . '{' . $i . '}', $alph[$i], $funcs);
        }

        $str = str_replace($matches[3], $funcs, $str);
        $funcs = explode(';', $funcs);
        foreach ($funcs as $func) {
            $func_arr = explode('=', $func);
            if (count($func_arr) == 2) {
                $func_arr[0] = str_replace('$', '', $func_arr[0]);
                $str = str_replace('${"GLOBALS"}["' . $func_arr[0] . '"]', $func_arr[1], $str);
            }
        }

        return $str;
    }

    /**
     * Format PHP.
     *
     * @param $string
     *
     * @return mixed
     */
    private function formatPHP($string)
    {
        $string = str_replace('<?php', '', $string);
        $string = str_replace('?>', '', $string);
        $string = str_replace(PHP_EOL, '', $string);
        $string = str_replace(';', ";\n", $string);

        return $string;
    }

    /**
     * deobfuscate fopo.
     *
     * @param $str
     *
     * @return bool|string
     */
    private function deobfuscate_fopo($str)
    {
        $phpcode = $this->formatPHP($str);
        $phpcode = base64_decode($this->getTextInsideQuotes($this->getEvalCode($phpcode)));
        @$phpcode = gzinflate(base64_decode(str_rot13($this->getTextInsideQuotes(end(explode(':', $phpcode))))));
        $old = '';
        while (($old != $phpcode) && (strlen(strstr($phpcode, '@eval($')) > 0)) {
            $old = $phpcode;
            $funcs = explode(';', $phpcode);
            if (count($funcs) == 5) {
                $phpcode = gzinflate(base64_decode(str_rot13($this->getTextInsideQuotes($this->getEvalCode($phpcode)))));
            } elseif (count($funcs) == 4) {
                $phpcode = gzinflate(base64_decode($this->getTextInsideQuotes($this->getEvalCode($phpcode))));
            }
        }

        return substr($phpcode, 2);
    }

    /**
     * Get obfuscation type.
     *
     * @param $str
     *
     * @return string|null
     */
    private function getObfuscateType($str)
    {
        $str = str_replace(array(".''", "''.", '.""', '"".'), '', $str);

        if (preg_match('~\$(?:\{(?:"|\'))?GLOBALS(?:(?:"|\')\})?\[\s*[\'"]_+\w{1,60}[\'"]\s*\]\s*=\s*\s*(?:array\s*\(|\[)\s*base64_decode\s*\(~msi', $str)) {
            return '_GLOBALS_';
        }
        if (preg_match('~function\s*_+\d+\s*\(\s*\$i\s*\)\s*{\s*\$a\s*=\s*(?:Array|\[)~msi', $str)) {
            return '_GLOBALS_';
        }
        if (preg_match('~__FILE__;\$[O0]+=[0-9a-fx]+;eval\(\$[O0]+\(\'([^\']+)\'\)\);return;~msi', $str)) {
            return 'ALS-Fullsite';
        }
        if (preg_match('~\$[O0]*=urldecode\(\'%66%67%36%73%62%65%68%70%72%61%34%63%6f%5f%74%6e%64\'\);\s*\$(?:(?:"|\')\})?GLOBALS(?:(?:"|\')\})?\[\'[O0]*\'\]=\$[O0]*~msi', $str)) {
            return 'LockIt!';
        }
        if (preg_match('~\$\w+="(\\\x?[0-9a-f]+){13}";@eval\(\$\w+\(~msi', $str)) {
            return 'FOPO';
        }
        if (preg_match('~\$_F=__FILE__;\$_X=\'([^\']+\');eval\(~ms', $str)) {
            return 'ByteRun';
        }
        if (preg_match('~(\$[O0_]+)=urldecode\("([%0-9a-f]+)"\);((\$[O0_]+=(\1\{\d+\}\.?)+;)+)~msi', $str)) {
            return 'urldecode_globals';
        }
        if (preg_match('~eval\((base64_decode|gzinflate|strrev|str_rot13|gzuncompress)~msi', $str)) {
            return 'eval';
        }

        return null;
    }
}