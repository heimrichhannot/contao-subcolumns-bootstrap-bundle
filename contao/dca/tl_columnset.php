<?php

use Contao\Config;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;

$GLOBALS['TL_DCA']['tl_columnset'] = [
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title', 'columns'],
            'format' => '%s <span style="color:#ccc;">[%s '.($GLOBALS['TL_LANG']['tl_columnset']['formatColumns'] ?? null).']</span>',
        ],
        'sorting'           => [
            'mode'        => 2,
            'flag'        => 1,
            'fields'      => ['title', 'columns'],
            'panelLayout' => 'sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_columnset']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_columnset']['copy'],
                'href'       => 'act=copy',
                'icon'       => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_columnset']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_columnset']['toggle'],
                'icon'       => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_columnset']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['useOutside', 'useInside'],
        'default'      => '{general_legend},title,description,columns,useOutside,useInside;{columnset_legend},sizes;{expert_legend:hide},cssID;{published_legend},published;',
    ],
    'subpalettes' => [
        'useOutside' => 'outsideClass',
        'useInside'  => 'insideClass',
    ],
    'fields'      => [
        'id'           => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'          => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'       => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['title'],
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'description'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['description'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'columns'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['columns'],
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 3,
            'length'    => 1,
            'inputType' => 'select',
            'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'reference' => &$GLOBALS['TL_LANG']['tl_columnset'],
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'useOutside'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['useOutside'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'outsideClass' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['outsideClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'useInside'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['useInside'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'insideClass'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['insideClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'sizes'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['sizes'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'options_callback'   => static function ()
            {
                return $GLOBALS['TL_SUBCL'][Config::get('subcolumns')]['sizes'] ?? [];
            },
            'reference' => &$GLOBALS['TL_LANG']['tl_columnset'],
            'eval'      => ['multiple' => true, 'submitOnChange' => true],
            'sql'       => "mediumblob NULL",
        ],
        'published'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['published'],
            'exclude'   => true,
            'default'   => '1',
            'inputType' => 'checkbox',
            'reference' => &$GLOBALS['TL_LANG']['tl_columnset'],
            'eval'      => [],
            'sql'       => "char(1) NULL",
        ],
        'cssID'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['cssID'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
    ],
];
