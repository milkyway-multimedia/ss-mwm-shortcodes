<?php namespace Milkyway\SS\Shortcodes;

/**
 * Milkyway Multimedia
 * Contract.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

interface Contract
{
    public function isAvailableForUse($member = null);

    public function render($arguments, $caption = null, $parser = null);

    public function code();

    public function title();

    public function formField();
}