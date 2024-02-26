<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\StringUtil;
use Contao\System;
use FelixPfeiffer\Subcolumns\colsetPart as FelixPfeifferColsetPart;
use HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ColsetPart extends FelixPfeifferColsetPart implements ServiceSubscriberInterface
{
    public function generate()
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        if (TL_MODE !== 'BE')
        {
            return parent::generate();
        }

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

        $arrColor = StringUtil::deserialize($this->sc_color);
        if (is_countable($arrColor) && count($arrColor) === 2 && empty($arrColor[1])) {
            $arrColor = '';
        } else {
            $arrColor  = $this->compileColor($arrColor);
        }

        if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false))
        {
            $columnsetContainer = static::getContainer()->get(ColumnsetContainer::class);
            $title = $this->sc_columnset ? $columnsetContainer->getTitle($this->sc_columnset) : '-- undefined --';

            $this->Template              = new BackendTemplate('be_subcolumns');
            $this->Template->setColor    = $arrColor;
            $this->Template->colsetTitle = "<span style='display:inline-block;width:60px;overflow:hidden;margin-right:1em;'>──────────────</span><strong>$title</strong>&emsp;<small>$this->sc_name</small>";
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

        $this->Template           = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;

        $parent = \ContentModel::findByPk($this->sc_parent);

        if ($parent !== null && ($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) !== null) {
            System::loadLanguageFile('tl_columnset');

            $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
        }

        $this->Template->visualSet = $strMiniset;
        $this->Template->hint      = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

        return $this->Template->parse();
    }

    protected function compile()
    {
        parent::compile();

        if (!SubColumnsBootstrapBundle::validProfile())
        {
            return;
        }

        $useGap = (bool)$GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $useInside = (($this->sc_gapdefault != 1 || !$useGap) ?? true)
            ? false
            : (bool)$GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $this->Template->useInside = $useInside;

        /** @var ColumnsetContainer $colsetContainer */
        $colsetContainer = static::getContainer()->get(ColumnsetContainer::class);
        $colset = $colsetContainer->getColumnSettings($this->sc_columnset);

        if ($colset === null)
        {
            return;
        }

        if (!empty($colset))
        {
            $colNumber = ($this->sc_sortid + 1);
            $colClass = " sc-col--$colNumber";

            if ($GLOBALS['TL_SUBCL'][$this->strSet]['legacyInfoCSS'] ?? false)
            {
                $colClass .= ' col_' . ($this->sc_sortid + 1);
            }

            $lastClass = ($this->sc_sortid == count($colset) - 1) ? ' last' : '';
            $this->Template->column = $colset[$this->sc_sortid][0] . $colClass . $lastClass;
        }

        $columnsetModel = $colsetContainer->tryColumnsetModelByIdentifier($this->sc_columnset);
        if ($columnsetModel === null)
        {
            return;
        }

        if ($this->Template->useOutside = (bool)$columnsetModel->useOutside) {
            $this->Template->outside = $columnsetModel->outsideClass ?: '';
        }

        if ($this->Template->useInside = (bool)$columnsetModel->useInside) {
            $this->Template->inside = $columnsetModel->insideClass ?: '';
        }
    }

    public static function getSubscribedServices(): array
    {
        return [ColumnsetContainer::class];
    }
}
