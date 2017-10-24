<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetPart extends \FelixPfeiffer\Subcolumns\colsetPart
{

    /**
     * extends subcolumns compile method for generating dynamically column set
     */
    protected function compile()
    {
        parent::compile();

        if ($GLOBALS['TL_CONFIG']['subcolumns'] == SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4) {
            $parent    = \ContentModel::findByPk($this->sc_parent);
            $container = ColumnSet::prepareContainer($parent->columnset_id);

            if ($container) {
                $this->Template->column = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid + 1) . (($this->sc_sortid == count($container) - 1) ? ' last' : '');
            }

            if (($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) === null) {
                return;
            }

            $this->Template->useInside = $columnSet->useInside;

            if ($columnSet->useInside) {
                $this->Template->inside = $columnSet->insideClass;
            }
        }
    }
}