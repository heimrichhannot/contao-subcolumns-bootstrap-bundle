<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\StringUtil;
use Contao\System;
use FelixPfeiffer\Subcolumns\colsetEnd as FelixPfeifferColsetEnd;
use HeimrichHannot\SubColumnsBootstrapBundle\Controller\ColsetIdentifierController;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use HeimrichHannot\SubColumnsBootstrapBundle\Util\ColorUtil;

class ColsetEnd extends ContentElement
{
    const TYPE = 'colsetEnd';

    public function generate(): string
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if (!$scopeMatcher->isBackendRequest($requestStack->getCurrentRequest()))
        {
            return ContentElement::generate();
        }

        $arrColor = StringUtil::deserialize($this->sc_color);
        if (is_countable($arrColor) && count($arrColor) === 2 && empty($arrColor[1])) {
            $arrColor = '';
        } else {
            $arrColor  = ColorUtil::compileColor($arrColor);
        }

        $css = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? null;
        $cssCallback = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css_callback'] ?? null;
        if ($cssCallback && is_callable($cssCallback))
        {
            $css = call_user_func($cssCallback);
        }
        if (!$css)
        {
            $columnsetContainer = static::getContainer()->get(ColumnsetContainer::class);
            $title = $this->sc_columnset ? $columnsetContainer->getTitle($this->sc_columnset) : '-- undefined --';

            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $arrColor;
            $this->Template->colsetTitle = "<span style='display:inline-block;width:80px;overflow:hidden;margin-right:1em;'>└─────────</span><strong>$title</strong>&emsp;<small>$this->sc_name</small>";

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumnsbootstrap/css/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $css;

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? '';
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? []);

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
        $columnSet = $parent !== null ? ColumnsetModel::findByPk($parent->columnset_id) : null;
        if ($columnSet !== null)
        {
            System::loadLanguageFile('tl_columnset');
            $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
        }

        $this->Template->visualSet = $strMiniset;

        return $this->Template->parse();
    }

    protected function compile(): void
    {
        if (!SubColumnsBootstrapBundle::validProfile())
        {
            return;
        }

        /** @var ColsetIdentifierController $colsetIdController */
        $colsetIdController = static::getContainer()->get(ColsetIdentifierController::class);
        $colset = $colsetIdController->getColumnSettings($this->sc_columnset);

        if ($colset === null)
        {
            return;
        }

        $columnsetModel = $colsetIdController->tryColumnsetModelByIdentifier($this->sc_columnset);
        if ($columnsetModel === null)
        {
            $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'] ?? null;
            $blnUseInner = $useGap
                && $this->sc_gapdefault == 1
                && ($GLOBALS['TL_SUBCL'][$this->strSet]['inside'] ?? false);

            $this->Template->useInside = $blnUseInner;
            return;
        }

        $this->Template->addContainer = (bool)$this->addContainer;

        if ($this->Template->useOutside = (bool)$columnsetModel->useOutside) {
            $this->Template->outside = $columnsetModel->outsideClass;
        }

        if ($this->Template->useInside = (bool)$columnsetModel->useInside) {
            $this->Template->inside = $columnsetModel->insideClass;
        }
    }

}
