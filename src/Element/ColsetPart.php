<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetPart extends \FelixPfeiffer\Subcolumns\colsetPart
{
    public function generate()
    {
        $this->strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ? $GLOBALS['TL_CONFIG']['subcolumns'] : 'yaml3';

        if (TL_MODE == 'BE') {
            switch ($this->sc_sortid) {
                case 1:
                    $colID = $GLOBALS['TL_LANG']['MSC']['sc_second'];
                    break;
                case 2:
                    $colID = $GLOBALS['TL_LANG']['MSC']['sc_third'];
                    break;
                case 3:
                    $colID = $GLOBALS['TL_LANG']['MSC']['sc_fourth'];
                    break;
                case 4:
                    $colID = $GLOBALS['TL_LANG']['MSC']['sc_fifth'];
                    break;
            }

            $arrColor = unserialize($this->sc_color);
            if(count($arrColor) === 2 && empty($arrColor[1])) {
                $arrColor = '';
            } else {
                $arrColor  = $this->compileColor($arrColor);
            }

            if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false)) {
                $this->Template              = new \BackendTemplate('be_subcolumns');
                $this->Template->setColor    = $arrColor;
                $this->Template->colsetTitle = '### COLUMNSET START ' . $this->sc_type . ' <strong>' . $this->sc_name . '</strong> ###';
                #$this->Template->visualSet = $strMiniset;
                $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

                return $this->Template->parse();
            }

            $GLOBALS['TL_CSS']['subcolumns']     = 'system/modules/Subcolumns/assets/be_style.css';
            $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];

            $arrColset  = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type];
            $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
            $blnInside  = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

            $intCountContainers = count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]);

            $strMiniset = '<div class="colsetexample ' . $strSCClass . '">';

            for ($i = 0; $i < $intCountContainers; $i++) {
                $arrPresentColset = $arrColset[$i];
                $strMiniset       .= '<div class="' . $arrPresentColset[0] . ($i == $this->sc_sortid ? ' active' : '') . '">' . ($blnInside ? '<div class="' . $arrPresentColset[1] . '">' : '') . ($i + 1) . ($blnInside ? '</div>' : '') . '</div>';
            }

            $strMiniset .= '</div>';

            $this->Template           = new \BackendTemplate('be_subcolumns');
            $this->Template->setColor = $arrColor;

            $parent = \ContentModel::findByPk($this->sc_parent);

            if ($parent !== null && ($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) !== null) {
                \System::loadLanguageFile('tl_columnset');

                $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
            }

            $this->Template->visualSet = $strMiniset;
            $this->Template->hint      = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

            return $this->Template->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {
        @parent::compile();

        if (in_array($GLOBALS['TL_CONFIG']['subcolumns'], [
            SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4,
            SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5
        ])) {
            $parent    = \ContentModel::findByPk($this->sc_parent);
            $container = ColumnSet::prepareContainer($parent->columnset_id);

            if ($container) {
                $this->Template->column = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid + 1) . (($this->sc_sortid == count($container) - 1) ? ' last' : '');
            }

            if (($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) === null) {
                return;
            }

            $this->Template->useOutside = $columnSet->useOutside;

            if ($columnSet->useOutside) {
                $this->Template->outside = $columnSet->outsideClass;
            }

            $this->Template->useInside = $columnSet->useInside;

            if ($columnSet->useInside) {
                $this->Template->inside = $columnSet->insideClass;
            }
        }
    }
}
