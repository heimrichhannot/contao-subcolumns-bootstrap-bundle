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
	$GLOBALS['TL_LANG']['tl_content']['sc_type'][0] = 'Sub columns';
	$GLOBALS['TL_LANG']['tl_content']['sc_type'][1] = 'Parelease choose how many columns are created';
}

$GLOBALS['TL_LANG']['tl_content']['columnset_id'][0] = 'Column set';
$GLOBALS['TL_LANG']['tl_content']['columnset_id'][1] = 'Please choose a defined column set.';
