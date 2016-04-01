<?php namespace Milkyway\SS\Shortcodes\Extras;

/**
 * Milkyway Multimedia
 * Parser.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use TextParser as Original;
use ShortcodeParser;

class TextParser extends Original {
    public function parse() {
        return ShortcodeParser::get_active()->parse($this->content);
    }
}