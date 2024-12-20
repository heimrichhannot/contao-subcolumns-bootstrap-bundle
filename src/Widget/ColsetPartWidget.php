<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Widget;

use Contao\FrontendTemplate;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\SubColumnsBootstrapBundle\Helper\ElementHelper;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColsetPartWidget extends Widget
{
    public const TYPE = 'formcolpart';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'form_colset';
    protected $strColTemplate = 'ce_colsetPart';


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

        $arrCounts = array('1'=>'second','2'=>'third','3'=>'fourth','4'=>'fifth');

        $objTemplate = new FrontendTemplate($this->strColTemplate);

        if ($this->fsc_gapuse == 1) {
            $gap_value = $this->fsc_gap != "" ? $this->fsc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ? $GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] : 12);
            $gap_unit = 'px';

            if (count($container) == 2) {
                $objTemplate->gap = array('left' => floor(0.5 * $gap_value) . $gap_unit);
            } elseif (count($container) == 3) {
                switch ($this->fsc_sortid) {
                    case 1:
                        $objTemplate->gap = array('right' => floor(0.333 * $gap_value) . $gap_unit, 'left' => floor(0.333 * $gap_value) . $gap_unit);
                        break;
                    case 2:
                        $objTemplate->gap = array('left' => ceil(0.666 * $gap_value) . $gap_unit);
                        break;
                }
            } elseif (count($container) == 4) {
                switch ($this->fsc_sortid) {
                    case 1:
                        $objTemplate->gap = array('right' => floor(0.5 * $gap_value) . $gap_unit, 'left' => floor(0.25 * $gap_value) . $gap_unit);
                        break;
                    case 2:
                        $objTemplate->gap = array('right' => floor(0.25 * $gap_value) . $gap_unit, 'left' => ceil(0.5 * $gap_value) . $gap_unit);
                        break;
                    case 3:
                        $objTemplate->gap = array('left' => ceil(0.75 * $gap_value) . $gap_unit);
                        break;
                }
            } elseif (count($container) == 5) {
                switch ($this->fsc_sortid) {
                    case 1:
                        $objTemplate->gap = array('right' => floor(0.6 * $gap_value) . $gap_unit, 'left' => floor(0.2 * $gap_value) . $gap_unit);
                        break;
                    case 2:
                        $objTemplate->gap = array('right' => floor(0.4 * $gap_value) . $gap_unit, 'left' => ceil(0.4 * $gap_value) . $gap_unit);
                        break;
                    case 3:
                        $objTemplate->gap = array('right' => floor(0.2 * $gap_value) . $gap_unit, 'left' => ceil(0.6 * $gap_value) . $gap_unit);
                        break;
                    case 4:
                        $objTemplate->gap = array('left' => ceil(0.8 * $gap_value) . $gap_unit);
                        break;
                }
            }
        }

        $objTemplate->column = $container[$this->fsc_sortid][0] . ' col_' . ($this->fsc_sortid + 1) . (($this->fsc_sortid == count($container) - 1) ? ' last' : '');
        $objTemplate->inside = $container[$this->fsc_sortid][1] ?? '';
        $objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        return $objTemplate->parse();
    }

    /**
     * @param ElementHelper $helper
     * @return string
     */
    protected function generateBackend(ElementHelper $helper, array $arrColset): string
    {
        switch ($this->fsc_sortid) {
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

        $arrColor = ElementHelper::getColor($this->fsc_color);

        if (!$GLOBALS['TL_SUBCL'][$this->strSet]['files']['css']) {
            return $helper->renderBackendTemplate(
                $helper->generateTitle($this->sc_columnset, $this->fsc_name),
                $arrColor,
                null,
                sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID)
            );
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'];

        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = count($arrColset);

        $strMiniset = '<div class="colsetexample ' . $strSCClass . '">';

        for ($i = 0; $i < $intCountContainers; $i++) {
            $arrPresentColset = $arrColset[$i];
            $strMiniset .= '<div class="' . $arrPresentColset[0] . ($i == $this->fsc_sortid ? ' active' : '') . '">' . ($blnInside ? '<div class="' . $arrPresentColset[1] . '">' : '') . ($i + 1) . ($blnInside ? '</div>' : '') . '</div>';
        }

        $strMiniset .= '</div>';

        return $helper->renderBackendTemplate(
            $helper->generateTitle($this->sc_columnset, $this->fsc_name),
            $arrColor,
            $helper->generateBackendMiniTable($intCountContainers, (int)$this->fsc_sortid),
            sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID)
        );
    }
}