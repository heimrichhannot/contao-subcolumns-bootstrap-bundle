<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\SubColumnsBootstrapBundle\Controller\ColsetIdentifierController;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use HeimrichHannot\SubColumnsBootstrapBundle\Util\ColorUtil;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use const HeimrichHannot\SubColumnsBootstrapBundle\Util\px;

class ColsetPart extends ContentElement implements ServiceSubscriberInterface
{
    const TYPE = 'colsetPart';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_colsetPart';

    /**
     * Set-Type
     */
    protected $strSet;

    public function generate(): string
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest()))
        {
            return ContentElement::generate();
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

            $this->Template              = new BackendTemplate('be_subcolumns');
            $this->Template->setColor    = $arrColor;
            $this->Template->colsetTitle = "<span style='display:inline-block;width:60px;overflow:hidden;margin-right:1em;'>──────────────</span><strong>$title</strong>&emsp;<small>$this->sc_name</small>";
            # $this->Template->visualSet = $strMiniset;
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns']     = 'bundles/subcolumnsbootstrap/css/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $css;

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

        $parent = ContentModel::findByPk($this->sc_parent);

        if ($parent !== null && ($columnSet = ColumnsetModel::findByPk($parent->columnset_id)) !== null) {
            System::loadLanguageFile('tl_columnset');

            $this->Template->colsetTitle = $columnSet->title . ' (' . $this->sc_type . ' ' . $GLOBALS['TL_LANG']['tl_columnset']['columns' . ($this->sc_type > 1 ? 'Plural' : 'Singular')] . ')';
        }

        $this->Template->visualSet = $strMiniset;
        $this->Template->hint      = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

        return $this->Template->parse();
    }

    protected function compile(): void
    {
        $this->compileWithGlobalIdentifier();

        if (!SubColumnsBootstrapBundle::validProfile())
        {
            return;
        }

        $useGap = (bool)$GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
        $useInside = !(($this->sc_gapdefault != 1 || !$useGap) ?? true)
            && $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $this->Template->useInside = $useInside;

        /** @var ColsetIdentifierController $colsetIdController */
        $colsetIdController = static::getContainer()->get(ColsetIdentifierController::class);
        $colset = $colsetIdController->getColumnSettings($this->sc_columnset);

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

        $columnsetModel = $colsetIdController->tryColumnsetModelByIdentifier($this->sc_columnset);
        if ($columnsetModel === null)
        {
            return;
        }

        if ($this->Template->useOutside = (bool) $columnsetModel->useOutside) {
            $this->Template->outside = $columnsetModel->outsideClass ?: '';
        }

        if ($this->Template->useInside = (bool) $columnsetModel->useInside) {
            $this->Template->inside = $columnsetModel->insideClass ?: '';
        }
    }

    protected function compileWithGlobalIdentifier(): void
    {
        $arrCounts = ['1' => 'second', '2' => 'third', '3' => 'fourth', '4' => 'fifth'];
        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->sc_type] ?? null;
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'] ?? false;
        $blnUseInner = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'] ?? false;

        if (!$container) {
            return;
        }

        $this->Template->colID = $arrCounts[$this->sc_sortid] ?? '';
        $this->Template->column = trim(sprintf(
            '%s col_%s %s',
            $container[$this->sc_sortid][0] ?? '',
            $this->sc_sortid + 1,
            $this->sc_sortid == count($container) - 1 ? 'last' : ''
        ));
        $this->Template->inside = $this->Template->useInside ? $container[$this->sc_sortid][1] : '';
        $this->Template->useInside = $blnUseInner;

        if ($this->sc_gapdefault != 1 || !$useGap)
        {
            $this->Template->useInside = false;
            return;
        }

        $gap_value = $this->sc_gap != "" ? $this->sc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 12);

        $containerCount = count($container);
        $sortId = (int) $this->sc_sortid;

        $this->Template->gap = match ($containerCount) {
            2 => ['left' => floor(0.5 * $gap_value) . px],
            3 => match ($sortId) {
                1 => ['right' => floor(0.333 * $gap_value) . px, 'left' => floor(0.333 * $gap_value) . px],
                2 => ['left' => ceil(0.666 * $gap_value) . px],
            },
            4 => match ($sortId) {
                1 => ['right' => floor(0.5 * $gap_value) . px, 'left' => floor(0.25 * $gap_value) . px],
                2 => ['right' => floor(0.25 * $gap_value) . px, 'left' => ceil(0.5 * $gap_value) . px],
                3 => ['left' => ceil(0.75 * $gap_value) . px],
            },
            5 => match ($sortId) {
                1 => ['right' => floor(0.6 * $gap_value) . px, 'left' => floor(0.2 * $gap_value) . px],
                2 => ['right' => floor(0.4 * $gap_value) . px, 'left' => ceil(0.4 * $gap_value) . px],
                3 => ['right' => floor(0.2 * $gap_value) . px, 'left' => ceil(0.6 * $gap_value) . px],
                4 => ['left' => ceil(0.8 * $gap_value) . px],
            },
        };
    }

    public static function getSubscribedServices(): array
    {
        return [ColumnsetContainer::class];
    }
}
