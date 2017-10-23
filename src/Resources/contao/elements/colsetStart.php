<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle;

class colsetStart extends \FelixPfeiffer\Subcolumns\colsetStart
{
    protected function compile()
    {
        parent::compile();

        if ($GLOBALS['TL_CONFIG']['subcolumns'] == 'boostrap_customizable') {
            $container = ColumnSet::prepareContainer($this->columnset_id);

            if ($container) {
                $this->Template->column = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid + 1) . (($this->sc_sortid == count($container) - 1) ? ' last' : '');
            }
        }
    }
}