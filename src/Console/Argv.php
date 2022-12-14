<?php
/**
 * PHP Antimalware Scanner.
 *
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 *
 * @see https://github.com/marcocesarato/PHP-Antimalware-Scanner
 */

namespace AMWScan\Console;

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
    protected $examples = [];
    /**
     * @var array
     */
    protected $flags = [];
    /**
     * @var array
     */
    protected $args = [];
    /**
     * @var array
     */
    protected $parsedFlags = [];
    /**
     * @var array
     */
    protected $parsedNamedArgs = [];
    /**
     * @var array
     */
    protected $parsedArgs = [];

    /**
     * Build.
     *
     * @param $callback
     *
     * @return static
     */
    public static function build($callback)
    {
        $static = new static();
        $bindTo = [$callback, 'bindTo'];
        if ($callback instanceof Closure && is_callable($bindTo)) {
            $callback = $callback->bindTo($static);
        }
        $callback($static);

        return $static;
    }

    /**
     * Argv constructor.
     *
     * @param string $description
     * @param null $name
     * @param array $examples
     */
    public function __construct($name = '', $description = '', $examples = [])
    {
        $this->description = $description;
        $this->name = $name;
        $this->examples = $examples;
    }

    /**
     * @param array $examples
     */
    public function setExamples($examples)
    {
        $this->examples = $examples;
    }

    /**
     * Parse argvs.
     *
     * @param array $args
     */
    public function parse($args = [])
    {
        if (empty($args) && !empty($_SERVER['argv'])) {
            $args = array_slice($_SERVER['argv'], 1); // First argument removed (php [scanner.php] [<path>] [<functions>])
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
                    call_user_func_array($flag->callback, [&$value]);
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
            if ($arg->required && !isset($args[$pos])) {
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
    public function addFlag($name, $options = [], $callback = null)
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
    public function addFlagVar($name, &$var, $options = [])
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
    public function addArgument($name, $options = [])
    {
        $argument = new Argument($name, $options);
        $this->args[] = $argument;

        return $this;
    }

    /**
     * Get arguments.
     *
     * @return array
     */
    public function getParsedArgs()
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
        return count($this->getParsedArgs());
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

        $usage = '';
        if ($this->description !== '') {
            $usage .= "\n\n{$this->description}";
        }

        if (count($this->args) > 0) {
            $usage .= "\n\nArguments:\n";
            $printArgs = [];
            $length = 0;
            foreach ($this->args as $arg) {
                $key = "<{$arg->name}>";
                if ($length < strlen($key)) {
                    $length = strlen($key);
                }
                $printArgs[$key] = CLI::wordWrap($arg->help);
                if ($arg->defaultValue !== null) {
                    $printArgs[$key] .= "\n[default: {$arg->defaultValue}]";
                }
            }
            $length += 3;
            ksort($printArgs);
            foreach ($printArgs as $name => $description) {
                $name = str_pad($name, $length, ' ', STR_PAD_RIGHT);
                $spaces = str_repeat(' ', $length + 2);
                $description = '- ' . str_replace("\n", "\n" . $spaces, $description);
                $usage .= "\n{$name}{$description}";
            }
        }

        if (count($this->flags) > 0) {
            $usage .= "\n\nFlags:\n";
            $printFlags = [];
            $length = 0;
            foreach ($this->flags as $flag) {
                $names = ['--' . $flag->name];
                if (!empty($flag->aliases)) {
                    $names = array_merge($flag->aliases);
                }
                usort($names, function ($a, $b) {
                    return strlen($b) - strlen($a);
                });

                $key = implode('|', $names);
                if ($flag->hasValue) {
                    $valueName = empty($flag->valueName) ? $flag->name : $flag->valueName;
                    $key .= ' <' . $valueName . '>';
                }
                if ($length < strlen($key)) {
                    $length = strlen($key);
                }
                $printFlags[$key] = CLI::wordWrap($flag->help);
                if ($flag->hasValue && $flag->defaultValue !== null && $flag->defaultValue !== false) {
                    $printFlags[$key] .= "\n[default: {$flag->defaultValue}]";
                }
            }
            $length += 3;
            ksort($printFlags);
            foreach ($printFlags as $name => $description) {
                $name = str_pad($name, $length, ' ', STR_PAD_RIGHT);
                $spaces = str_repeat(' ', $length + 2);
                $description = '- ' . str_replace("\n", "\n" . $spaces, $description);
                $usage .= "\n{$name}{$description}";
            }
        }

        $example = CLI::wordWrap("$script $flags $args");
        $example = str_replace("\n", "\n\t", $example);

        $usage .= "\n\nUsage: $example";

        if ($this->examples !== []) {
            $usage .= "\n\nExamples:\n\n" . implode("\n", $this->examples);
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
