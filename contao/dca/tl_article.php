<?php

use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ArticleContainer;

$dca = &$GLOBALS['TL_DCA']['tl_article'];

if (is_array($dca['config']['oncopy_callback'] ?? null)) {
    $key = array_search(['tl_subcolumnsCallback', 'articleCheck'], $dca['config']['oncopy_callback']);
    if ($key !== false) {
        unset($dca['config']['oncopy_callback'][$key]);
    }
}

$dca['config']['oncopy_callback'][] = [ArticleContainer::class, 'onCopyCallback'];
