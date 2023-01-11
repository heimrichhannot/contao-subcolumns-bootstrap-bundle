<?php

use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

$lang = &$GLOBALS['TL_LANG']['tl_content'];

if (($GLOBALS['TL_CONFIG']['subcolumns'] ?? '') == SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4) {
    $lang['sc_type'][0] = 'Sub columns';
    $lang['sc_type'][1] = 'Please choose how many columns should be used';
}

$lang['columnset_id'][0] = 'Column set';
$lang['columnset_id'][1] = 'Please choose a defined column set.';

$lang['addContainer'][0] = 'Add DIV with CSS-class "container"';
$lang['addContainer'][1] = 'Choose this option to wrap the column set into an DIV-element with CSS-class "container"';
