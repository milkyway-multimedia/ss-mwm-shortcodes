<?php namespace Milkyway\SS\Shortcodes\Extensions;

/**
 * Milkyway Multimedia
 * ShortcodableController.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Extension;
use Object;
use ShortcodeParser;

class ShortcodableController extends Extension
{
    public static function register()
    {
        static::executeOnShortcodeList(function ($shortcode, $code) {
            ShortcodeParser::get('default')->register($code, [$shortcode, 'render']);
        });
    }

    protected function registerForForm()
    {
        $this->executeOnShortcodeList(function ($shortcode, $code) {
            singleton('ShortcodableParser')->register($code);
        });
    }

    public function updateShortcodeForm($form)
    {
        $this->registerForForm();

        $classname = false;
        $shortcodeData = false;

        if ($shortcode = $this->owner->Request->requestVar('Shortcode')) {
            $shortcodeData = singleton('ShortcodableParser')->the_shortcodes([], $shortcode);
            if (isset($shortcodeData[0])) {
                $shortcodeData = $shortcodeData[0];
                $classname = $shortcodeData['name'];
            }
        } else {
            $classname = $this->owner->Request->requestVar('ShortcodeType');
        }

        if ($types = $form->Fields()->fieldByName('ShortcodeType')) {
            $types->setTitle(_t('Shortcodable.SHORTCODE_TYPE', 'Shortcode type'));

            $source = $types->Source;

            $this->executeOnShortcodeList(function ($shortcode) use (&$source) {
                $source = array_merge(
                    $source,
                    $shortcode->title()
                );
            });

            natsort($source);

            $types->setSource($source);

            if ($classname) {
                $types->setValue($classname);
            }

            if ($currentShortcode = $types->Value()) {
                $this->executeOnShortcodeList(function ($shortcode, $code) use ($currentShortcode, $form) {
                    if ($code == $currentShortcode) {
                        $form->Fields()->push($shortcode->formField()->setForm($form)->addExtraClass('attributes-composite'));
                    }
                });
            }
        }

        if ($shortcodeData && isset($shortcodeData['atts'])) {
            $form->loadDataFrom($shortcodeData['atts']);
        }
    }

    protected static function executeOnShortcodeList($callback)
    {
        foreach (
            array_diff(
                (array)\Config::inst()->forClass('ShortcodeParser')->providers,
                (array)\Config::inst()->forClass('ShortcodeParser')->disabled
            ) as $shortcodeClass) {
            $shortcode = Object::create($shortcodeClass);

            foreach ((array)$shortcode->code() as $code) {
                if ($shortcode->isAvailableForUse()) {
                    $callback($shortcode, $code, $shortcodeClass);
                }
            }
        }
    }
}
