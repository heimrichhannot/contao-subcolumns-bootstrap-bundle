<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class FormFieldContainer
{
    /**
     * @Callback(table="tl_form_field", target="fields.fsc_type.options")
     */
    public function onFscTypeOptionsCallback()
    {



        if (!SubColumnsBootstrapBundle::validProfile($GLOBALS['TL_CONFIG']['subcolumns'])) {
            $sc = new \tl_form_subcols();
            return @$sc->getAllTypes();
        }

        $db = Database::getInstance();
        $collection = $db->execute('SELECT columns FROM tl_columnset GROUP BY columns ORDER BY columns');

        $types = [];

        while ($collection->next()) {
            $types[] = $collection->columns;
        }

        return $types;
    }
}