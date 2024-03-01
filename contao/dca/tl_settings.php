<?php

use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;

$dca = &$GLOBALS['TL_DCA']['tl_settings'];

$dca['fields']['subcolumns'] = [
    'inputType' => 'select',
    'options_callback' => [ColumnsetContainer::class, 'getAllProfileOptions'],
    'eval' => ['tl_class' => 'w50'],
];

$dca['fields']['subcolumns_gapdefault'] = [  # todo: remove
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50'],
];

$dca['palettes']['default'] .= ';{subcolumns_legend:hide},subcolumns;';
