<?php

use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ContentContainer;

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['config']['onload_callback'][] = [ColumnSet::class, 'appendColumnsetIdToPalette'];
$dca['config']['onload_callback'][] = [ContentContainer::class, 'createPalette'];
$dca['config']['onsubmit_callback'][] = [ColumnsetContainer::class, 'onUpdate'];
$dca['config']['onsubmit_callback'][] = [ContentContainer::class, 'setElementProperties'];
$dca['config']['ondelete_callback'][] = [ColumnsetContainer::class, 'onDelete'];
$dca['config']['oncopy_callback'][] = [ColumnsetContainer::class, 'onCopy'];

/**
 * Fields
 */
$fields = [
    'sc_name' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_name'],
        'inputType' => 'text',
        'save_callback' => [[ContentContainer::class, 'scName_onSaveCallback']],
        'eval' => [
            'maxlength' => '255',
            'unique' => true,
            'spaceToUnderscore' => true
        ],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'sc_gap' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_gap'],
        'default' => ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 0),
        'inputType' => 'text',
        'eval' => ['maxlength' => '4', 'regxp' => 'digit', 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'sc_type' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_type'],
        'inputType' => 'select',
        'options_callback' => [ContentContainer::class, 'getAllTypes'],
        'eval' => [
            'includeBlankOption' => true,
            'mandatory' => true,
            'tl_class' => 'w50'
        ],
        'sql' => "varchar(64) NOT NULL default ''",
    ],
    'sc_gapdefault' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_gapdefault'],
        'default' => 1,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'clr m12 w50'],
        'sql' => "char(1) NOT NULL default '1'",
    ],
    'sc_equalize' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_equalize'],
        'inputType' => 'checkbox',
        'eval' => [],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'sc_color' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_color'],
        'inputType' => 'text',
        'eval' => [
            'maxlength' => 6,
            'multiple' => true,
            'size' => 2,
            'colorpicker' => true,
            'isHexColor' => true,
            'decodeEntities' => true,
            'tl_class' => 'w50 wizard'
        ],
        'sql' => "varchar(64) NOT NULL default ''",
    ],
    'sc_parent' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_parent'],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'sc_childs' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_childs'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'sc_sortid' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['sc_sortid'],
        'sql' => "int(2) unsigned NOT NULL default '0'",
    ],


    'columnset_id'         => [
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => [ContentContainer::class, 'getColumnsetOptions'],
        'reference'        => &$GLOBALS['TL_LANG']['tl_content'],
        'eval'             => [
            'mandatory' => false,
            'submitOnChange' => true,
            'tl_class' => 'w50'
        ],
        'wizard'           => [[ContentContainer::class, 'columnsetIdWizard']],
        'sql'              => "varchar(10) NOT NULL default ''"
    ],
    'addContainer' => [
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'sc_columnset' => [
        'inputType'	=> 'select',
        'options_callback' => [ColumnsetContainer::class, 'getOptions'],
        'eval' => [
            'maxlength' => '255',
            'spaceToUnderscore' => true,
            'mandatory' => true
        ],
        'sql' => "varchar(255) NOT NULL default ''"
    ]
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

$dca['fields']['invisible']['save_callback'][] = [ContentContainer::class, 'toggleAdditionalElements'];
$dca['palettes']['colsetPart'] = 'cssID';
$dca['palettes']['colsetEnd'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['default'];

$dca['fields']['sc_name']['eval']['tl_class'] = 'w50';

$dca['fields']['sc_type']['options_callback'] = [ColumnSet::class, 'getAllTypes'];
$dca['fields']['sc_type']['eval']['submitOnChange'] = true;
$dca['fields']['sc_type']['eval']['mandatory'] = false;
