<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;


use Contao\System;

class Content extends \Backend
{
    public function editColumnset(\DataContainer $dc)
    {
        if ($dc->value > 0) {
            return System::getContainer()->get('huh.utils.dca')->getModalEditLink('columnset', (int)$dc->value);
        }

        return '';
    }
}