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
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use Contao\StringUtil;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

#[AsHook("loadDataContainer")]
class LoadDataContainerListener
{
    public function __invoke(string $table): void
    {
        if ($table === 'tl_content')
        {
            SubColumnsBootstrapBundle::setProfile('bootstrap3');
            return;
        }

        if ('tl_columnset' !== $table) {
            return;
        }

        $sizes = $GLOBALS['TL_SUBCL'][Config::get('subcolumns')]['sizes'] ?? null;
        if (!$sizes) {
            return;
        }

        foreach ($sizes as $size) {
            $GLOBALS['TL_DCA']['tl_columnset']['fields']["columnset_$size"] = static::createSizeField();
        }
    }

    public static function createSizeField(): array
    {
        return [
            'exclude'       => true,
            'inputType'     => 'multiColumnWizard',
            'load_callback' => [[static::class, 'createColumns']],
            'eval'          => [
                'includeBlankOption' => true,
                'columnFields'       => [
                    'width'  => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['width'],
                        'inputType' => 'select',
                        'options'   => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        'eval'      => ['style' => 'width: 100px;'],
                    ],
                    'offset' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['offset'],
                        'inputType' => 'select',
                        'options'   => ['reset', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        'eval'      => [
                            'style' => 'width: 100px;',
                            'includeBlankOption' => true
                        ],
                    ],
                    'order'  => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_columnset']['order'],
                        'inputType' => 'select',
                        'options'   => [
                            'order-1', 'order-2', 'order-3', 'order-4',  'order-5',  'order-6',
                            'order-7', 'order-8', 'order-9', 'order-10', 'order-11', 'order-12',
                        ],
                        'eval' => ['style' => 'width: 160px;', 'includeBlankOption' => true],
                    ],
                ],
                'buttons' => ['copy' => false, 'delete' => false],
            ],
            'sql' => "blob NULL",
        ];
    }

    /**
     * create a MCW row for each column
     *
     * @param string $value deserializable value
     * @param DataContainer $mcw multi column wizard or DC_Table
     * @return mixed
     */
    public static function createColumns(string $value, DataContainer $mcw): mixed
    {
        $columns = (int) $mcw->activeRecord->columns;
        $value   = StringUtil::deserialize($value, true);
        $count   = count($value);

        if ($count == 0) { // initialize columns
            for ($i = 0; $i < $columns; $i++) {
                $value[$i]['width'] = floor(12 / $columns);
            }
        }
        elseif ($count > $columns) // reduce columns if necessary
        {
            $count = count($value) - $columns;

            for ($i = 0; $i < $count; $i++) {
                array_pop($value);
            }
        }
        else // make sure that column numbers has not changed
        {
            for ($i = 0; $i < ($columns - $count); $i++) {
                $value[$i + $count]['width'] = floor(12 / $columns);
            }
        }

        return $value;
    }
}
