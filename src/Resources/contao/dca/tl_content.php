<?php

/**
 * inject bootstrap column set definitions
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = ['HeimrichHannot\SubColumnsBootstrapBundle\ColumnSet', 'appendColumnsetIdToPalette'];

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['sc_type']['options_callback']       = ['HeimrichHannot\SubColumnsBootstrapBundle\ColumnSet', 'getAllTypes'];
$GLOBALS['TL_DCA']['tl_content']['fields']['sc_type']['eval']['submitOnChange'] = true;

$GLOBALS['TL_DCA']['tl_content']['fields']['columnset_id'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['columnset_id'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => ['HeimrichHannot\SubColumnsBootstrapBundle\ColumnSet', 'getAllColumnsets'],
    'reference'        => &$GLOBALS['TL_LANG']['tl_content'],
    'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
    'sql'              => "varchar(10) NOT NULL default ''"
];