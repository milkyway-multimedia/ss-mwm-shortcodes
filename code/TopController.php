<?php namespace Milkyway\SS\Shortcodes;

/**
 * Milkyway Multimedia
 * TopController.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Controller;
use CompositeField;
use FieldList;
use LiteralField;
use TextField;
use DropdownField;

class TopController implements Contract
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
        $curr = Controller::curr();

        if ($curr->hasField($field) || $curr->hasMethod($field)) {
            $value = $curr->hasMethod($field) ? $curr->$field() : $curr->obj($field);
        } else {
            if ($curr && !$curr->hasMethod('data')) {
                return '';
            }
            $page = $curr->data();

            if (!$page) {
                return '';
            }

            if (!$page->hasField($field) && !$page->hasMethod($field)) {
                return;
            }

            if (array_key_exists('type', $arguments)) {
                $type = $arguments['type'];
            } else {
                $type = 'Nice';
            }

            $value = $page->hasMethod($field) ? $page->$field() : $page->obj($field)->$type();
        }

        if (!$value && isset($fields['default'])) {
            $value = $parser->parse($fields['default']);
        }

        return $value;
    }

    public function code()
    {
        return ['current_page', 'top_controller'];
    }

    public function title()
    {
        return [
            'current_page' => _t('Shortcodable.CURRENT_PAGE', 'Current page'),
        ];
    }

    public function formField()
    {
        return CompositeField::create(
            FieldList::create(
                LiteralField::create(
                    'NOTE-ADVANCED',
                    '<p class="message info">' .
                    _t(
                        'Shortcodable.NOTE-ADVANCED',
                        'Note: This is an advanced shortcode. It is recommended to use only use the suggested codes.'
                    )
                    . '</p>'
                ),
                TextField::create(
                    'field',
                    _t('Shortcodable.FIELD', 'Field')
                )->setDescription(
                    _t(
                        'Shortcodable.DESC-CurrentPageField',
                        'Suggested codes: Title, Subtitle, Content, Link, AbsoluteLink'
                    )
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
                )
            )
        );
    }
} 