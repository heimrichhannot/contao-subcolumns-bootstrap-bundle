<?php

$GLOBALS['BE_MOD']['design']['columnset'] = [
    'icon'   => 'system/modules/subcolumns_bootstrap_customizable/assets/icon.png',
    'tables' => ['tl_columnset'],
];


/**
 * replace content elements
 */
$GLOBALS['TL_CTE']['subcolumn']['colsetStart'] = 'HeimrichHannot\SubColumnsBootstrapBundle\colsetStart';
$GLOBALS['TL_CTE']['subcolumn']['colsetPart']  = 'HeimrichHannot\SubColumnsBootstrapBundle\colsetPart';


/**
 * columset
 */

$GLOBALS['TL_SUBCL']['boostrap4'] = [
    'label'   => 'Bootstrap 4', // Label for the selectmenu
    'scclass' => 'row', // Class for the wrapping container
    'inside'  => false, // Are inside containers used?
    'gap'     => false, // A gap between the columns can be entered in backend
    'sets'    => [ // provide default column sets as fallback if an database entry is deleted
        '1'  => [
            ['col-lg-12'],
        ],
        '2'  => [
            ['col-lg-6'],
            ['col-lg-6'],
        ],
        '3'  => [
            ['col-lg-4'],
            ['col-lg-4'],
            ['col-lg-4'],
        ],
        '4'  => [
            ['col-lg-3'],
            ['col-lg-3'],
            ['col-lg-3'],
            ['col-lg-3'],
        ],
        '5'  => [
            ['col-lg-3'],
            ['col-lg-3'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
        ],
        '6'  => [
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
        ],
        '7'  => [
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-1'],
            ['col-lg-1'],
        ],
        '8'  => [
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
        ],
        '9'  => [
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
        ],
        '10' => [
            ['col-lg-2'],
            ['col-lg-2'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
        ],
        '11' => [
            ['col-lg-2'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
        ],
        '12' => [
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
            ['col-lg-1'],
        ],
    ],
];