<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package   netzmacht-columnset
 * @author    David Molineus <http://www.netzmacht.de>
 * @license   GNU/LGPL
 * @copyright Copyright 2012 David Molineus netzmacht creative
 *
 **/


// change label of sc_type
if($GLOBALS['TL_CONFIG']['subcolumns'] == 'boostrap_customizable') {
	$GLOBALS['TL_LANG']['tl_content']['sc_type'][0] = 'Spaltenanzahl';
	$GLOBALS['TL_LANG']['tl_content']['sc_type'][1] = 'Wählen Sie die Spaltenanzahl aus, die das Spaltenset besitzen soll.';
}

$GLOBALS['TL_LANG']['tl_content']['columnset_id'][0] = 'Spaltenset';
$GLOBALS['TL_LANG']['tl_content']['columnset_id'][1] = 'Wählen Sie eines der verfügbaren Spaltensets.';