<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetStart extends \FelixPfeiffer\Subcolumns\colsetStart
{
    protected function compile()
    {
        parent::compile();

        if ($GLOBALS['TL_CONFIG']['subcolumns'] == SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4) {
            $container = ColumnSet::prepareContainer($this->columnset_id);

            if ($container) {
                $equalize = $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] && $this->sc_equalize ? $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] . ' ' : '';

                $this->Template->column  = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid + 1) . (($this->sc_sortid == count($container) - 1) ? ' last' : '');
                $this->Template->scclass = $equalize . $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'] . ' colcount_' . count($container) . ' ' . $this->strSet . ' col_' . $this->sc_type;
            }

            if (($columnSet = ColumnsetModel::findByPk($this->columnset_id)) === null) {
                return;
            }

            $this->Template->useInside = $columnSet->useInside;

            if ($columnSet->useInside) {
                $this->Template->inside = $columnSet->insideClass;
            }
        }
    }
}