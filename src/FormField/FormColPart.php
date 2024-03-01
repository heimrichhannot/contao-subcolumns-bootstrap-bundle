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
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use HeimrichHannot\SubColumnsBootstrapBundle\Util\ColorUtil;
use const HeimrichHannot\SubColumnsBootstrapBundle\Util\px;

/**
 * Class FormColPart
 *
 * Form field "explanation".
 * @copyright  Felix Pfeiffer : Neue Medien 2010
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @package    Subcolumns
 */
class FormColPart extends Widget
{
    const TYPE = 'formcolpart';

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'form_colset';
    protected string $strColTemplate = 'ce_colsetPart';

    /**
     * Do not validate
     */
    public function validate(): void
    {
        return;
    }

    protected function generateBackend(): string
    {
        $msc = &$GLOBALS['TL_LANG']['MSC'];

        $colID = match ($this->fsc_sortid) {
            1 => $msc['sc_second'],
            2 => $msc['sc_third'],
            3 => $msc['sc_fourth'],
            4 => $msc['sc_fifth'],
            default => ''
        };

        $arrColor = StringUtil::deserialize($this->fsc_color);

        if (count($arrColor) === 2 && empty($arrColor[1])) {
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
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$colID);

            return $this->Template->parse();
        }

        $GLOBALS['TL_CSS']['subcolumns'] = 'bundles/subcolumnsbootstrap/css/be_style.css';
        $GLOBALS['TL_CSS']['subcolumns_set'] = $css;

        $arrColset = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
        $strSCClass = $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'];
        $blnInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $intCountContainers = count($GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type]);

        $strMiniset = '';
        for ($i = 0; $i < $intCountContainers; $i++)
        {
            $strMiniset .= sprintf('<div class="%s">%s</div>',
                $arrColset[$i][0] . ($i == $this->fsc_sortid ? ' active' : ''),
                $blnInside
                    ? sprintf('<div class="%s">%s</div>', $arrColset[$i][1], $i + 1)
                    : $i + 1
            );
        }
        $strMiniset = "<div class=\"colsetexample $strSCClass\">$strMiniset</div>";

        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $arrColor;
        $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
        $this->Template->visualSet = $strMiniset;
        $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $colID);

        return $this->Template->parse();
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
            return $this->generateBackend();
        }

        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];

        $objTemplate = new FrontendTemplate($this->strColTemplate);

        $objTemplate->column = $container[$this->fsc_sortid][0] . ' col_' . ($this->fsc_sortid + 1) . (($this->fsc_sortid == count($container) - 1) ? ' last' : '');
        $objTemplate->inside = $container[$this->fsc_sortid][1] ?? '';
        $objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'] ?? false;

        if ($this->fsc_gapuse == 1) {
            return $objTemplate->parse();
        }

        $gap_value = $this->fsc_gap != "" ? $this->fsc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?: 12);

        $intSortId = (int) $this->fsc_sortid;

        $objTemplate->gap = match (count($container)) {
            2 => ['left' => floor(0.5 * $gap_value) . px],
            3 => match ($intSortId) {
                1 => ['right' => floor(0.333 * $gap_value) . px, 'left' => floor(0.333 * $gap_value) . px],
                2 => ['left' => ceil(0.666 * $gap_value) . px]
            },
            4 => match ($intSortId) {
                1 => ['right' => floor(0.5 * $gap_value) . px, 'left' => floor(0.25 * $gap_value) . px],
                2 => ['right' => floor(0.25 * $gap_value) . px, 'left' => ceil(0.5 * $gap_value) . px],
                3 => ['left' => ceil(0.75 * $gap_value) . px]
            },
            5 => match ($intSortId) {
                1 => ['right' => floor(0.6 * $gap_value) . px, 'left' => floor(0.2 * $gap_value) . px],
                2 => ['right' => floor(0.4 * $gap_value) . px, 'left' => ceil(0.4 * $gap_value) . px],
                3 => ['right' => floor(0.2 * $gap_value) . px, 'left' => ceil(0.6 * $gap_value) . px],
                4 => ['left' => ceil(0.8 * $gap_value) . px]
            }
        };

        return $objTemplate->parse();
    }

    /**
     * Compile a color value and return a hex or rgba color
     * @param mixed
     * @param boolean
     * @param array
     * @return string
     */
    protected function compileColor($color): string
    {
        if (!is_array($color))
        {
            return "#$color";
        }
        elseif (empty($color[1]))
        {
            return "#$color[0]";
        }
        else
        {
            return 'rgba(' . implode(',', $this->convertHexColor($color[0], $blnWriteToFile ?? false, $vars ?? [])) . ','. ($color[1] / 100) .')';
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
    protected function convertHexColor($color, bool $blnWriteToFile = false, array $vars = []): array
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
