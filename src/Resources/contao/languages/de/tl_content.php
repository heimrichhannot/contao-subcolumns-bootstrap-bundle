<?php

$lang = &$GLOBALS['TL_LANG']['tl_content'];

if ($GLOBALS['TL_CONFIG']['subcolumns'] == \HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4) {
    $lang['sc_type'][0] = 'Spaltenanzahl';
    $lang['sc_type'][1] = 'Wählen Sie die Spaltenanzahl aus, die das Spaltenset besitzen soll.';
}

$lang['columnset_id'][0] = 'Spaltenset';
$lang['columnset_id'][1] = 'Wählen Sie eines der verfügbaren Spaltensets.';

$lang['addContainer'][0] = 'DIV mit der Klasse "container" hinzufügen';
$lang['addContainer'][1] = 'Wählen Sie diese Option, um das Spaltenset in ein DIV-Element mit der Klasse "container" einzuschließen.';