<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use Symfony\Component\HttpFoundation\RequestStack;

class ColumnsetContainer
{
    public function __construct(
        private Connection $connection,
        private RequestStack $requestStack,
        private ScopeMatcher $scopeMatcher
    ) {}

    /**
     * @throws \Doctrine\DBAL\Exception
     * @Callback(table="tl_columnset", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$this->scopeMatcher->isBackendRequest($request) || 'edit' !== Input::get('act')) {
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

    public function getAllProfileOptions(): array
    {
        $profiles = [];

        foreach ($GLOBALS['TL_SUBCL'] as $k => $v)
        {
            $profiles[$k] = $v['label'] ?? $k;
        }

        return $profiles;
    }
}