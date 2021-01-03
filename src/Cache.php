<?php

/**
 * PHP Antimalware Scanner.
 *
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 *
 * @see https://github.com/marcocesarato/PHP-Antimalware-Scanner
 */

namespace marcocesarato\amwscan;

use marcocesarato\amwscan\Abstracts\SingletonAbstract;
use RuntimeException;

class Cache extends SingletonAbstract
{
    protected static $cache;
    protected static $ext = 'cache';
    protected static $DS = DIRECTORY_SEPARATOR;

    protected $tempdir;

    /**
     * Cache constructor.
     */
    protected function __construct()
    {
        parent::__construct();
        $tempdir = sys_get_temp_dir() . self::$DS . Scanner::getName() . self::$DS;

        if (!is_dir($tempdir) && (!mkdir($tempdir, 0777, true) && !is_dir($tempdir))) {
            throw new RuntimeException(sprintf('Temp Directory "%s" was not created', $tempdir));
        }

        $this->tempdir = $tempdir;
    }

    /**
     * Set cache item.
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = 3600)
    {
        $key = $this->key($key);

        if ($data = json_encode(['ttl' => $ttl > 0 ? time() + $ttl : $ttl,  'data' => $value])) {
            if (!Scanner::isCacheEnabled()) {
                self::$cache[$key] = $data;

                return true;
            }

            if (file_put_contents($this->tempdir . $key, $data) !== false) {
                self::$cache[$key] = $data;

                return true;
            }
        }

        return false;
    }

    /**
     * Update time to live.
     *
     * @param string $key
     * @param int $ttl
     *
     * @return bool
     */
    public function touch($key, $ttl = 3600)
    {
        if (!Scanner::isCacheEnabled()) {
            return true;
        }

        if ($data = $this->get($key)) {
            return $this->set($key, $data, $ttl);
        }

        return false;
    }

    /**
     * Get cache item.
     *
     * @param string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $key = $this->key($key);
        $file = $this->tempdir . $key;

        $data = false;
        if (isset(self::$cache[$key])) {
            $data = self::$cache[$key];
        } elseif (Scanner::isCacheEnabled() && is_file($file)) {
            $data = file_get_contents($file);
            self::$cache[$key] = $data;
        }

        if ($data !== false) {
            if (!empty($data)) {
                $data = json_decode($data, true);

                if (isset($data['ttl'], $data['data'])) {
                    if ($data['ttl'] <= 0 || $data['ttl'] >= time()) {
                        return $data['data'];
                    }

                    $this->deleteItem($file);
                }
            } else {
                $this->deleteItem($file);
            }
        }

        return $default;
    }

    /**
     * Clean cache.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->deleteRegex('*');
    }

    /**
     * Delete cache item.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete($key)
    {
        $key = $this->key($key);

        return $this->deleteItem($this->tempdir . $key);
    }

    /**
     * Delete item cache by regex.
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function deleteRegex($pattern = '*')
    {
        $return = true;

        foreach (glob($this->tempdir . $pattern, GLOB_NOSORT | GLOB_BRACE) as $cacheFile) {
            if (!$this->deleteItem($cacheFile)) {
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Generate key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function key($key)
    {
        $filename = $key . '.' . self::$ext;
        $specialChars = ['?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}'];
        $filename = str_replace($specialChars, '', $filename);
        $filename = preg_replace('/[\s-]+/', '-', $filename);

        return trim($filename, '.-_');
    }

    /**
     * Delete a item.
     *
     * @param string $item
     */
    private function deleteItem($item)
    {
        unset(self::$cache[basename($item)]);

        clearstatcache(true, $item);

        if (file_exists($item)) {
            if (!unlink($item)) {
                return file_put_contents($item, '') !== false;
            }

            clearstatcache(true, $item);
        }

        return true;
    }
}
