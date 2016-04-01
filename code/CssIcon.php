<?php namespace Milkyway\SS\Shortcodes;

/**
 * Milkyway Multimedia
 * CssIcon.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author  Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use HTMLEditorField;
use DropdownField;
use TextField;
use ReadonlyField;
use CompositeField;
use FieldList;
use ArrayData;
use Requirements;

class CssIcon implements Contract
{
    public static $additional_classes = 'fa';

    public static $default_classes = 'fa-';

    public static $use_icon_picker = true;

    public static $include_font_css = true;

    public static $insert_icon_name_as_class = true;

    public function isAvailableForUse($member = null)
    {
        return true;
    }

    public function render($arguments, $caption = null, $parser = null)
    {
        $content = isset($arguments['use']) ? $arguments['use'] : $caption;

        if (!$content) {
            return '';
        }

        if (static::$include_font_css) {
            Requirements::css(singleton('env')->get('CDN.font-awesome',
                'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'
            ));
        }

        $prepend = HTMLEditorField::config()->prepend_icon;

        if (!$prepend && isset($arguments['prepend']) && $arguments['prepend']) {
            $prepend = $arguments['prepend'];
        } elseif (!$prepend) {
            $prepend = static::$default_classes;
        }

        if(static::$insert_icon_name_as_class && strpos($content, $prepend) === 0)
            $prepend = '';

        $prepend = static::$additional_classes . ' ' . $prepend;

        if (isset($arguments['classes']) && $arguments['classes']) {
            $prepend = $arguments['classes'] . ' ' . $prepend;
        }

        return ArrayData::create([
            'iconContent' => static::$insert_icon_name_as_class ? '' : $content,
            'iconClass' => static::$insert_icon_name_as_class ? $prepend . $content : $prepend,
        ])->renderWith('css-icon');
    }

    public function code()
    {
        return ['icon', 'css_icon'];
    }

    public function title()
    {
        return [
            'icon' => _t('Shortcodable.ICON', 'Icon'),
        ];
    }

    public function formField()
    {
        if(class_exists('FontAwesomeIconPickerField') && static::$use_icon_picker) {
            $icon = \FontAwesomeIconPickerField::create(
                'use',
                _t('Shortcodable.ICON', 'Icon')
            );

            $icon
                ->addExtraClass('icp--css-icon-shortcode')
                ->setAttribute('data-placement', 'bottomLeft');

            if($validIcons = HTMLEditorField::config()->valid_icon_shortcodes)
                $icon->setAttribute('data-icons', json_encode(array_keys($validIcons)));
        }
        else if (HTMLEditorField::config()->valid_icon_shortcodes) {
            $icon = DropdownField::create(
                'use',
                _t('Shortcodable.ICON', 'Icon'),
                singleton('mwm')->map_array_to_i18n(HTMLEditorField::config()->valid_icon_shortcodes, 'Icon')
            );
        } else {
            $icon = TextField::create('use', _t('Shortcodable.ICON', 'Icon'));
        }

        if (HTMLEditorField::config()->prepend_icon) {
            $iconPrepend = ReadonlyField::create(
                'prepend',
                _t('Shortcodable.ICON_PREPEND', 'Prepend'),
                HTMLEditorField::config()->prepend_icon
            );
        } else {
            $iconPrepend = TextField::create(
                'prepend',
                _t('Shortcodable.ICON_PREPEND', 'Prepend')
            )->setAttribute('placeholder', static::$default_classes);
        }

        if (HTMLEditorField::config()->valid_icon_classes) {
            $iconClasses = DropdownField::create(
                'classes',
                _t('Shortcodable.ICON_CLASSES', 'Classes'),
                array_combine(
                    HTMLEditorField::config()->valid_icon_classes,
                    HTMLEditorField::config()->valid_icon_classes
                )
            );
        } else {
            $iconClasses = TextField::create('classes', _t('Shortcodable.ICON_CLASSES', 'Classes'));
        }

        return
            CompositeField::create(
                FieldList::create(
                    $icon,
                    $iconPrepend,
                    $iconClasses
                )
            );
    }
}
