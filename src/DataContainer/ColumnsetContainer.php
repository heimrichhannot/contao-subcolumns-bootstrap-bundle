<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\Config;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Image;
use Contao\Message;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetIdentifier;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use Symfony\Component\HttpFoundation\RequestStack;

class ColumnsetContainer
{
    public function __construct(
        private Connection $connection,
        private RequestStack $requestStack,
        private ScopeMatcher $scopeMatcher,
        private ContaoCsrfTokenManager $tokenManager
    ) {}

    /**
     * @throws \Doctrine\DBAL\Exception
     * @Callback(table="tl_columnset", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$this->scopeMatcher->isBackendRequest($request) || 'edit' !== $request->get('act')) {
            return;
        }

        $this->preparePalette((int)$dc->id);

        $sizes = $GLOBALS['TL_SUBCL'][Config::get('subcolumns')]['sizes'] ?? null;
        if (!$sizes) {
            return;
        }

        $tableColumns = $this->connection->createSchemaManager()->listTableColumns('tl_columnset');

        foreach ($sizes as $size)
        {
            if (!array_key_exists('columnset_'.strtolower($size), $tableColumns)) {
                Message::addError($GLOBALS['TL_LANG']['ERR']['huhSubColMissingTableRow']);
                return;
            }
        }
    }

    protected function preparePalette(int $id): void
    {
        $model  = ColumnsetModel::findByPk($id);
        $sizes  = array_merge(StringUtil::deserialize($model->sizes, true));

        $pm = PaletteManipulator::create();

        foreach ($sizes as $size) {
            $pm->addField('columnset_' . $size, 'columnset_legend', PaletteManipulator::POSITION_APPEND);
        }

        $pm->applyToPalette('default', 'tl_columnset');
    }

    /* ===== ERICs WORK BELOW (this comment is temporary) ===== */

