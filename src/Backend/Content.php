<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;

use HeimrichHannot\Haste\Dca\General;

class Content extends \Backend
{
    public function editColumnset(\DataContainer $dc)
    {
        return General::getModalEditLink('columnset', $dc->value);
        return ($dc->value < 1)
            ? ''
            : ' <a href="contao?do=columnset&amp;act=edit&amp;popup=1&amp;id=' . $dc->value . '&amp;rt=' . \RequestToken::get() . '" title="' . sprintf(
                specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]),
                $dc->value
            ) . '" style="padding-left:3px">' . $this->generateImage(
                'alias.gif',
                $GLOBALS['TL_LANG']['tl_content']['editalias'][0],
                'style="vertical-align:top"'
            ) . '</a>';
    }
}