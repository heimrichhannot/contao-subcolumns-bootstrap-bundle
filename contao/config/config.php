<?php

use Contao\System;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ContentContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Element\ColsetEnd;
use HeimrichHannot\SubColumnsBootstrapBundle\Element\ColsetPart;
use HeimrichHannot\SubColumnsBootstrapBundle\Element\ColsetStart;
use HeimrichHannot\SubColumnsBootstrapBundle\FormField\FormColEnd;
use HeimrichHannot\SubColumnsBootstrapBundle\FormField\FormColPart;
use HeimrichHannot\SubColumnsBootstrapBundle\FormField\FormColStart;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnSetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

/**
 * ## Backend modules
 */
$GLOBALS['BE_MOD']['design']['columnset'] = [
    'icon'   => 'system/modules/subcolumns_bootstrap_customizable/assets/icon.png',
    'tables' => ['tl_columnset'],
];

/**
 * ## Content elements
 */
$GLOBALS['TL_CTE']['subcolumn'] = [
    ColsetStart::TYPE => ColsetStart::class,
    ColsetPart::TYPE => ColsetPart::class,
    ColsetEnd::TYPE => ColsetEnd::class
];

/**
 * ## Models
 */
$GLOBALS['TL_MODELS']['tl_columnset'] = ColumnSetModel::class;

/**
 * ## Hooks
 */
# $GLOBALS['TL_HOOKS']['loadDataContainer'][] = [LoadDataContainerListener::class, '__invoke'];
$GLOBALS['TL_HOOKS']['clipboardCopy'][] = [ContentContainer::class, 'clipboardCopy'];
$GLOBALS['TL_HOOKS']['clipboardCopyAll'][] = [ContentContainer::class, 'clipboardCopyAll'];

/**
 * ## Form fields
 */
$GLOBALS['TL_FFL'][FormColStart::TYPE] = FormColStart::class;
$GLOBALS['TL_FFL'][FormColPart::TYPE] = FormColPart::class;
$GLOBALS['TL_FFL'][FormColEnd::TYPE] = FormColEnd::class;

/**
 * ## Einrücken von Elementen
 */
$GLOBALS['TL_WRAPPERS']['start'][] = ColsetStart::TYPE;
$GLOBALS['TL_WRAPPERS']['separator'][] = ColsetPart::TYPE;
$GLOBALS['TL_WRAPPERS']['stop'][] = ColsetEnd::TYPE;

/**
 * ## Spaltensatzprofile
 */
$GLOBALS['TL_SUBCL'] ??= [];

// bootstrap 3
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP3] = [
    'label'    => 'Bootstrap 3',
    'legacyInfoCSS' => true,
    'scclass'  => 'row',
    'equalize' => false,
    'inside'   => true,
    'gap'      => true,
    'files'    => [],
    'sizes'   => ['xs', 'sm', 'md', 'lg'],
    'sets'     => [],
];

// bootstrap 4
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4] = [
    'label'   => 'Bootstrap 4',
    'legacyInfoCSS' => false,
    'scclass' => 'row',
    'inside'  => false,
    'gap'     => false,
    'files'   => [
        'css' => (function() {
            $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
            $requestStack = System::getContainer()->get('request_stack');

            if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
                return 'bundles/subcolumnsbootstrap/css/contao-subcolumns-bootstrap-bundle.be.css||static';
            }

            return '';
        })()
    ],
    'sizes'   => ['xs', 'sm', 'md', 'lg', 'xl'],
    'sets'    => [],
];

// bootstrap 5
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5] = $GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4];

$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5]['label'] = 'Bootstrap 5';
$GLOBALS['TL_SUBCL'][SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5]['sizes'][] = 'xxl';
