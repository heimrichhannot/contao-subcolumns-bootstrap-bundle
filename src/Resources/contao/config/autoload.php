<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package Subcolumns_bootstrap_customizable
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Netzmacht',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'Contao\ColumnsetModel'           => 'system/modules/subcolumns_bootstrap_customizable/models/ColumnsetModel.php',

	// Classes
	'Netzmacht\ColumnSet\ColumnSet'   => 'system/modules/subcolumns_bootstrap_customizable/classes/ColumnSet.php',

	// Elements
	'Netzmacht\ColumnSet\colsetStart' => 'system/modules/subcolumns_bootstrap_customizable/elements/colsetStart.php',
	'Netzmacht\ColumnSet\colsetPart'  => 'system/modules/subcolumns_bootstrap_customizable/elements/colsetPart.php',
));
