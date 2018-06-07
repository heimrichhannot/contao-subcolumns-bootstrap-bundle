<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['design']['columnset'] = [
    'icon'   => 'system/modules/subcolumns_bootstrap_customizable/assets/icon.png',
    'tables' => ['tl_columnset'],
];

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['subcolumn']['colsetStart'] = 'HeimrichHannot\SubColumnsBootstrapBundle\Element\ColsetStart';
$GLOBALS['TL_CTE']['subcolumn']['colsetPart']  = 'HeimrichHannot\SubColumnsBootstrapBundle\Element\ColsetPart';
$GLOBALS['TL_CTE']['subcolumn']['colsetEnd']   = 'HeimrichHannot\SubColumnsBootstrapBundle\Element\ColsetEnd';

/**
 * JavaScript
 */
if (System::getContainer()->get('huh.utils.container')->isFrontend()) {
    $GLOBALS['TL_JAVASCRIPT']['contao-subcolumns-bootstrap-bundle'] =
        'web/bundles/subcolumnsbootstrap/js/contao-subcolumns-bootstrap-bundle.fe.min.js|static';
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_columnset'] = 'HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnSetModel';

/**
 * Columset
 */
$GLOBALS['TL_SUBCL'][\HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4] = [
    'label'   => 'Bootstrap 4',
    'scclass' => 'row',
    'inside'  => false,
    'gap'     => false,
    'files'   => [
        'css' => (System::getContainer()->get('huh.utils.container')->isBackend() ?
            'bundles/subcolumnsbootstrap/css/contao-subcolumns-bootstrap-bundle.be.css||static' : ''),
    ],
    'sizes'   => ['xs', 'sm', 'md', 'lg', 'xl'],
    'sets'    => [
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