<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Exception;
use FelixPfeiffer\Subcolumns\colsetStart as FelixPfeifferColsetStart;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ColsetStart extends FelixPfeifferColsetStart implements ServiceSubscriberInterface
{
    /** @noinspection PhpUndefinedFieldInspection */
    public function generate(): string
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        if (TL_MODE !== 'BE')
        {
            return ContentElement::generate();
        }

        $arrColor = unserialize($this->sc_color);
        // avoid firing compileColor for php8 compatibility
        if (is_array($arrColor) && count($arrColor) === 2 && empty($arrColor[1])) {
            $arrColor = '';
        } else {
            $arrColor  = $this->compileColor($arrColor);
        }

        if(!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false))
        {
            $columnsetContainer = static::getContainer()->get(ColumnsetContainer::class);
            $title = $columnsetContainer->getTitle($this->sc_columnset);

            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $arrColor;
            $this->Template->colsetTitle = "<span style='display:inline-block;width:100px'>┌——————</span><strong>$title</strong>&emsp;<small>$this->sc_name</small>";
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first']);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?: false;

        $arrColset = !empty($this->sc_type) ? ($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? '') : '';
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = isset($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]) ? count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]) : 0;

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

        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;

        if (($columnSet = ColumnsetModel::findByPk($this->columnset_id)) !== null) {
            System::loadLanguageFile('tl_columnset');
            $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
        }

        $this->Template->visualSet = $strMiniset;
        $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$GLOBALS['TL_LANG']['MSC']['sc_first']);

        return $this->Template->parse();
    }

    /** @noinspection PhpUndefinedFieldInspection */
    protected function compile(): void
    {
        if (!SubColumnsBootstrapBundle::validProfile())
        {
            throw new Exception("Could not find a valid sub-column profile.");
        }

        $profile = $this->strSet = SubColumnsBootstrapBundle::getProfile();

        if (!isset($GLOBALS['TL_SUBCL'][$profile])) {
            throw new Exception(
                "The requested column-set profile could not be found. "
                . "Type \"$profile\" was requested, but no such profile is defined. "
                . "Maybe your configuration is not correct?"
            );
        }

        /**
         * CSS Code in das Pagelayout einfügen
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$profile]['files']['css'] ?? false;
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$profile]['files']['ie'] ?? false;

        if ($mainCSS) {
            $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        }

        if ($IEHacksCSS) {
            $GLOBALS['TL_HEAD']['subcolumns'] = '<!--[if lte IE 7]><link href="' . $IEHacksCSS . '" rel="stylesheet" type="text/css" /><![endif]--> ';
        }

        /** @var ColumnsetContainer $colsetContainer */
        $colsetContainer = static::getContainer()->get(ColumnsetContainer::class);
        $columnset = $colsetContainer->getColumnSettings($this->sc_columnset);
        if ($columnset === null) {
            throw new Exception("The requested column-set \"$this->sc_columnset\" could not be found.");
        }

        $colCount = count($columnset);

        $equalize = '';
        if ($GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] && $this->sc_equalize) {
            $equalize = $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] . ' ';
        }

        $useGap = (bool)$GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $useInner = (bool)$GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        if ($this->sc_gapdefault != 1 || !$useGap)
        {
            $useInner = false;
        }
        else  # $this->sc_gapdefault == 1 && $useGap
        {
            $gap_value = $this->sc_gap ?: ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 12);

            $factor = [
                2 => 0.5,
                3 => 0.666,
                4 => 0.75,
                5 => 0.8,
            ][$colCount] ?? 0;

            if ($factor > 0) {
                $this->Template->gap = ['right' => ceil($factor * $gap_value) . 'px'];
            }
        }

        $this->Template->useInside = $useInner;
        $this->Template->useOutside = false;
        $this->Template->scclass = '';
        $this->Template->column = $columnset[0][0] . ' sc-col:1 first';
        $this->Template->inside = $this->Template->useInside ? $columnset[0][1] : '';

        /*** Altered Pfeiffer code above ***/

        $scclass = sprintf(
            "%s%s sc-colcount:%s sc-profile:%s sc-colset:%s sc-type:%s",
            $equalize,
            $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'] ?? '',
            $colCount,
            $this->strSet,
            preg_replace('/[^a-z0-9_.-\/]+/', '_', strtolower($this->sc_columnset)),
            $this->sc_type ?: 'deprecated'
        );

        $this->Template->addContainer = $this->addContainer;

        $columnsetModel = $colsetContainer->tryColumnsetModelByIdentifier($this->sc_columnset);

        $cssID = ($this->cssID ?? []) ?: [];
        if (!is_array($cssID) || empty(array_filter($cssID)))
        {
            if ($columnsetModel && $columnsetModel->hasCssID())
            {
                $cssID = $columnsetModel->getCssID();
            }
            else
            {
                $cssID = StringUtil::deserialize($cssID, true);
            }
        }
        $cssID[1] = ($cssID[1] ?? false) ? ' ' . $cssID[1] : '';
        $cssID[1] = trim("{$this->Template->class} $scclass$cssID[1]");
        $this->cssID = $cssID;

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
