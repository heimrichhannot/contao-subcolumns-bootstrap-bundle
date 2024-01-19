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
// bootstrap 3
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP3] = [
    'label'    => 'Bootstrap 3',
    'scclass'  => 'row',
    'equalize' => false,
    'inside'   => true,
    'gap'      => true,
    'files'    => [],
    'sizes'   => ['xs', 'sm', 'md', 'lg'],
    'sets'     => [
        // two columns
        '1/11' => [
            ['col-xs-2 col-sm-2 col-md-1 col-lg-1', 'inside'],
            ['col-xs-10 col-sm-10 col-md-11 col-lg-11', 'inside']
        ],
        '2/10' => [
            ['col-xs-12 col-sm-3 col-md-3 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-9 col-md-9 col-lg-10', 'inside']
        ],
        '4/8' => [
            ['col-xs-12 col-sm-4 col-md-4 col-lg-4', 'inside'],
            ['col-xs-12 col-sm-8 col-md-8 col-lg-8', 'inside']
        ],
        '6/6' => [
            ['col-lg-6 col-md-6 col-sm-6 col-xs-12', 'inside'],
            ['col-lg-6 col-md-6 col-sm-6 col-xs-12', 'inside']
        ],
        '8/4' => [
            ['col-xs-12 col-sm-8 col-md-8 col-lg-8', 'inside'],
            ['col-xs-12 col-sm-4 col-md-4 col-lg-4', 'inside']
        ],
        '9/3' => [
            ['col-xs-12 col-sm-8 col-md-9 col-lg-9', 'inside'],
            ['col-xs-12 col-sm-4 col-md-3 col-lg-3', 'inside']
        ],
        '10/2' => [
            ['col-xs-12 col-sm-9 col-md-9 col-lg-10', 'inside'],
            ['col-xs-12 col-sm-3 col-md-3 col-lg-2', 'inside']
        ],
        // three columns
        '4/4/4' => [
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4', 'inside']]
        ,
        // three columns, one short
        '2/5/5' => [
            ['col-xs-12 col-sm-12 col-md-2 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-12 col-md-5 col-lg-5', 'inside'],
            ['col-xs-12 col-sm-12 col-md-5 col-lg-5', 'inside']]
        ,
        // four columns
        '3/3/3/3' => [
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3', 'inside'],
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3', 'inside'],
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3', 'inside'],
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3', 'inside']
        ],
        // six columns
        '2/2/2/2/2/2' => [
            ['col-xs-12 col-sm-2 col-md-2 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-2 col-md-2 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-2 col-md-2 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-2 col-md-2 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-2 col-md-2 col-lg-2', 'inside'],
            ['col-xs-12 col-sm-2 col-md-2 col-lg-2', 'inside'],
        ],
        // box columns
        'box-3/3/3/3' => [
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-3 col-lg-3 box', 'inside'],
        ],
        'box-4/4/4' => [
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
        ],
        'box-4/4/4_first-red' => [
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box red', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
        ],
        'box-4/8' => [
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-8 col-lg-8 box', 'inside'],
        ],
        'box-6/6' => [
            ['col-xs-12 col-sm-12 col-md-6 col-lg-6 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-6 col-lg-6 box', 'inside'],
        ],
        'box-8/4' => [
            ['col-xs-12 col-sm-12 col-md-8 col-lg-8 box', 'inside'],
            ['col-xs-12 col-sm-12 col-md-4 col-lg-4 box', 'inside'],
        ],
        'box' => [
            ['col-xs-12 col-sm-12 col-md-12 col-lg-12 box', 'inside']
        ],
        'box-red' => [
            ['col-xs-12 col-sm-12 col-md-12 col-lg-12 box red', 'inside']
        ],
        // 1
        'full'        => [['full', 'inside']], // complete width
        'full-center' => [['col-lg-12', 'inside']], // full width with centered container
        '12'          => [['col-xs-12 col-sm-12 col-md-12 col-lg-12', 'inside']],
    ],
];

// bootstrap 4
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4] = [
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
        '1'  => [['col-md-12']],
        '2'  => [['col-md-6'], ['col-md-6']],
        '3'  => [['col-md-4'], ['col-md-4'], ['col-md-4']],
        '4'  => [['col-md-3'], ['col-md-3'], ['col-md-3'], ['col-md-3']],
        '5'  => [['col-md-3'], ['col-md-3'], ['col-md-2'], ['col-md-2'], ['col-md-2']],
        '6'  => [['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-2']],
        '7'  => [['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-1'], ['col-md-1']],
        '8'  => [['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1']],
        '9'  => [['col-md-2'], ['col-md-2'], ['col-md-2'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1']],
        '10' => [['col-md-2'], ['col-md-2'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1']],
        '11' => [['col-md-2'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1']],
        '12' => [['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1'], ['col-md-1']],
    ],
];

// bootstrap 5
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5] = $GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4];

$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5]['label'] = 'Bootstrap 5';
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5]['sizes'][] = 'xxl';
