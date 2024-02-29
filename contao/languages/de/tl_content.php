<?php

use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

$lang = &$GLOBALS['TL_LANG']['tl_content'];

if (SubColumnsBootstrapBundle::validProfile($GLOBALS['TL_CONFIG']['subcolumns'] ?? '', 4)) {
    $lang['sc_type'][0] = 'Spaltenanzahl';
    $lang['sc_type'][1] = 'Wählen Sie die Spaltenanzahl aus, die der Spaltensatz besitzen soll.';
}

$lang['columnset_id'][0] = 'Spaltensatz';
$lang['columnset_id'][1] = 'Wählen Sie einen der verfügbaren Spaltensätze.';

$lang['addContainer'][0] = 'DIV mit der Klasse "container" hinzufügen';
$lang['addContainer'][1] = 'Wählen Sie diese Option, um das Spaltenset in ein DIV-Element mit der Klasse "container" einzuschließen.';

$lang['sc_columnset'] = ['Spaltensatz', 'Wählen Sie hier den Spaltensatz aus, den Sie verwenden möchten.'];
