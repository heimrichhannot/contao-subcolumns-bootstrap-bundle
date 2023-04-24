<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Message;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use Symfony\Component\HttpFoundation\RequestStack;

class ColumnsetContainer
{
    private Connection $connection;
    private RequestStack $requestStack;
    private ScopeMatcher $scopeMatcher;

    public function __construct(Connection $connection, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * @Callback(table="tl_columnset", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$this->scopeMatcher->isBackendRequest($request) || 'edit' !== $request->get('act')) {
            return;
        }

        [$sizes, $arrDca, $size] = $this->preparePalette((int)$dc->id);

        $sizes = $GLOBALS['TL_SUBCL'][Config::get('subcolumns')]['sizes'] ?? null;
        if (!$sizes) {
            return;
        }

        $tableColumns = $this->connection->getSchemaManager()->listTableColumns('tl_columnset');

        foreach ($sizes as $size)
        {
            if (!array_key_exists('columnset_'.strtolower($size), $tableColumns)) {
                Message::addError($GLOBALS['TL_LANG']['ERR']['huhSubColMissingTableRow']);
                return;
            }
        }
    }

    protected function preparePalette(int $id): array
    {
        $model  = ColumnsetModel::findByPk($id);
        $sizes  = array_merge(StringUtil::deserialize($model->sizes, true));
        $arrDca = &$GLOBALS['TL_DCA']['tl_columnset'];

        $pm = PaletteManipulator::create();

        foreach ($sizes as $size) {
            $pm->addField('columnset_' . $size, 'sizes', PaletteManipulator::POSITION_APPEND);
        }
        $pm->applyToPalette('default', 'tl_columnset');
        return [$sizes, $arrDca, $size];
    }
}