    /**
     * @param string|ColumnsetIdentifier $identifier
     */
    public function getColumnSettings(string|ColumnsetIdentifier $identifier): ?array
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        return match ($identifier->getSource()) {
            'globals' => $this->getGlobalColumnSettings(...$identifier->getParams()),
            'db' => $this->getDBColumnSettings(...$identifier->getParams()),
            default => null,
        };
    }

    private function getGlobalColumnSettings(string $profile, string $columnSet): ?array
    {
        return $GLOBALS['TL_SUBCL'][$profile]['sets'][$columnSet] ?? null;
    }

    public function getGlobalColumnsetProfile(string $profile): ?array
    {
        return $GLOBALS['TL_SUBCL'][$profile] ?? null;
    }

    /**
     * @param ColumnsetIdentifier|string $identifier
     * @return array|null
     */
    public function tryGlobalColumnsetProfileByIdentifier(string $identifier): ?array
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        return $this->getGlobalColumnsetProfile(...$identifier->getParams());
    }

    /**
     * @param ColumnsetIdentifier|string $identifier
     * @return ColumnsetModel|null
     */
    public function tryColumnsetModelByIdentifier(string $identifier): ?ColumnsetModel
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        if ($identifier->getSource() !== 'db') {
            return null;
        }

        return $this->tryColumnsetModel(...$identifier->getParams());
    }


    /**
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    public function tryColumnsetModel(string $table, string $id): ?ColumnsetModel
    {
        if ($table !== 'tl_columnset')
        {
            return null;
        }

        return ColumnsetModel::findByPk($id);
    }

    public function getTitle(string|ColumnsetIdentifier $identifier): string
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);
        $columnsetModel = $this->tryColumnsetModelByIdentifier($identifier);
        $globalProfile = $this->tryGlobalColumnsetProfileByIdentifier($identifier);
        $title = $columnsetModel->title ?? '';
        if (!$title && $globalProfile) {
            $title = $globalProfile['label'] ?? '';
            if ($title) {
                $title .= ':&ensp;' . ($identifier->getParams()[1] ?? '');
            } else {
                $title = implode(' ', $identifier->getParams());
            }
        }
        return $title;
    }

    private function getDBColumnSettings(string $table, string $id): ?array
    {
        $columnsetModel = $this->tryColumnsetModel($table, $id);

        if ($columnsetModel === null) {
            return null;
        }

        $breakpoints = $columnsetModel->getSizes();
        $cssClasses = [];

        foreach ($breakpoints as $breakpoint)
        {
            $colSetup = $columnsetModel->getColumnset($breakpoint);

            if (!$colSetup)
            {
                continue;
            }

            foreach ($colSetup as $i => $col)
            {
                $classes = &$cssClasses[$i];

                if ($width = $col['width']) {
                    $classes[] = "col-$breakpoint-$width";
                }

                if ($offset = $col['offset']) {
                    $offset = ($offset === 'reset') ? 0 : $offset;
                    $classes[] = "offset-$breakpoint-$offset";
                }

                if ($order = $col['order']) {
                    $order = explode('-', $order);
                    if (sizeof($order) === 2) {
                        $classes[] = "$order[0]-$breakpoint-$order[1]";
                    }
                }
            }
        }

        return array_map(function ($arrClasses) {
            return [join(' ', $arrClasses)];
        }, $cssClasses);
    }

    public function columnsetIdWizard(DataContainer $dc): string
    {
        $id = (int)$dc->value;

        if ($id < 1) {
            return '';
        }

        $module = 'columnset';

        $url = Environment::get('url')
            . parse_url(Environment::get('uri'), PHP_URL_PATH);

        if (!$id) {
            return '';
        }

        $label = sprintf(StringUtil::specialchars($GLOBALS['TL_LANG']['tl_columnset']['editalias'][1] ?? 'wizard'), $id);

        return sprintf(
            ' <a href="'.$url.'?do=%s&amp;act=edit&amp;id=%s&amp;popup=1&amp;nb=1&amp;rt=%s" title="%s" '
            . 'style="padding-left: 5px; padding-top: 2px; display: inline-block;" onclick="%s;return false">%s</a>',
            $module,
            $id,
            $this->tokenManager->getDefaultTokenValue(),
            $label,
            1024,
            $label,
            "Backend.openModalIframe({'width':%s,'title':'%s','url':this.href})",
            Image::getHtml('alias.svg', $label, 'style="vertical-align:top"')
        );
    }

    /**
     * get all column-sets
     */
    public function getColumnsetOptions(DataContainer $dc): array
    {
        $collection = ColumnsetModel::findBy(
            'published=1 AND columns',
            $dc->activeRecord->sc_type,
            ['order' => 'title']
        );

        $set = [];

        if ($collection !== null) {
            while ($collection->next()) {
                $set[$collection->id] = $collection->title;
            }
        }

        return $set;
    }

    /**
     * Autogenerate a name for the colset if it has not yet been set
     */
    public function onNameSaveCallback(mixed $varValue, DataContainer $dc): string
    {
        // Generate alias if there is none
        return strlen($varValue) ? $varValue : ('colset.' . $dc->id);
    }

    /**
     * Get the col-sets depending on the selection from the settings
     */
    public function getAllTypes(): array
    {
        $strSet = SubColumnsBootstrapBundle::getProfile();
        return array_keys($GLOBALS['TL_SUBCL'][$strSet]['sets'] ?? []);
    }

    /**
     * @param array $dca Provide the data container array, e.g. $GLOBALS['TL_DCA']['tl_content'].
     * @return void
     */
    public static function attachToDCA(array &$dca): void
    {

    }

    /**
     * @param array $dca
     * @param class-string<AbstractColsetContainer> $colsetContainerClass
     * @return void
     */
    public static function attachCallbacks(array &$dca, string $colsetContainerClass): void
    {
        $dca['config']['onload_callback'][] = [$colsetContainerClass, 'appendColumnsetIdToPalette'];
        $dca['config']['onload_callback'][] = [$colsetContainerClass, 'createPalette'];
        $dca['config']['onsubmit_callback'][] = [$colsetContainerClass, 'onUpdate'];
        $dca['config']['onsubmit_callback'][] = [$colsetContainerClass, 'setElementProperties'];
        $dca['config']['ondelete_callback'][] = [$colsetContainerClass, 'onDelete'];
        $dca['config']['oncopy_callback'][] = [$colsetContainerClass, 'onCopy'];

        $dca['fields']['invisible']['save_callback'][] = [ContentContainer::class, 'toggleAdditionalElements'];
    }

    public static function attachFields(array &$dca): void
    {
        $dca['fields'] = array_merge($dca['fields'], static::createDataContainerFields());
    }

    public static function createDataContainerFields(): array
    {
        return [
            'sc_name' => [
                'inputType' => 'text',
                'save_callback' => [[ColumnsetContainer::class, 'onNameSaveCallback']],
                'eval' => [
                    'maxlength' => '255',
                    'unique' => true,
                    'spaceToUnderscore' => true,
                ],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'sc_gap' => [
                'default' => ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 0),
                'inputType' => 'text',
                'eval' => ['maxlength' => '4', 'regxp' => 'digit', 'tl_class' => 'w50'],
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'sc_type' => [
                'inputType' => 'select',
                'options_callback' => [ColumnsetContainer::class, 'getAllTypes'],
                'eval' => [
                    'includeBlankOption' => true,
                    'mandatory' => true,
                    'tl_class' => 'w50',
                ],
                'sql' => "varchar(64) NOT NULL default ''",
            ],
            'sc_gapdefault' => [
                'default' => 1,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'clr m12 w50'],
                'sql' => "char(1) NOT NULL default '1'",
            ],
            'sc_equalize' => [
                'inputType' => 'checkbox',
                'eval' => [],
                'sql' => "char(1) NOT NULL default ''",
            ],
            'sc_color' => [
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
            ],
            'sc_parent' => [
                'sql' => "int(10) unsigned NOT NULL default '0'",
            ],
            'sc_childs' => [
                'sql' => "varchar(255) NOT NULL default ''",
            ],
            'sc_sortid' => [
                'sql' => "int(2) unsigned NOT NULL default '0'",
            ],

            'columnset_id'         => [
                'exclude'          => true,
                'inputType'        => 'select',
                'options_callback' => [ColumnsetContainer::class, 'getColumnsetOptions'],
                'reference'        => &$GLOBALS['TL_LANG']['tl_content'],
                'eval'             => [
                    'mandatory' => false,
                    'submitOnChange' => true,
                    'tl_class' => 'w50',
                ],
                'wizard'           => [[ColumnsetContainer::class, 'columnsetIdWizard']],
                'sql'              => "varchar(10) NOT NULL default ''",
            ],
            'addContainer' => [
                'exclude'   => true,
                'inputType' => 'checkbox',
                'eval'      => ['tl_class' => 'w50'],
                'sql'       => "char(1) NOT NULL default ''",
            ],
            'sc_columnset' => [
                'inputType'	=> 'select',
                'options_callback' => [ColumnsetContainer::class, 'getOptions'],
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