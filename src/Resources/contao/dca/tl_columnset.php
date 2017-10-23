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

/**
 * Table tl_columnset
 */
$GLOBALS['TL_DCA']['tl_columnset'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'onload_callback'             => array
		(
			array('Netzmacht\\ColumnSet\\ColumnSet', 'appendColumnSizesToPalette')
		),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
			)
		)
	),

	// List
	'list' => array
	(
		'label' => array
		(
			'fields'                  => array ('title', 'columns'),
			'format'                  => '%s <span style="color:#ccc;">[%s ' . $GLOBALS['TL_LANG']['tl_columnset']['formatColumns'] . ']</span>'
		),
		'sorting' => array
		(
			'mode'                    => 2,
			'flag'                    => 1,
			'fields'                  => array('title', 'columns'),
			'panelLayout'             => 'sort,search,limit',
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_columnset']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_columnset']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_columnset']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_columnset']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_columnset']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		),
	),

	// Palettes
	'metapalettes' => array
	(
		'default' => array
		(
			'title'                   => array('title', 'description', 'columns'),
			'columnset'               => array('sizes'),
			'published'               => array('published'),
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_columnset']['title'],
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class' => 'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_columnset']['description'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class' => 'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),

		'columns' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_columnset']['columns'],
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 3,
			'length'                  => 1,
			'inputType'               => 'select',
			'options'                 => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
			'reference'               => &$GLOBALS['TL_LANG']['tl_columnset'],
			'eval'                    => array('submitOnChange' => true),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),

		'sizes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_columnset']['sizes'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => array('xs', 'sm', 'md', 'lg'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_columnset'],
			'eval'                    => array('multiple' => true, 'submitOnChange' => true),
			'sql'                     => "mediumblob NULL"
		),

		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_columnset']['published'],
			'exclude'                 => true,
			'default'                 => '1',
			'inputType'               => 'checkbox',
			'reference'               => &$GLOBALS['TL_LANG']['tl_columnset'],
			'eval'                    => array(),
			'sql'                     => "char(1) NULL"
		)
	)
);


// defining col set fields
$colSetTemplate = array
(
	'exclude'                 => true,
	'inputType'               => 'multiColumnWizard',
	'load_callback'           => array
	(
		array('Netzmacht\\ColumnSet\\ColumnSet', 'createColumns')
	),
	'eval'                    => array
	(
		'includeBlankOption' => true,
		'columnFields'       => array
		(
			'width' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_columnset']['width'],
				'inputType' => 'select',
				'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
				'eval' => array('style' => 'width: 100px;'),
			),

			'offset' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_columnset']['offset'],
				'inputType' => 'select',
				'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
				'eval' => array('style' => 'width: 100px;', 'includeBlankOption' => true),
			),

			'order' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_columnset']['order'],
				'inputType' => 'select',
				'options' => array(
					'push' => array('push-1', 'push-2', 'push-3', 'push-4', 'push-5', 'push-6', 'push-7', 'push-8', 'push-9', 'push-10', 'push-11', 'push-12'),
					'pull' => array('pull-1', 'pull-2', 'pull-3', 'pull-4', 'pull-5', 'pull-6', 'pull-7', 'pull-8', 'pull-9', 'pull-10', 'pull-11', 'pull-12'),
				),
				'eval' => array('style' => 'width: 160px;', 'includeBlankOption' => true),
			),
		),
		'buttons'             => array('copy' => false, 'delete' => false),
	),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_xs'] = array_merge
(
	$colSetTemplate, array('label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_xs'])
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_sm'] = array_merge
(
	$colSetTemplate, array('label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_sm'])
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_md'] = array_merge
(
	$colSetTemplate, array('label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_md'])
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_lg'] = array_merge
(
	$colSetTemplate, array('label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_lg'])
);