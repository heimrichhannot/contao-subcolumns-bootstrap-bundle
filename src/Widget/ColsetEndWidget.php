<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Widget;

use Contao\FrontendTemplate;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\SubColumnsBootstrapBundle\Helper\ElementHelper;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetEndWidget extends Widget
{
    public const TYPE = 'formcolend';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'form_colset';
    protected $strColTemplate = 'ce_colsetEnd';

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

        $this->strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ?: 'yaml3';
        $this->strSet = SubColumnsBootstrapBundle::filterProfile($this->strSet);

        try {
            $container = $helper->getColumnset($this->sc_columnset);
        } catch (\Exception $e) {
            return '';
        }

        if (TL_MODE == 'BE')
        {
            return $this->generateBackend($helper, $container);
        }

        $objTemplate = new FrontendTemplate($this->strColTemplate);
        $objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];
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
                null
            );
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];

        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = count($arrColset);

        $strMiniset = '<div class="colsetexample final ' . $strSCClass . '">';

        for ($i = 0; $i < $intCountContainers; $i++) {
            $arrPresentColset = $arrColset[$i];
            $strMiniset .= '<div class="' . $arrPresentColset[0] . '">' . ($blnInside ? '<div class="' . $arrPresentColset[1] . '">' : '') . ($i + 1) . ($blnInside ? '</div>' : '') . '</div>';
        }

        $strMiniset .= '</div>';

        return $helper->renderBackendTemplate(
            $helper->generateTitle($this->sc_columnset, $this->fsc_name),
            $arrColor,
            $helper->generateBackendMiniTable($intCountContainers, $intCountContainers),
            null
        );
    }
}