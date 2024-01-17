<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\StringUtil;
use Contao\System;
use FelixPfeiffer\Subcolumns\colsetEnd as FelixPfeifferColsetEnd;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetEnd extends FelixPfeifferColsetEnd
{
    public function generate()
    {
        $this->strSet = SubColumnsBootstrapBundle::getSubType();

        if (TL_MODE !== 'BE')
        {
            return parent::generate();
        }

        $arrColor = StringUtil::deserialize($this->sc_color);
        if (is_countable($arrColor) && count($arrColor) === 2 && empty($arrColor[1])) {
            $arrColor = '';
        } else {
            $arrColor  = $this->compileColor($arrColor);
        }

        if(!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false))
        {
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $arrColor;
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->sc_type.' <strong>'.$this->sc_name.'</strong> ###';

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];

        $arrColset = ($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? '');
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = count(($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? []));

        $strMiniset = '<div class="colsetexample final '.$strSCClass.'">';

        for ($i = 0; $i < $intCountContainers; $i++)
        {
            $arrPresentColset = $arrColset[$i];
            $strMiniset .= '<div class="'.$arrPresentColset[0].'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i+1).($blnInside ? '</div>' : '').'</div>';
        }

        $strMiniset .= '</div>';

        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;

        $parent = ContentModel::findByPk($this->sc_parent);

        if ($parent !== null && ($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) !== null) {
            System::loadLanguageFile('tl_columnset');

            $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
        }

        $this->Template->visualSet = $strMiniset;

        return $this->Template->parse();
    }

    protected function compile()
    {
        @parent::compile();

        $content = ContentModel::findByPk($this->sc_parent);
        if ($content === null) {
            return;
        }

        $this->Template->addContainer = $content->addContainer;

        $columnSet = ColumnsetModel::findByPk($content->columnset_id);
        if ($columnSet === null) {
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
