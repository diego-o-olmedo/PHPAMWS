<?php

/**
 * PHP Antimalware Scanner.
 *
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2020
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 *
 * @see https://github.com/marcocesarato/PHP-Antimalware-Scanner
 */

namespace marcocesarato\amwscan;

use ArrayAccess;
use Closure;

/**
 * Class Argv.
 */
class Argv implements ArrayAccess
{
    /**
     * @var null
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var array
     */
    protected $examples = array();
    /**
     * @var array
     */
    protected $flags = array();
    /**
     * @var array
     */
    protected $args = array();
    /**
     * @var array
     */
    protected $parsedFlags = array();
    /**
     * @var array
     */
    protected $parsedNamedArgs = array();
    /**
     * @var array
     */
    protected $parsedArgs = array();

    /**
     * Build.
     *
     * @param $callback
     *
     * @return static
     */
    public static function build($callback)
    {
        $parser = new static();
        $bindTo = array($callback, 'bindTo');
        if ($callback instanceof Closure and is_callable($bindTo)) {
            $callback = $callback->bindTo($parser);
        }
        call_user_func($callback, $parser);

        return $parser;
    }

    /**
     * Argv constructor.
     *
     * @param string $description
     * @param null $name
     * @param array $examples
     */
    public function __construct($description = '', $name = null, $examples = array())
    {
        $this->description = $description;
        $this->name = $name;
        $this->examples = $examples;
    }

    /**
     * Parse argvs.
     *
     * @param null $args
     */
    public function parse($args = null)
    {
        if (empty($args)) {
            $args = array_slice($_SERVER['argv'], 1); // First argument removed (php [index.php.php] [<path>] [<functions>])
        }

        foreach ($args as $pos => $arg) {
            // reset value
            $value = null;
            if (substr($arg, 0, 1) === '-') {
                if (preg_match('/^(.+)=(?:\"|\\\')?(.+)(?:\"|\\\')?/', $arg, $matches)) {
                    $arg = $matches[1];
                    $value = $matches[2];
                }
                if (!$flag = @$this->flags[$arg]) {
                    return;
                }
                unset($args[$pos]);
                if ($flag->hasValue) {
                    if (!isset($value)) {
                        $value = $args[$pos + 1];
                        unset($args[$pos + 1]);
                    }
                } else {
                    $value = true;
                }
                if (null !== $flag->callback) {
                    call_user_func_array($flag->callback, array(&$value));
                }
                // Set the reference given as the flag's 'var'.
                $flag->var = $this->parsedFlags[$flag->name] = $value;
            }
        }
        foreach ($this->flags as $flag) {
            if (!array_key_exists($flag->name, $this->parsedFlags)) {
                $flag->var = $this->parsedFlags[$flag->name] = $flag->defaultValue;
            }
        }
        $this->parsedArgs = $args = array_values($args);
        $pos = 0;
        foreach ($this->args as $arg) {
            if ($arg->required and !isset($args[$pos])) {
                return;
            }
            if (isset($args[$pos])) {
                if ($arg->vararg) {
                    $value = array_slice($args, $pos);
                    $pos += count($value);
                } else {
                    $value = $args[$pos];
                    $pos++;
                }
            } else {
                $value = $arg->defaultValue;
            }
            $this->parsedNamedArgs[$arg->name] = $value;
        }
    }

    /**
     * Add Flag.
     *
     * @param $name
     * @param array $options
     * @param null $callback
     *
     * @return $this
     */
    public function addFlag($name, $options = array(), $callback = null)
    {
        $flag = new Flag($name, $options, $callback);
        foreach ($flag->aliases as $alias) {
            $this->flags[$alias] = $flag;
        }

        return $this;
    }

    /**
     * Add flag var.
     *
     * @param $name
     * @param $var
     * @param array $options
     *
     * @return Argv
     */
    public function addFlagVar($name, &$var, $options = array())
    {
        $options['var'] = &$var;

        return $this->addFlag($name, $options);
    }

    /**
     * Add Argument.
     *
     * @param $name
     * @param array $options
     *
     * @return $this
     */
    public function addArgument($name, $options = array())
    {
        $arg = new Argument($name, $options);
        $this->args[] = $arg;

        return $this;
    }

    /**
     * Get arguments.
     *
     * @return array
     */
    public function args()
    {
        return $this->parsedArgs;
    }

    /**
     * Count arguments.
     *
     * @return int
     */
    public function count()
    {
        return count($this->args());
    }

    /**
     * Get flag or argument.
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->flag($name) ?: $this->arg($name);
    }

    /**
     * Get argument from position.
     *
     * @param $pos
     *
     * @return mixed
     */
    public function arg($pos)
    {
        if (array_key_exists($pos, $this->parsedNamedArgs)) {
            return $this->parsedNamedArgs[$pos];
        }
        if (array_key_exists($pos, $this->parsedArgs)) {
            return $this->parsedArgs[$pos];
        }

        return null;
    }

    /**
     * Get flag.
     *
     * @param $name
     *
     * @return mixed
     */
    public function flag($name)
    {
        if (array_key_exists($name, $this->parsedFlags)) {
            return $this->parsedFlags[$name];
        }

        return null;
    }

    /**
     * Usage.
     *
     * @return string
     */
    public function usage()
    {
        $flags = implode(' ', array_unique(array_values($this->flags)));
        $args = implode(' ', $this->args);
        $script = $this->name ?: 'php ' . basename($_SERVER['SCRIPT_NAME']);
        $usage = "Usage: $script $flags $args";
        if ($this->examples) {
            $usage .= "\n\nExamples\n\n" . implode("\n", $this->examples);
        }
        if ($this->description) {
            $usage .= "\n\n{$this->description}";
        }

        return $usage;
    }

    /**
     * @param $start
     * @param null $length
     *
     * @return array
     */
    public function slice($start, $length = null)
    {
        return array_slice($this->parsedArgs, $start, $length);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return null !== $this->get($offset);
    }

    /**
     * Set Offest.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Unset Offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
    }
}
