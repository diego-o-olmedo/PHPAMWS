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

use FilesystemIterator;

class Figlet
{
    /**
     * Path fonts.
     *
     * @var string
     */
    public static $pathFonts;

    /**
     * Signature.
     *
     * @var string
     */
    protected $signature;
    /**
     * Hard blank.
     *
     * @var string
     */
    protected $hardblank;
    /**
     * Height.
     *
     * @var string
     */
    protected $height;
    /**
     * Baseline.
     *
     * @var string
     */
    protected $baseline;
    /**
     * Max lenght.
     *
     * @var string
     */
    protected $maxLenght;
    /**
     * Old layout.
     *
     * @var string
     */
    protected $oldLayout;
    /**
     * Comment line.
     *
     * @var int
     */
    protected $commentLines;
    /**
     * Print direction.
     *
     * @var string
     */
    protected $printDirection;
    /**
     * Full layout.
     *
     * @var string
     */
    protected $fullLayout;
    /**
     * Code tag count.
     *
     * @var string
     */
    protected $codeTagCount;
    /**
     * Font file.
     *
     * @var string
     */
    protected $fontFile;

    /**
     * Figlet constructor.
     */
    public function __construct()
    {
        self::$pathFonts = __DIR__ . '/Fonts';
    }

    /**
     * Load a random flf font file.
     */
    public function loadRandomFont()
    {
        $font = null;
        $fonts = new FilesystemIterator(
            self::$pathFonts,
            FilesystemIterator::SKIP_DOTS
        );
        $i = mt_rand(0, iterator_count($fonts) - 1);
        $c = 0;
        foreach ($fonts as $file) {
            if ($i === $c) {
                $font = $file->getPathname();
                break;
            }
            $c++;
        }

        return $this->loadfont($font);
    }

    /**
     * Load an flf font file. Return true on success, false on error.
     *
     * @param $fontfile
     *
     * @return bool
     */
    public function loadFont($fontfile)
    {
        if (!is_file($fontfile)) {
            $fontfile = self::$pathFonts . '/' . $fontfile;
        }
        $this->fontFile = file($fontfile);
        if (!$this->fontFile) {
            trigger_error("Couldn't open font $fontfile\n");

            return false;
        }

        // Header
        $header = explode(' ', $this->fontFile[0]);

        $this->signature = substr($header[0], 0, -1);
        $this->hardblank = $header[0][strlen($header[0]) - 1];
        $this->height = @$header[1];
        $this->baseline = @$header[2];
        $this->maxLenght = @$header[3];
        $this->oldLayout = @$header[4];
        $this->commentLines = ((int)@$header[5]) + 1;
        $this->printDirection = @$header[6];
        $this->fullLayout = @$header[7];
        $this->codeTagCount = @$header[8];

        if ($this->signature !== 'flf2a') {
            trigger_error('Unknown font version ' . $this->signature . "\n");

            return false;
        }

        return true;
    }

    /**
     * Get a character as a string, or an array with one line
     * for each font height.
     *
     * @param $character
     *
     * @return array|string
     */
    public function getCharacter($character)
    {
        $asciValue = ord($character);
        $start = $this->commentLines + ($asciValue - 32) * $this->height;
        $data = [];

        for ($a = 0; $a < $this->height; $a++) {
            $tmp = $this->fontFile[$start + $a];
            $separator = substr(trim($tmp), -1);
            $tmp = str_replace($this->hardblank, ' ', $tmp);
            $tmp = preg_replace('/' . preg_quote($separator, '/') . '+$/s', '', $tmp);

            $data[] = $tmp;
        }

        return $data;
    }

    /**
     * Returns a figletized line of characters.
     *
     * @param $line
     *
     * @return string
     */
    public function render($line)
    {
        $ret = '';
        $data = [];

        for ($i = 0; $i < (strlen($line)); $i++) {
            $data[] = $this->getCharacter($line[$i]);
        }

        for ($i = 0; $i < $this->height; $i++) {
            foreach ($data as $v) {
                $ret .= str_replace("\n", '', $v[$i]);
            }
            reset($data);
            $ret .= "\n";
        }

        return trim($ret);
    }
}
