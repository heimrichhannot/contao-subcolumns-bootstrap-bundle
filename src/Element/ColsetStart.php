<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\ContentElement;
use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetStart extends \FelixPfeiffer\Subcolumns\colsetStart
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
                $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$GLOBALS['TL_LANG']['MSC']['sc_first']);

                return $this->Template->parse();
            }

            $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
            $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ? $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] : false;

            $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type];
            $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
            $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

            $intCountContainers = count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]);

            $strMiniset = '';

            if($GLOBALS['TL_CSS']['subcolumns_set'])
            {
                $strMiniset = '<div class="colsetexample '.$strSCClass.'">';

                for($i=0;$i<$intCountContainers;$i++)
                {
                    $arrPresentColset = $arrColset[$i];
                    $strMiniset .= '<div class="'.$arrPresentColset[0].($i==0 ? ' active' : '').'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i+1).($blnInside ? '</div>' : '').'</div>';
                }

                $strMiniset .= '</div>';
            }

            $this->Template = new \BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);

            if (($columnSet = ColumnsetModel::findByPk($this->columnset_id)) !== null) {
                \System::loadLanguageFile('tl_columnset');
                $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
            }

            $this->Template->visualSet = $strMiniset;
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$GLOBALS['TL_LANG']['MSC']['sc_first']);

            return $this->Template->parse();
        }

        return ContentElement::generate();
    }

    protected function compile()
    {
        parent::compile();

        if ($GLOBALS['TL_CONFIG']['subcolumns'] == SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4) {
            $container = ColumnSet::prepareContainer($this->columnset_id);

            if ($container) {
                $equalize = $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] && $this->sc_equalize ? $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] . ' ' : '';

                $this->Template->column  = $container[$this->sc_sortid][0] . ' col_' . ($this->sc_sortid + 1) . (($this->sc_sortid == count($container) - 1) ? ' last' : '');
                $this->Template->scclass = $equalize . $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'] . ' colcount_' . count($container) . ' ' . $this->strSet . ' col_' . $this->sc_type;
            }else{
                $this->Template->scclass =  $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'] . ' ' . $this->strSet . ' col_' . $this->sc_type;
            }

            if (($columnSet = ColumnsetModel::findByPk($this->columnset_id)) === null) {
                return;
            }

            $cssID = $this->cssID;

            if ($this->cssID == ['', ''] && $columnSet->cssID)
            {
                $cssID = deserialize($columnSet->cssID, true);
            }

            $cssID[1] = $this->Template->class . ' ' . $this->Template->scclass . ' ' . $cssID[1];
            $this->cssID = $cssID;

            $this->Template->class = '';
            $this->Template->scclass = '';

            $this->Template->addContainer = $this->addContainer;

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