<?php

use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\FormContainer;

$dca = &$GLOBALS['TL_DCA']['tl_form'];

if (is_array($dca['config']['oncopy_callback'] ?? null)) {
    $key = array_search(['tl_subcolumnsCallback', 'formCheck'], $dca['config']['oncopy_callback']);
    if ($key !== false) {
        unset($dca['config']['oncopy_callback'][$key]);
    }
}

$dca['config']['oncopy_callback'][] = [FormContainer::class, 'onCopyCallback'];
