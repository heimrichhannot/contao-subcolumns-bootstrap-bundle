<?php

$GLOBALS['TL_DCA']['tl_columnset'] = [

    // Config
    'config'       => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => [
            ['HeimrichHannot\SubColumnsBootstrapBundle\ColumnSet', 'appendColumnSizesToPalette']
        ],
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ]
        ]
    ],

    // List
    'list'         => [
        'label'             => [
            'fields' => ['title', 'columns'],
            'format' => '%s <span style="color:#ccc;">[%s ' . $GLOBALS['TL_LANG']['tl_columnset']['formatColumns'] . ']</span>'
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
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_columnset']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_columnset']['copy'],
                'href'       => 'act=paste&amp;mode=copy',
                'icon'       => 'copy.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_columnset']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_columnset']['toggle'],
                'icon'       => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_columnset']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ]
        ],
    ],

    // Palettes
    'metapalettes' => [
        'default' => [
            'title'     => ['title', 'description', 'columns'],
            'columnset' => ['sizes'],
            'published' => ['published'],
        ]
    ],

    // Fields
    'fields'       => [
        'id'          => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'         => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['title'],
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'description' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['description'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],

        'columns' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['columns'],
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 3,
            'length'    => 1,
            'inputType' => 'select',
            'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'reference' => &$GLOBALS['TL_LANG']['tl_columnset'],
            'eval'      => ['submitOnChange' => true],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],

        'sizes' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['sizes'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'options'   => ['xs', 'sm', 'md', 'lg'],
            'reference' => &$GLOBALS['TL_LANG']['tl_columnset'],
            'eval'      => ['multiple' => true, 'submitOnChange' => true],
            'sql'       => "mediumblob NULL"
        ],

        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['published'],
            'exclude'   => true,
            'default'   => '1',
            'inputType' => 'checkbox',
            'reference' => &$GLOBALS['TL_LANG']['tl_columnset'],
            'eval'      => [],
            'sql'       => "char(1) NULL"
        ]
    ]
];


// defining col set fields
$colSetTemplate = [
    'exclude'       => true,
    'inputType'     => 'multiColumnWizard',
    'load_callback' => [
        ['HeimrichHannot\SubColumnsBootstrapBundle\ColumnSet', 'createColumns']
    ],
    'eval'          => [
        'includeBlankOption' => true,
        'columnFields'       => [
            'width' => [
                'label'     => $GLOBALS['TL_LANG']['tl_columnset']['width'],
                'inputType' => 'select',
                'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                'eval'      => ['style' => 'width: 100px;'],
            ],

            'offset' => [
                'label'     => $GLOBALS['TL_LANG']['tl_columnset']['offset'],
                'inputType' => 'select',
                'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                'eval'      => ['style' => 'width: 100px;', 'includeBlankOption' => true],
            ],

            'order' => [
                'label'     => $GLOBALS['TL_LANG']['tl_columnset']['order'],
                'inputType' => 'select',
                'options'   => [
                    'push' => ['push-1', 'push-2', 'push-3', 'push-4', 'push-5', 'push-6', 'push-7', 'push-8', 'push-9', 'push-10', 'push-11', 'push-12'],
                    'pull' => ['pull-1', 'pull-2', 'pull-3', 'pull-4', 'pull-5', 'pull-6', 'pull-7', 'pull-8', 'pull-9', 'pull-10', 'pull-11', 'pull-12'],
                ],
                'eval'      => ['style' => 'width: 160px;', 'includeBlankOption' => true],
            ],
        ],
        'buttons'            => ['copy' => false, 'delete' => false],
    ],
    'sql'           => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_xs'] = array_merge
(
    $colSetTemplate, ['label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_xs']]
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_sm'] = array_merge
(
    $colSetTemplate, ['label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_sm']]
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_md'] = array_merge
(
    $colSetTemplate, ['label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_md']]
);

$GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_lg'] = array_merge
(
    $colSetTemplate, ['label' => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_lg']]
);