<?php namespace Milkyway\SS\Shortcodes;

/**
 * Milkyway Multimedia
 * SiteConfig.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author  Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\Utilities;
use Milkyway\SS\Core\Director;

use DBField;
use Email;
use CompositeField;
use FieldList;
use DropdownField;
use TextField;

class SiteConfig implements Contract
{
    public function isAvailableForUse($member = null)
    {
        return class_exists('SiteConfig');
    }

    public function render($arguments, $caption = null, $parser = null)
    {
        if (!array_key_exists('field', $arguments) || !$arguments['field']) {
            return '';
        }

        $field = $arguments['field'];
        $siteConfig = \SiteConfig::current_site_config();

        if (!$siteConfig->hasField($field)) {
            return '';
        }

        $value = $siteConfig->obj($field);

        if ($value instanceof DBField) {
            if (isset($arguments['type']) && $value->hasMethod($arguments['type'])) {
                $cast = $arguments['type'];
            } else {
                $cast = 'Nice';
            }

            $value = $value->$cast();
        }

        if ($parser) {
            $caption = $parser->parse($caption);
            $value = $parser->parse($value);
        }

        if (isset($arguments['caption'])) {
            $caption = $arguments['caption'];
        }

        if (!$caption) {
            $caption = $value;
        }

        if (!$value && isset($fields['default'])) {
            $value = $parser->parse($fields['default']);
        }

        if (Email::validEmailAddress($value) && !isset($arguments['nolink'])) {
            return '<a href="mailto:' . $value . '">' . $caption . '</a>';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) && !isset($arguments['nolink'])) {
            return '<a href="' . Director::absoluteURL($value) . '">' . $caption . '</a>';
        }

        return $value;
    }

    public function code()
    {
        return ['site_config', 'setting'];
    }

    public function title()
    {
        return [
            'setting' => _t('Shortcodable.SITE_SETTING', 'Site setting'),
        ];
    }

    public function formField()
    {
        $shortcodes = Utilities::map_array_to_i18n(
            \SiteConfig::config()->valid_shortcode_fields,
            'SiteConfig'
        );

        natsort($shortcodes);

        return
            CompositeField::create(
                FieldList::create(
                    DropdownField::create(
                        'field',
                        _t('Shortcodable.FIELD', 'Field'),
                        $shortcodes
                    ),
                    TextField::create(
                        'default',
                        _t('Shortcodable.DEFAULT_VALUE', 'Default Value')
                    ),
                    DropdownField::create(
                        'type',
                        _t('Shortcodable.DISPLAY_TYPE', 'Display type'),
                        [
                            '' => 'Nice',
                        ]
                    ),
                    TextField::create(
                        'caption',
                        _t('Shortcodable.CAPTION', 'Caption')
                    )->setDescription(
                        _t(
                            'Shortcodable.DESC-CAPTION',
                            'Only used for values that will resolve to links'
                        )
                    ),
                    DropdownField::create(
                        'nolink',
                        _t('Shortcodable.NO_AUTO_LINK', 'Autolink'),
                        [
                            '' => 'Yes',
                            '1' => 'No',
                        ]
                    )
                )
            );
    }
} 