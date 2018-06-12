<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\ContentElement;
use Contao\ContentModel;
use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetEnd extends \FelixPfeiffer\Subcolumns\colsetEnd
{
    public function generate()
    {
        $this->strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ? $GLOBALS['TL_CONFIG']['subcolumns'] : 'yaml3';

        if (TL_MODE == 'BE')
        {

            $arrColor = unserialize($this->sc_color);

            if(!$GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'])
            {
                $this->Template = new \BackendTemplate('be_subcolumns');
                $this->Template->setColor = $this->compileColor($arrColor);
                $this->Template->colsetTitle = '### COLUMNSET START '.$this->sc_type.' <strong>'.$this->sc_name.'</strong> ###';

                return $this->Template->parse();
            }

            $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
            $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];



            $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type];
            $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
            $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

            $intCountContainers = count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]);

            $strMiniset = '<div class="colsetexample final '.$strSCClass.'">';

            for($i=0;$i<$intCountContainers;$i++)
            {
                $arrPresentColset = $arrColset[$i];
                $strMiniset .= '<div class="'.$arrPresentColset[0].'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i+1).($blnInside ? '</div>' : '').'</div>';
            }

            $strMiniset .= '</div>';

            $this->Template = new \BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);

            $parent = \ContentModel::findByPk($this->sc_parent);

            if ($parent !== null && ($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) !== null) {
                \System::loadLanguageFile('tl_columnset');

                $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
            }

            $this->Template->visualSet = $strMiniset;

            return $this->Template->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {
        parent::compile();

        if (($content = ContentModel::findByPk($this->sc_parent)) === null)
            return;

        $this->Template->addContainer = $content->addContainer;

        if (($columnSet = ColumnsetModel::findByPk($content->columnset_id)) !== null) {
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