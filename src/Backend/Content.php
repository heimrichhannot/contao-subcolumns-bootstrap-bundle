<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;


use Contao\DataContainer;
use Contao\System;

class Content extends \Backend
{
    public function createPalette(DataContainer $dc)
    {
        $strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ? $GLOBALS['TL_CONFIG']['subcolumns'] : 'yaml3';

        $strGap = $GLOBALS['TL_SUBCL'][$strSet]['gap'] ? ',sc_gapdefault,sc_gap' : '';
        $strEquilize = $GLOBALS['TL_SUBCL'][$strSet]['equalize'] ? '{colheight_legend:hide},sc_equalize;' : '';

        $palette = '{type_legend},type;{colset_legend},sc_name,sc_type,sc_color'.$strGap.';'.$strEquilize.'{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible;';

        if (class_exists('onemarshall\AosBundle\AosBundle')) {
            $palette = str_replace(
                '{invisible_legend:hide}',
                '{aos_legend:hide},
                    aosAnimation,
                    aosEasing,
                    aosDuration,
                    aosDelay,
                    aosAnchor,
                    aosAnchorPlacement,
                    aosOffset,
                    aosOnce;
                    {invisible_legend:hide}',
                $palette
            );
        }

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