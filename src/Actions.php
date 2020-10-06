<?php

namespace marcocesarato\amwscan;

class Actions
{
    /**
     * Clean Evil Code.
     *
     * @param $code
     * @param $pattern_found
     *
     * @return string|string[]|null
     */
    public static function cleanEvilCode($code, $pattern_found)
    {
        foreach ($pattern_found as $pattern) {
            preg_match('/(<\?php)(.*?)(' . preg_quote($pattern['match'], '/') . '[\s\r\n]*;?)/si', $code, $match);
            $match[2] = trim($match[2]);
            $match[4] = trim($match[4]);
            if (!empty($match[2]) || !empty($match[4])) {
                $code = str_replace($match[0], $match[1] . $match[2] . $match[4] . $match[5], $code);
            } else {
                $code = str_replace($match[0], '', $code);
            }
            $code = preg_replace('/<\?php[\s\r\n]*\?>/si', '', $code);
        }

        return $code;
    }

    /**
     * Clean Evil Code Line.
     *
     * @param $code
     * @param $pattern_found
     *
     * @return string
     */
    public static function cleanEvilCodeLine($code, $pattern_found)
    {
        $lines = explode(PHP_EOL, $code);
        foreach ($pattern_found as $pattern) {
            unset($lines[(int)$pattern['line'] - 1]);
        }
        $code = implode(PHP_EOL, $lines);

        return $code;
    }

    /**
     * Delete File.
     *
     * @param $file
     *
     * @return bool
     */
    public static function deleteFile($file)
    {
        return unlink($file);
    }

    /**
     * Move to Quarantine.
     *
     * @param $file
     *
     * @return string
     */
    public static function moveToQuarantine($file)
    {
        $quarantine = Application::getPathQuarantine() . str_replace(realpath(Application::currentDirectory()), '', $file);
        if (!is_dir(dirname($quarantine))) {
            if (!mkdir($concurrentDirectory = dirname($quarantine), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        rename($file, $quarantine);

        return $quarantine;
    }

    /**
     * Add to Whitelist.
     *
     * @param $file
     * @param $pattern_found
     *
     * @return false|int
     */
    public static function addToWhitelist($file, $pattern_found)
    {
        $whitelist = Application::getWhitelist();
        foreach ($pattern_found as $key => $pattern) {
            $exploit = $pattern['key'];
            $lineNumber = $pattern['line'];
            $match = $pattern['match'];
            $fileName = str_replace(Application::getPathScan(), '', $file);
            $key = md5($exploit . $fileName . $lineNumber);
            $whitelist[$key] = array(
                'file' => $fileName,
                'exploit' => $exploit,
                'line' => $lineNumber,
                'match' => $match,
            );
        }
        Application::setWhitelist($whitelist);

        return file_put_contents(Application::getPathWhitelist(), json_encode($whitelist));
    }

    /**
     * Open with VIM.
     *
     * @param $file
     */
    public static function openWithVim($file)
    {
        $descriptors = array(
            array('file', '/dev/tty', 'r'),
            array('file', '/dev/tty', 'w'),
            array('file', '/dev/tty', 'w'),
        );
        $process = proc_open("vim '$file'", $descriptors, $pipes);
        while (true) {
            $proc_status = proc_get_status($process);
            if ($proc_status['running'] == false) {
                break;
            }
        }
    }

    /**
     * Open with Nano.
     *
     * @param $file
     */
    public static function openWithNano($file)
    {
        $descriptors = array(
            array('file', '/dev/tty', 'r'),
            array('file', '/dev/tty', 'w'),
            array('file', '/dev/tty', 'w'),
        );
        $process = proc_open("nano '$file'", $descriptors, $pipes);
        while (true) {
            $proc_status = proc_get_status($process);
            if ($proc_status['running'] == false) {
                break;
            }
        }
    }
}
