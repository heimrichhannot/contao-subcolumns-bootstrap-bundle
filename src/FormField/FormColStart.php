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
     *
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
        $arrColor = StringUtil::deserialize($this->fsc_color);

        if (count($arrColor) === 2 && empty($arrColor[1])) {
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
            $this->Template = new BackendTemplate('be_subcolumns');
            $this->Template->setColor = $this->compileColor($arrColor);
            $this->Template->colsetTitle = '### COLUMNSET START '.$this->fsc_type.' <strong>'.$this->fsc_name.'</strong> ###';
            $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'],$GLOBALS['TL_LANG']['MSC']['sc_first']);

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
                $arrColset[$i][0] . ($i === 0 ? ' active' : ''),
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
        $this->Template->hint = sprintf($GLOBALS['TL_LANG']['MSC']['contentAfter'], $GLOBALS['TL_LANG']['MSC']['sc_first']);

        return $this->Template->parse();
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate(): string
    {
        $this->strSet = SubColumnsBootstrapBundle::getProfile();

        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack = System::getContainer()->get('request_stack');

        if ($scopeMatcher->isBackendRequest($requestStack->getCurrentRequest())) {
            return $this->generateBackend();
        }

        /**
         * CSS Code in das Pagelayout einfügen
         */
        $mainCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css'] ?? null;
        $IEHacksCSS = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['ie'] ?? false;
        $cssCallback = $GLOBALS['TL_SUBCL'][$this->strSet]['files']['css_callback'] ?? null;
        if ($cssCallback && is_callable($cssCallback))
        {
            $mainCSS = call_user_func($cssCallback);
        }
        if ($mainCSS) {
            $GLOBALS['TL_CSS']['subcolumns'] = $mainCSS;
        }

        $GLOBALS['TL_HEAD']['subcolumns'] = $IEHacksCSS ? '<!--[if lte IE 7]><link href="' . $IEHacksCSS . '" rel="stylesheet" type="text/css" /><![endif]--> ' : '';

        $container = $GLOBALS['TL_SUBCL'][$this->strSet]['sets'][$this->fsc_type];
        $useGap = $GLOBALS['TL_SUBCL'][$this->strSet]['gap'];

        $objTemplate = new FrontendTemplate($this->strColTemplate);

        if ($this->fsc_gapuse && $useGap)
        {
            $gap_value = $this->fsc_gap != "" ? $this->fsc_gap : ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?: 12);

            $objTemplate->gap = match (count($container)) {
                2 => ['right' => ceil(0.5 * $gap_value) . px],
                3 => ['right' => ceil(0.666 * $gap_value) . px],
                4 => ['right' => ceil(0.75 * $gap_value) . px],
                5 => ['right' => ceil(0.8 * $gap_value) . px]
            };
        }

        #$container = unserialize($this->sc_container);
        $objTemplate->column = $container[0][0] . ' col_1 first';
        $objTemplate->inside = $container[0][1] ?? '';
        $objTemplate->useInside = $GLOBALS['TL_SUBCL'][$this->strSet]['inside'];

        $objTemplate->scclass = trim(sprintf('%s %s colcount_%s %s sc-type-%s',
            $this->fsc_equalize ? 'equalize ' : '',
            $GLOBALS['TL_SUBCL'][$this->strSet]['scclass'],
            count($container),
            $this->strSet,
            $this->sc_type
        ));
        $objTemplate->scclass .= $this->class ? ' ' . $this->class : '';

        return $objTemplate->parse();
    }
}