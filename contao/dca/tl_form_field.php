<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Widget\ColsetEndWidget;
use HeimrichHannot\SubColumnsBootstrapBundle\Widget\ColsetPartWidget;
use HeimrichHannot\SubColumnsBootstrapBundle\Widget\ColsetStartWidget;

$dca = &$GLOBALS['TL_DCA']['tl_form_field'];

PaletteManipulator::create()
    ->addField('sc_columnset', 'colsettings_legend', PaletteManipulator::POSITION_PREPEND)
    ->removeField('fsc_type')
    ->applyToPalette(ColsetStartWidget::TYPE, 'tl_form_field')
    ->applyToPalette(ColsetPartWidget::TYPE, 'tl_form_field')
    ->applyToPalette(ColsetEndWidget::TYPE, 'tl_form_field')
;

$dca['fields']['sc_columnset'] = [
    'inputType'	=> 'select',
    'options_callback' => [ColumnsetContainer::class, 'getOptions'],
    'eval' => [
        'tl_class' => 'w50',
        'mandatory' => true,
        'chosen' => true,
    ],
    'sql' => "varchar(255) NOT NULL default ''"
];