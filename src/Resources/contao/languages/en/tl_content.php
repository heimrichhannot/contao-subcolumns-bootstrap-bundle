<?php

$lang = &$GLOBALS['TL_LANG']['tl_content'];

if ($GLOBALS['TL_CONFIG']['subcolumns'] == \HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4) {
    $lang['sc_type'][0] = 'Sub columns';
    $lang['sc_type'][1] = 'Please choose how many columns are created';
}

$lang['columnset_id'][0] = 'Column set';
$lang['columnset_id'][1] = 'Please choose a defined column set.';
