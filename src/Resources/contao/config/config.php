<?php

use HeimrichHannot\SubColumnsBootstrapBundle\EventListener\Contao\LoadDataContainerListener;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

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
 * Models
 */
$GLOBALS['TL_MODELS']['tl_columnset'] = 'HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnSetModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [LoadDataContainerListener::class, '__invoke'];

/**
 * Columset
 */
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4] = [
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
            ['col-12'],
        ],
        '2'  => [
            ['col-6'],
            ['col-6'],
        ],
        '3'  => [
            ['col-4'],
            ['col-4'],
            ['col-4'],
        ],
        '4'  => [
            ['col-3'],
            ['col-3'],
            ['col-3'],
            ['col-3'],
        ],
        '5'  => [
            ['col-3'],
            ['col-3'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
        ],
        '6'  => [
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
        ],
        '7'  => [
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-1'],
            ['col-1'],
        ],
        '8'  => [
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
        ],
        '9'  => [
            ['col-2'],
            ['col-2'],
            ['col-2'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
        ],
        '10' => [
            ['col-2'],
            ['col-2'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
        ],
        '11' => [
            ['col-2'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
        ],
        '12' => [
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
            ['col-1'],
        ],
    ],
];

// bootstrap 5
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5] = $GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4];

$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5]['label'] = 'Bootstrap 5';
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5]['sizes'][] = 'xxl';
