<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('subcolumns_legend', 'config_legend')
    ->addField('subcolumns', 'subcolumns_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_theme')
;

$GLOBALS['TL_DCA']['tl_theme']['fields']['subcolumns'] = [
    'inputType' => 'select',
    'options_callback' => ['tl_subcolumnsCallback', 'getSets'],
    'eval' => [
        'includeBlankOption' => true,
        'tl_class' => 'w50'
    ],
    'sql' => 'varchar(255) default ""'
];
