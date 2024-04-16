<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Exception;
use FelixPfeiffer\Subcolumns\colsetStart as FelixPfeifferColsetStart;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Helper\ElementHelper;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetIdentifier;
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

        $arrColor = ElementHelper::getColor($this->sc_color);

        /** @var ElementHelper $helper */
        $helper = System::getContainer()->get(ElementHelper::class);

        if (!($GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? false)) {
            return $helper->renderBackendTemplate(
                $helper->generateTitle($this->sc_columnset, $this->sc_name, ElementHelper::POSITION_START),
                $arrColor,
                null,
                sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first'])
            );
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?: false;

        $arrColset = !empty($this->sc_type) ? ($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? '') : '';
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = isset($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]) ? count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]) : 0;

        $strMiniset = '';

        if ($GLOBALS['TL_CSS']['subcolumns_set'] ?? false)
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

    protected function compile(): void
    {
        /** @var ElementHelper $helper */
        $helper = System::getContainer()->get(ElementHelper::class);

        $colSet = $helper->getSet($this->sc_columnset);

        if (!$colSet)
        {
            throw new Exception("Could not find a valid sub-column profile.");
        }

        if (!isset($GLOBALS['TL_SUBCL'][$colSet->name])) {
            throw new Exception(
                "The requested column-set profile could not be found. "
                . "Type \"".$colSet->name."\" was requested, but no such profile is defined. "
                . "Maybe your configuration is not correct?"
            );
        }

        /**
         * CSS Code in das Pagelayout einfügen
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$colSet->name]['files']['css'] ?? false;
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$colSet->name]['files']['ie'] ?? false;

        if ($mainCSS) {
            $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        }

        if ($IEHacksCSS) {
            $GLOBALS['TL_HEAD']['subcolumns'] = '<!--[if lte IE 7]><link href="' . $IEHacksCSS . '" rel="stylesheet" type="text/css" /><![endif]--> ';
        }

        /** @var ColumnsetContainer $colsetContainer */
        $colsetContainer = System::getContainer()->get(ColumnsetContainer::class);

        $helper->legacyGridFormatting($this->Template, $colSet, $this->sc_gapdefault, $this->sc_gap);

        $colCount = count($colSet->sets);

        $equalize = '';
        if ($GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] && $this->sc_equalize) {
            $equalize = $GLOBALS['TL_SUBCL'][$this->strSet]['equalize'] . ' ';
        }

        $legacyInfos = (bool)($GLOBALS['TL_SUBCL'][$this->strSet]['legacyInfoCSS'] ?? false);

        $this->Template->useOutside = false;
        $this->Template->scclass = '';
        $this->Template->inside = $this->Template->useInside ? ($colSet->sets[0][1] ?? '') : '';
        $this->Template->column = ($colSet->sets[0][0] ?? '') . ($legacyInfos ? ' col_1' : '') . ' sc-col--1 first';;

        /*** Altered Pfeiffer code above ***/

        $rowClasses = sprintf(
            "%s%s sc-colcount--%s sc-profile--%s sc-colset--%s sc-type--%s",
            $equalize,
            $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'] ?? '',
            $colCount,
            $this->strSet,
            preg_replace('/[^a-z0-9_.-\/]+/', '_', strtolower($this->sc_columnset)),
            $this->sc_type ?: 'deprecated'
        );

        if ($legacyInfos)
        {
            $identifier = ColumnsetIdentifier::deconstruct($this->sc_columnset ?? '');
            $identifierLastParam = $identifier->getParam(-1) ?? '';

            $colIdentifier = preg_replace('/[^a-z0-9\s-]+/i', '-',
                str_replace('_', ' ', $identifierLastParam)
            );

            if ($identifier->getSource() === 'db') {
                $colIdentifier = "db--$colIdentifier";
            }

            $rowClasses .= sprintf(
                ' colcount_%s %s col-%s',
                $colCount,
                $this->strSet,
                $colIdentifier
            );
        }

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
        $cssID[1] = trim("{$this->Template->class} $rowClasses$cssID[1]");
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
