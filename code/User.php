<?php namespace Milkyway\SS\Shortcodes;

/**
 * Milkyway Multimedia
 * User.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Member;
use DBField;
use Email;

use CompositeField;
use FieldList;
use TextField;
use DropdownField;

class User implements Contract
{
    public function isAvailableForUse($member = null)
    {
        return true;
    }

    public function render($arguments, $caption = null, $parser = null)
    {
        if (!array_key_exists('field', $arguments) || !$arguments['field']) {
            return '';
        }

        $field = $arguments['field'];
        $member = Member::currentUser();

        if (!$member) {
            return '';
        }
        if (!$member->hasField($field)) {
            return '';
        }

        $value = $member->obj($field);

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

        if (Email::is_valid_address($value) && !isset($arguments['nolink'])) {
            return '<a href="mailto:' . $value . '">' . $caption . '</a>';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) && !isset($arguments['nolink'])) {
            return '<a href="' . $value . '">' . $caption . '</a>';
        }

        return $value;
    }

    public function code()
    {
        return ['user', 'current_user'];
    }

    public function title()
    {
        return [
            'user' => _t('Shortcodable.LOGGED_IN_MEMBER_SETTING', 'Logged-in member'),
        ];
    }

    public function formField()
    {
        $shortcodes = singleton('mwm')->map_array_to_i18n(Member::config()->valid_shortcode_fields, 'Member');
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
