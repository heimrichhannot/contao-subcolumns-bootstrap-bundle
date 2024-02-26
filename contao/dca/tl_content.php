<?php

use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['config']['onload_callback'][] = ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'appendColumnsetIdToPalette'];
$dca['config']['onload_callback'][] = ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\Content', 'createPalette'];
$dca['config']['onsubmit_callback'][] = [ColumnsetContainer::class, 'onUpdate'];
$dca['config']['ondelete_callback'][] = [ColumnsetContainer::class, 'onDelete'];

/**
 * Fields
 */
$fields = [
    'columnset_id'      => [
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'getAllColumnsets'],
        'reference'        => &$GLOBALS['TL_LANG']['tl_content'],
        'eval'             => [
            'mandatory' => false,
            'submitOnChange' => true,
            'tl_class' => 'w50'
        ],
        'wizard'           => [
            ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\Content', 'editColumnset'],
        ],
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
        'options_callback' => ['HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer', 'getOptions'],
        'eval' => [
            'maxlength' => '255',
            'spaceToUnderscore' => true,
            'mandatory' => true
        ],
        'sql' => "varchar(255) NOT NULL default ''"
    ]
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);

$dca['fields']['sc_name']['eval']['tl_class'] = 'w50';

$dca['fields']['sc_type']['options_callback'] = ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'getAllTypes'];
$dca['fields']['sc_type']['eval']['submitOnChange'] = true;
$dca['fields']['sc_type']['eval']['mandatory'] = false;
