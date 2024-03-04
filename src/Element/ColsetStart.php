<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\Controller\ColsetIdentifierController;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetIdentifier;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use HeimrichHannot\SubColumnsBootstrapBundle\Util\ColorUtil;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use const HeimrichHannot\SubColumnsBootstrapBundle\Util\px;

class ColsetStart extends ContentElement implements ServiceSubscriberInterface
{
    const TYPE = 'colsetStart';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_colsetStart';

    /**
     * Set-Type
     */
    protected $strSet;

    /** @noinspection PhpUndefinedFieldInspection */
    public function generate(): string
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        $scopeMatcher = static::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = static::getContainer()->get('request_stack');

        if (!$scopeMatcher->isBackendRequest($requestStack->getCurrentRequest()))
        {
            return ContentElement::generate();
        }

        $arrColor = unserialize($this->sc_color);
        // avoid firing compileColor for php8 compatibility
        if (is_array($arrColor) && count($arrColor) === 2 && empty($arrColor[1])) {
            $arrColor = '';
        } else {
            $arrColor = ColorUtil::compileColor($arrColor);
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
            $this->Template->colsetTitle = "<span style='display:inline-block;width:80px;overflow:hidden;margin-right:1em;'>┌─────────</span><strong>$title</strong>&emsp;<small>$this->sc_name</small>";
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first']);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumnsbootstrap/css/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $css;

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? '';
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = isset($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]) ? count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type]) : 0;
        $strMiniset = '<div class="colsetexample '.$strSCClass.'">';

        for ($i = 0; $i < $intCountContainers; $i++)
        {
            $arrPresentColset = $arrColset[$i];
            $strMiniset .= '<div class="'.$arrPresentColset[0].($i==0 ? ' active' : '').'">'.($blnInside ? '<div class="'.$arrPresentColset[1].'">' : '').($i+1).($blnInside ? '</div>' : '').'</div>';
        }

        $strMiniset .= '</div>';

        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;

        $columnSet = ColumnsetModel::findByPk($this->columnset_id);
        if ($columnSet !== null)
        {
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
                . "Check your configuration."
            );
        }

        /**
         * CSS Code in das Pagelayout einfügen
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$profile]['files']['css'] ?? null;
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$profile]['files']['ie'] ?? false;
        $cssCallback = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css_callback'] ?? null;
        if ($cssCallback && is_callable($cssCallback))
        {
            $mainCSS = call_user_func($cssCallback);
        }
        if ($mainCSS)
        {
            $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        }
        if ($IEHacksCSS)
        {
            $GLOBALS['TL_HEAD']['subcolumns'] = '<!--[if lte IE 7]><link href="' . $IEHacksCSS . '" rel="stylesheet" type="text/css" /><![endif]--> ';
        }

        /** @var ColsetIdentifierController $colsetContainer */
        $colsetContainer = static::getContainer()->get(ColsetIdentifierController::class);
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
        $legacyInfos = (bool)($GLOBALS['TL_SUBCL'][$this->strSet]['legacyInfoCSS'] ?? false);

        if ($this->sc_gapdefault != 1 || !$useGap)
        {
            $useInner = false;
        }
        else  # $this->sc_gapdefault == 1 && $useGap
        {
            $gap_value = (int) ($this->sc_gap ?: ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 12));

            $factor = [2 => 0.5, 3 => 0.666, 4 => 0.75, 5 => 0.8][$colCount] ?? 0;

            if ($factor > 0) {
                $this->Template->gap = ['right' => ceil($factor * $gap_value) . px];
            }
        }

        $this->Template->useInside = $useInner;
        $this->Template->useOutside = false;
        $this->Template->scclass = '';
        $this->Template->inside = $this->Template->useInside ? ($columnset[0][1] ?? '') : '';
        $this->Template->column = ($columnset[0][0] ?? '') . ($legacyInfos ? ' col_1' : '') . ' sc-col--1 first';;

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

        $this->Template->addContainer = $this->sc_addContainer;

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
