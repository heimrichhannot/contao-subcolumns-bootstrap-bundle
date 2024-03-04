<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Util;

use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\AbstractColsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\AbstractColsetParentContainer;

class ColsetDcaUtil
{
    /**
     * @param array $dca Provide the data container array, e.g. $GLOBALS['TL_DCA']['tl_content'].
     * @param class-string<AbstractColsetContainer> $colsetContainerClass
     * @return void
     */
    public static function attachToDCA(array &$dca, string $colsetContainerClass): void
    {
        static::attachCallbacks($dca, $colsetContainerClass);
        static::attachFields($dca, $colsetContainerClass);
        static::attachPalettes($dca);
    }

    /**
     * @param array &$dca
     * @param class-string<AbstractColsetContainer> $colsetContainerClass
     * @return void
     */
    public static function attachCallbacks(array &$dca, string $colsetContainerClass): void
    {
        /** @var AbstractColsetContainer $colsetContainerClass IDE support */
        // $dca['config']['onload_callback'][] = [$colsetContainerClass, 'appendColumnsetIdToPalette'];
        $dca['config']['onload_callback'][] = [$colsetContainerClass, 'createPalette'];
        $dca['config']['onsubmit_callback'][] = [$colsetContainerClass, 'onUpdate'];
        $dca['config']['onsubmit_callback'][] = [$colsetContainerClass, 'setElementProperties'];
        $dca['config']['ondelete_callback'][] = [$colsetContainerClass, 'onDelete'];
        $dca['config']['oncopy_callback'][] = [$colsetContainerClass, 'onCopy'];
        $dca['fields']['invisible']['save_callback'][] = [$colsetContainerClass, 'toggleAdditionalElements'];
        $dca['fields']['sc_type']['options_callback'] = [$colsetContainerClass, 'getAllTypes'];
    }

    public static function attachPalettes(array &$dca): void
    {
        $dca['palettes']['colsetStart'] = '{type_legend},type;'
            . '{colset_legend},sc_name,sc_type,sc_color,sc_gapdefault,sc_gap;'  # todo: remove gap? + remove sc_type
            . '{colheight_legend:hide},sc_equalize;'  # equalize // todo: remove?
            . '{protected_legend:hide},protected;'
            . '{expert_legend:hide},guests,invisible,cssID,space';
        $dca['palettes']['colsetPart'] = 'cssID';
        $dca['palettes']['colsetEnd'] = $dca['palettes']['default'];
    }

    /**
     * @param array $dca
     * @param class-string<AbstractColsetContainer> $colsetContainerClass
     * @return void
     */
    public static function attachFields(array &$dca, string $colsetContainerClass): void
    {
        $dca['fields'] = array_merge($dca['fields'], static::createDataContainerFields($colsetContainerClass));

        $dca['fields']['sc_name']['eval']['tl_class'] = 'w50';
        $dca['fields']['sc_type']['eval']['submitOnChange'] = true;
        $dca['fields']['sc_type']['eval']['mandatory'] = false;
    }

    /**
     * @param class-string<AbstractColsetContainer> $colsetContainerClass
     * @return array
     */
    public static function createDataContainerFields(string $colsetContainerClass, string $colsetParentClass = null): array
    {
        /**
         * @var AbstractColsetContainer $colsetContainerClass IDE support
         * @var AbstractColsetParentContainer $colsetParentClass IDE support
         */
        return [
            'sc_name' => [
                'inputType' => 'text',
                'save_callback' => [[$colsetContainerClass, 'onNameSaveCallback']],
                'eval' => [
                    'maxlength' => '255',
                    'unique' => true,
                    'spaceToUnderscore' => true,
                ],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            /*'sc_gap' => [
                'default' => ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 0),
                'inputType' => 'text',
                'eval' => ['maxlength' => '4', 'regxp' => 'digit', 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],*/
            /*'sc_type' => [
                'inputType' => 'select',
                'options_callback' => [$colsetContainerClass, 'getAllSubcolumnTypeOptions'],
                'eval' => [
                    'includeBlankOption' => true,
                    'mandatory' => true,
                    'tl_class' => 'w50',
                ],
                'sql' => "varchar(64) NOT NULL default ''",
            ],*/
            /*'sc_gapdefault' => [
                'default' => 1,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'clr m12 w50'],
                'sql' => "char(1) NOT NULL default '1'",
            ],*/
            'sc_equalize' => [
                'inputType' => 'checkbox',
                'eval' => [],
                'sql' => "char(1) NOT NULL default ''",
            ],
            /*'sc_color' => [
                'inputType' => 'text',
                'eval' => [
                    'maxlength' => 6,
                    'multiple' => true,
                    'size' => 2,
                    'colorpicker' => true,
                    'isHexColor' => true,
                    'decodeEntities' => true,
                    'tl_class' => 'w50 wizard',
                ],
                'sql' => "varchar(64) NOT NULL default ''",
            ],*/
            'sc_parent' => [
                'sql' => "int(10) unsigned NOT NULL default '0'",
            ],
            'sc_childs' => [
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'sc_sortid' => [
                'sql' => "int(2) unsigned NOT NULL default '0'",
            ],

            /*'columnset_id'         => [
                'exclude'          => true,
                'inputType'        => 'select',
                'options_callback' => [$colsetContainerClass, 'getColumnsetIdOptions'],
                'eval'             => [
                    'mandatory' => false,
                    'submitOnChange' => true,
                    'tl_class' => 'w50',
                ],
                'wizard'           => [[$colsetContainerClass, 'getColumnsetIdWizard']],
                'sql'              => "varchar(10) NOT NULL default ''",
            ],*/
            'sc_addContainer' => [
                'exclude'   => true,
                'inputType' => 'checkbox',
                'eval'      => ['tl_class' => 'w50'],
                'sql'       => "char(1) NOT NULL default ''",
            ],
            'sc_columnset' => [
                'inputType'	=> 'select',
                'options_callback' => [$colsetContainerClass, 'getColsetOptions'],
                'eval' => [
                    'maxlength' => '255',
                    'spaceToUnderscore' => true,
                    'mandatory' => true,
                ],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
        ];
    }
}