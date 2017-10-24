<?php

$dca = &$GLOBALS['TL_DCA']['tl_content'];

$dca['config']['onload_callback'][] = ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'appendColumnsetIdToPalette'];

/**
 * Fields
 */
$dca['fields']['columnset_id'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['columnset_id'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'getAllColumnsets'],
    'reference'        => &$GLOBALS['TL_LANG']['tl_content'],
    'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
    'sql'              => "varchar(10) NOT NULL default ''"
];

$dca['fields']['sc_name']['eval']['tl_class'] = 'w50';

$dca['fields']['sc_type']['options_callback']       = ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'getAllTypes'];
$dca['fields']['sc_type']['eval']['submitOnChange'] = true;