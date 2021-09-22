<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\SubColumnsBootstrapBundle\EventListener\Contao;


use Contao\Config;

class LoadDataContainerListener
{
    public function __invoke(string $table): void
    {
        if ('tl_columnset' !== $table || !isset($GLOBALS['TL_SUBCL'][Config::get('subcolumns')])) {
            return;
        }

        $sizes = $GLOBALS['TL_SUBCL'][Config::get('subcolumns')]['sizes'];

        foreach ($sizes as $size) {
            $GLOBALS['TL_DCA']['tl_columnset']['fields']['columnset_'.$size] = [
                'label'         => &$GLOBALS['TL_LANG']['tl_columnset']['columnset_'.$size],
                'exclude'       => true,
                'inputType'     => 'multiColumnWizard',
                'load_callback' => [
                    ['HeimrichHannot\SubColumnsBootstrapBundle\Backend\ColumnSet', 'createColumns'],
                ],
                'eval'          => [
                    'includeBlankOption' => true,
                    'columnFields'       => [
                        'width'  => [
                            'label'     => $GLOBALS['TL_LANG']['tl_columnset']['width'],
                            'inputType' => 'select',
                            'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                            'eval'      => ['style' => 'width: 100px;'],
                        ],
                        'offset' => [
                            'label'     => $GLOBALS['TL_LANG']['tl_columnset']['offset'],
                            'inputType' => 'select',
                            'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                            'eval'      => ['style' => 'width: 100px;', 'includeBlankOption' => true],
                        ],
                        'order'  => [
                            'label'     => $GLOBALS['TL_LANG']['tl_columnset']['order'],
                            'inputType' => 'select',
                            'options'   => [
                                'order-1',
                                'order-2',
                                'order-3',
                                'order-4',
                                'order-5',
                                'order-6',
                                'order-7',
                                'order-8',
                                'order-9',
                                'order-10',
                                'order-11',
                                'order-12',
                            ],
                            'eval'      => ['style' => 'width: 160px;', 'includeBlankOption' => true],
                        ],
                    ],
                    'buttons'            => ['copy' => false, 'delete' => false],
                ],
                'sql'           => "blob NULL",
            ];
        }
    }
}