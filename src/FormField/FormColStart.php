<?php

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Felix Pfeiffer : Neue Medien 2007 - 2012
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @package    Subcolumns
 * @license    CC-A 2.0
 * @filesource
 */

namespace HeimrichHannot\SubColumnsBootstrapBundle\FormField;

use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\Subcolumns\SubcolumnTypes;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

/**
 * Class FormColStart
 *
 * Form field "explanation".
 * @copyright  Felix Pfeiffer : Neue Medien 2010
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @package    Subcolumns
 */
class FormColStart extends Widget
{
    const TYPE = 'formcolstart';

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'form_colset';
	protected $strColTemplate = 'ce_colsetStart';


	/**
	 * Do not validate
	 */
	public function validate(): void
    {
		return;
	}

    protected function generateBackend(): string
    {
        $arrColor = unserialize($this->fsc_color);

        if(count($arrColor) === 2 && empty($arrColor[1])) {
            $arrColor = '';
        } else {
            $arrColor  = $this->compileColor($arrColor);
        }

        if(!$GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'])
        {
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$GLOBALS['TL_LANG']['MSC']['sc_first']);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'system/modules/Subcolumns/assets/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ? $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] : false;

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type]);

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
        $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
        $this->Template->visualSet = $strMiniset;
        $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$GLOBALS['TL_LANG']['MSC']['sc_first']);

        return $this->Template->parse();
    }

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
        $this->strSet = SubcolumnTypes::compatSetType();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest()))
        {
            return $this->generateBackend();
        }

		/**
		 * CSS Code in das Pagelayout einfügen
		 */
        $mainCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ? $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] : '';
		$IEHacksCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['ie'] ?? false;
		
		$GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
		$GLOBALS['TL_HEAD']['subcolumns'] = $IEHacksCSS ? '<!--[if lte IE 7]><link href="'.$IEHacksCSS.'" rel="stylesheet" type="text/css" /><![endif]--> ' : '';
		
		$container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
		$useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];
		
		$objTemplate = new FrontendTemplate($this->strColTemplate);
		
		if($this->fsc_gapuse == 1 && $useGap)
		{
            $gap_value = $this->fsc_gap != "" ? $this->fsc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ? $GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] : 12);
			$gap_unit = 'px';
			
			if(count($container) == 2)
			{
				$objTemplate->gap = array('right'=>ceil(0.5*$gap_value).$gap_unit);
			}
			elseif (count($container) == 3)
			{
				$objTemplate->gap = array('right'=>ceil(0.666*$gap_value).$gap_unit);

			}
			elseif (count($container) == 4)
			{
				$objTemplate->gap = array('right'=>ceil(0.75*$gap_value).$gap_unit);
			}
			elseif (count($container) == 5)
			{
				$objTemplate->gap = array('right'=>ceil(0.8*$gap_value).$gap_unit);
			}
		}
		
		#$container = unserialize($this->sc_container);
		$objTemplate->column = $container[0][0] . ' col_1' . ' first';
		$objTemplate->inside = $container[0][1] ?? '';
		$objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $scTypeClass = ' col-' . $this->fsc_type;

        if (class_exists(SubColumnsBootstrapBundle::class)
            && SubColumnsBootstrapBundle::validProfile($this->strSet))
        {
            $scTypeClass = '';
        }

		$objTemplate->scclass = ($this->fsc_equalize ? 'equalize ' : '') . $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'] . ' colcount_' . count($container) . ' ' . $this->strSet . $scTypeClass . (' sc-type-' . $this->sc_type) . ($this->class ? ' ' . $this->class : '');
		return $objTemplate->parse();
	}

    /**
     * Compile a color value and return a hex or rgba color
     * @param mixed
     * @param boolean
     * @param array
     * @return string
     */
    protected function compileColor($color)
    {
        if (!is_array($color))
        {
            return "#$color";
        }
        elseif (!isset($color[1]) || empty($color[1]))
        {
            return "#$color[0]";
        }
        else
        {
            return 'rgba(' . implode(',', $this->convertHexColor($color[0], $blnWriteToFile, $vars)) . ','. ($color[1] / 100) .')';
        }
    }

    /**
     * Convert hex colors to rgb
     * @param string
     * @param boolean
     * @param array
     * @return array
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    protected function convertHexColor($color, $blnWriteToFile=false, $vars=array()): array
    {
        // Support global variables
        if (strncmp($color, '$', 1) === 0)
        {
            if (!$blnWriteToFile)
            {
                return [$color];
            }
            else
            {
                $color = str_replace(array_keys($vars), array_values($vars), $color);
            }
        }

        $rgb = [];

        // Try to convert using bitwise operation
        if (strlen($color) == 6)
        {
            $dec = hexdec($color);
            $rgb['red'] = 0xFF & ($dec >> 0x10);
            $rgb['green'] = 0xFF & ($dec >> 0x8);
            $rgb['blue'] = 0xFF & $dec;
        }

        // Shorthand notation
        elseif (strlen($color) == 3)
        {
            $rgb['red'] = hexdec(str_repeat(substr($color, 0, 1), 2));
            $rgb['green'] = hexdec(str_repeat(substr($color, 1, 1), 2));
            $rgb['blue'] = hexdec(str_repeat(substr($color, 2, 1), 2));
        }

        return $rgb;
    }

}