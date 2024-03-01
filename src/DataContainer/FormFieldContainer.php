<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\FormField\FormColStart;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class FormFieldContainer extends AbstractColsetContainer
{
    const TABLE = 'tl_form_field';

    public static function getTable(): string
    {
        return static::TABLE;
    }
}