<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Widget;

use Contao\FrontendTemplate;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\Subcolumns\SubcolumnTypes;
use HeimrichHannot\SubColumnsBootstrapBundle\Helper\ElementHelper;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetStartWidget extends Widget
{
    public const TYPE = 'formcolstart';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'form_colset';
    protected $strColTemplate = 'ce_colsetStart';

    /**
     * Do not validate
     */
    public function validate()
    {
        return;
    }

    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        /** @var ElementHelper $helper */
        $helper = System::getContainer()->get(ElementHelper::class);
        $colSet = $helper->getSet($this->sc_columnset);
        if (!$colSet) {
            return $this->Template->parse();
        }

        $container = $helper->getColumnset($this->sc_columnset);
        $this->strSet = SubcolumnTypes::compatSetType();

        if (TL_MODE == 'BE') {
            return $this->generateBackend($helper, $container);
        }

        /**
         * CSS Code in das Pagelayout einfügen
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$colSet->name]['files']['css'] ?? '';
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$colSet->name]['files']['ie'] ?? false;

        $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        $GLOBALS['TL_HEAD']['subcolumns'] = $IEHacksCSS ? '<!--[if lte IE 7]><link href="' . $IEHacksCSS . '" rel="stylesheet" type="text/css" /><![endif]--> ' : '';

        $objTemplate = new FrontendTemplate($this->strColTemplate);

        $helper->legacyGridFormatting($objTemplate, $colSet, $this->fsc_gapuse, $this->fsc_gap);

        $scTypeClass = ' col-' . $this->fsc_type;

        if (SubColumnsBootstrapBundle::validProfile($colSet->name)) {
            $scTypeClass = '';
        }

        $objTemplate->scclass = ($this->fsc_equalize ? 'equalize ' : '') . $GLOBALS['TL_SUBCL'][$colSet->name]['scclass'] . ' colcount_' . count($container) . ' ' . $colSet->name . $scTypeClass . (' sc-type-' . $this->sc_type) . ($this->class ? ' ' . $this->class : '');
        return $objTemplate->parse();
    }

    /**
     * @return string
     */
    protected function generateBackend(ElementHelper $helper, array $arrColset): string
    {
        $arrColor = ElementHelper::getColor($this->fsc_color);

        if (!$GLOBALS['TL_SUBCL'][$this->strSet]['files']['css']) {
            return $helper->renderBackendTemplate(
                $helper->generateTitle($this->sc_columnset, $this->fsc_name),
                $arrColor,
                null,
                sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first'])
            );
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumnsbootstrap/scss/contao-subcolumns-bootstrap-bundle.be.scss';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ? $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] : false;

        $intCountContainers = count($arrColset);

        return $helper->renderBackendTemplate(
            $helper->generateTitle($this->sc_columnset, $this->fsc_name),
            $arrColor,
            $helper->generateBackendMiniTable($intCountContainers, 0),
            sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first'])
        );
    }
}