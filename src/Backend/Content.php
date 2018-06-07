<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;


use Contao\System;

class Content extends \Backend
{
    public function editColumnset(\DataContainer $dc)
    {
        return System::getContainer()->get('huh.utils.dca')->getModalEditLink('columnset', $dc->value);
    }
}