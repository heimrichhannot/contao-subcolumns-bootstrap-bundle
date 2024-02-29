<?php

use Contao\StringUtil;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\FormFieldContainer;

$dca = &$GLOBALS['TL_DCA']['tl_form_field'];

/**
 * Table tl_form_field
 */
/**
 * Config
 **/
$dca['config']['onsubmit_callback'][] = [FormFieldContainer::class, 'onSubmit'];
$dca['config']['ondelete_callback'][] = ['tl_form_subcols', 'scDelete'];
$dca['config']['oncopy_callback'][] = ['tl_form_subcols', 'scCopy'];

/**
 * Operations
 **/
$dca['list']['operations']['edit']['button_callback'] = ['tl_form_subcols', 'showEditOperation'];
$dca['list']['operations']['copy']['button_callback'] = ['tl_form_subcols', 'showCopyOperation'];
$dca['list']['operations']['delete']['button_callback'] = ['tl_form_subcols', 'showDeleteOperation'];
$dca['list']['operations']['toggle']['button_callback'] = ['tl_form_subcols', 'toggleIcons'];

/**
 * Palettes
 **/
$dca['palettes']['__selector__'][] = 'fsc_gapuse';

$dca['palettes']['formcolstart'] = '{type_legend},type;{colsettings_legend},fsc_type,fsc_color,fsc_name,fsc_equalize,fsc_gapuse;{expert_legend:hide},class';
$dca['palettes']['formcolpart'] = '{type_legend},type;{colsettings_legend},fsc_type';
$dca['palettes']['formcolend'] = '{type_legend},type;{colsettings_legend},fsc_type';

/**
 * Subpalettes
 **/
$dca['subpalettes']['fsc_gapuse'] = 'fsc_gap';

/**
 * Fields
 **/
$dca['fields']['fsc_type'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['tl_form_subcols', 'getAllTypes'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$dca['fields']['fsc_name'] = [
    'exclude' => true,
    'inputType' => 'text',
    'save_callback' => [['tl_form_subcols', 'setColsetName']],
    'eval' => ['maxlength' => '255', 'unique' => true, 'spaceToUnderscore' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$dca['fields']['fsc_gapuse'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$dca['fields']['fsc_gap'] = [
    'exclude' => true,
    'inputType' => 'text',
    'default' => ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 0),
    'eval' => ['maxlength' => '4', 'regxp' => 'digit'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$dca['fields']['fsc_equalize'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$dca['fields']['fsc_color'] = [
    'inputType' => 'text',
    'eval' => ['maxlength' => 6, 'multiple' => true, 'size' => 2, 'colorpicker' => true, 'isHexColor' => true, 'decodeEntities' => true, 'tl_class' => 'w50 wizard'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$dca['fields']['fsc_parent'] = [
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$dca['fields']['fsc_childs'] = [
    'sql' => "varchar(255) NOT NULL default ''",
];

$dca['fields']['fsc_sortid'] = [
    'sql' => "int(2) unsigned NOT NULL default '0'",
];
