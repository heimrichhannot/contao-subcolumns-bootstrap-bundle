<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;


use Contao\DataContainer;
use Contao\System;

class Content extends \Backend
{
    public function createPalette(DataContainer $dc)
    {
        if (!class_exists('onemarshall\AosBundle\AosBundle')) {
            return;
        }

        $palette = $GLOBALS['TL_DCA']['tl_content']['palettes']['colsetStart'];

        $palette = str_replace('invisible,', '', $palette) . ';{invisible_legend:hide},invisible;' . '{aos_legend:hide},
                aosAnimation,
                aosEasing,
                aosDuration,
                aosDelay,
                aosAnchor,
                aosAnchorPlacement,
                aosOffset,
                aosOnce;
                {invisible_legend:hide}';

        $GLOBALS['TL_DCA']['tl_content']['palettes']['colsetStart'] = $palette;
    }

    public function editColumnset(\DataContainer $dc)
    {
        if ($dc->value > 0) {
            return System::getContainer()->get('huh.utils.dca')->getModalEditLink('columnset', (int)$dc->value);
        }

        return '';
    }
}