<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetIdentifier;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class ColumnsetContainer
{
    private Connection $connection;
    private RequestStack $requestStack;
    private ScopeMatcher $scopeMatcher;
    private KernelInterface $kernel;
    private ?array $options = null;

    public function __construct(
        Connection $connection,
        RequestStack $requestStack,
        ScopeMatcher $scopeMatcher,
        KernelInterface $kernel
    )
    {
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
        $this->kernel = $kernel;
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

        $this->preparePalette((int)$dc->id);

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
    public function getColumnSettings($identifier): ?array
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        switch ($identifier->getSource()) {
            case 'globals':
                return $this->getGlobalColumnSettings(...$identifier->getParams());
            case 'db':
                return $this->getDBColumnSettings(...$identifier->getParams());
        }

        return null;
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

    public function getTitle(string $identifier): string
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

    public function getOptions(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        return $this->options = array_merge(
            $this->getOptionsFromDatabase(),
            $this->getOptionsFromGlobals()
        );
    }

    /**
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    private function getOptionsFromDatabase(): array
    {
        try {
            $columnSets = $this->connection
                ->fetchAllAssociative('SELECT id, title, columns, description FROM `tl_columnset` ORDER BY columns');
        } catch (\Exception $e) {
            return [];
        }

        $types = [];
        $dbLegend = $GLOBALS['TL_LANG']['tl_content']['db_legend'] ?? '[DB]';

        foreach ($columnSets as $columnSet)
        {
            $types[$dbLegend]['db.tl_columnset.' . $columnSet['id']] =
                "[{$columnSet['columns']}] "
                . $columnSet['title']
                . (!empty($columnSet['description']) ? " ({$columnSet['description']})" : '');
        }

        return $types;
    }

    private function getOptionsFromGlobals(): array
    {
        $types = [];
        $dbLegend = $GLOBALS['TL_LANG']['tl_content']['globals_legend'] ?? '[GLOBALS]';
        $dbLegend .= " ";

        foreach ($GLOBALS['TL_SUBCL'] as $subType => $config) {
            if (str_contains($subType, 'yaml')) {
                continue;
            }
            foreach ($config['sets'] as $set => $columns) {
                $types[$dbLegend . $config['label']]['globals.' . $subType . '.' . $set] = $set;
            }
        }

        ksort($types);

        return $types;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    private function moveRows($pid, $ptable, $sorting, int $ammount = 128)
    {
        $this->connection
            ->prepare("UPDATE `tl_content` SET sorting = sorting + ? WHERE pid=? AND ptable=? AND sorting > ?")
            ->executeQuery([$ammount, $pid, $ptable, $sorting]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function onUpdate(DataContainer $dc): bool
    {
        /** @var Result $record */
        $record = $dc->activeRecord;
        $colsetIdentifier = $record->sc_columnset ?? null;
        $colset = $this->getColumnSettings($colsetIdentifier);

        if (empty($colset))
        {
            return false;
        }

        $children = StringUtil::deserialize($record->sc_childs ?? null) ?: null;

        return $this->createOrUpdateColset($record, $colsetIdentifier, $colset, $children);
    }

    private function updateEqualColset($record, array $children): bool
    {
        foreach (array_values($children) as $i => $childId)
        {
            $i++;
            $scName = $record->sc_name . ($i === count($children) ? '-End' : ("-Part-$i"));

            $update = [
                'sc_gap' => $record->sc_gap,
                'sc_gapdefault' => $record->sc_gapdefault,
                'sc_sortid' => $i,
                'sc_name' => $scName,
                'sc_color' => StringUtil::deserialize($record->sc_color)
                    ? $record->sc_color
                    : serialize($record->sc_color),
                'sc_columnset' => $record->sc_columnset
            ];

            if ($published = $record->published) {
                $update['published'] = $published;
            }

            $this->connection->update('tl_content', $update, ['id' => $childId]);
        }

        return true;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateReduceColset($record, array $colSettings, array $children): bool
    {
        $diff = count($children) - count($colSettings);

        $toDelete = array_slice($children, count($colSettings) - 1, $diff);

        $this->connection->createQueryBuilder()
            ->delete('tl_content')
            ->where('id IN (:ids)')
            ->setParameter(':ids', $toDelete, Connection::PARAM_INT_ARRAY)
            ->execute()
        ;

        $remainingChildren = array_values(array_diff($children, $toDelete));

        $this->connection->update('tl_content', [
            'sc_childs' => serialize($remainingChildren),
        ], ['id' => $record->id]);

        return $this->updateEqualColset($record, $remainingChildren);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateExpandColset($record, array $colSettings, array $children): bool
    {
        $diff = count($colSettings) - count($children);

        $colsetEnd = ContentModel::findByPk($children[count($children) - 1]);

        $this->moveRows($record->pid, $record->ptable, $colsetEnd->sorting - 1, 64 * $diff);

        $insert = [
            'type' => 'colsetPart',
            'pid' => $record->pid,
            'ptable' => $record->ptable,
            'tstamp' => time(),
            'sorting' => 0,
            'sc_name' => '',
            'sc_type' => 'deprecated',
            'sc_parent' => $record->id,
            'sc_sortid' => 0,
            'sc_gap' => $record->sc_gap,
            'sc_gapdefault' => $record->sc_gapdefault,
            'sc_color' => $record->sc_color,
            'sc_columnset' => $record->sc_columnset
        ];

        $insertedChildren = [];

        for ($i = 0; $i < $diff; $i++)
        {
            $insert['sorting'] = $colsetEnd->sorting + $i * 64;
            $this->connection->insert('tl_content', $insert);
            $insertedChildren[] = $this->connection->lastInsertId();
        }

        array_splice($children, count($children) - 1, 0, $insertedChildren);

        $this->connection->update('tl_content', [
            'sc_childs' => serialize($children),
        ], ['id' => $record->id]);

        return $this->updateEqualColset($record, $children);
    }

    /**
     * @param ContentModel|Result $record
     * @param string $colset
     * @param array $colSettings
     * @param array|null $children
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    private function createOrUpdateColset($record, string $colset, array $colSettings, array $children = null): bool
    {
        /* Neues Spaltenset anlegen */
        if (empty($children))
        {
            return $this->setupColset($record, $colset, $colSettings);
        }

        $newColCount = count($colSettings);
        $oldColCount = count($children);

        /* Gleiche Spaltenzahl */
        if ($newColCount === $oldColCount)
        {
            return $this->updateEqualColset($record, $children);
        }

        /* Weniger Spalten */
        if ($newColCount < $oldColCount)
        {
            return $this->updateReduceColset($record, $colSettings, $children);
        }

        /* Mehr Spalten */
        if ($newColCount > $oldColCount) {
            return $this->updateExpandColset($record, $colSettings, $children);
        }

        return false;
    }

    /**
     * @param ContentModel|Result $record
     * @return true
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function setupColset($record, string $colset, array $colSettings): bool
    {
        $children = [];
        $columnCount = count($colSettings);

        $this->moveRows($record->pid, $record->ptable, $record->sorting, 128 * ($columnCount + 1));

        $insert = [
            'pid' => $record->pid,
            'ptable' => $record->ptable,
            'tstamp' => time(),
            'sorting' => 0,
            'type' => 'colsetPart',
            'sc_name' => '',
            'sc_type' => 'deprecated',
            'sc_parent' => $record->id,
            'sc_sortid' => 0,
            'sc_gap' => $record->sc_gap,
            'sc_gapdefault' => $record->sc_gapdefault,
            'sc_color' => serialize($record->sc_color ?? []),
            'sc_columnset' => $record->sc_columnset
        ];

        try {
            if ($this->kernel->getBundle('GlobalContentelements'))
            {
                $insert['do'] = Input::get('do');
            }
        } catch (InvalidArgumentException $e) {
            // do nothing
        }

        for ($i = 1; $i < $columnCount; $i++)
        {
            $insert['sorting'] = $record->sorting + ($i + 1) * 64;
            $insert['sc_name'] = "{$record->sc_name}-Part-$i";
            $insert['sc_sortid'] = $i;

            if ($this->connection->insert("tl_content", $insert) > 0)
            {
                $children[] = $this->connection->lastInsertId();
            }
        }

        $insert['sorting'] = $record->sorting + ($i + 1) * 64;
        $insert['type'] = 'colsetEnd';
        $insert['sc_name'] = "{$record->sc_name}-End";
        $insert['sc_sortid'] = $columnCount;

        if ($this->connection->insert("tl_content", $insert) > 0) {
            $children[] = $this->connection->lastInsertId();
        }

        $updatedRows = $this->connection->update('tl_content', [
            'sc_childs' => serialize($children),
            'sc_parent' => $record->id
        ], ['id' => $record->id]);

        return $updatedRows > 0;
    }

    /**
     * @throws Exception
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    public function onDelete(DataContainer $dc)
    {
        $delRecord = $this->connection->fetchAssociative(
            "SELECT * FROM tl_content WHERE id=? LIMIT 1",
            [$dc->id]
        );

        if (!in_array($delRecord['type'], ['colsetStart', 'colsetPart', 'colsetEnd']))
        {
            return;
        }

        $this->connection->createQueryBuilder()
            ->delete('tl_content')
            ->where('sc_parent != "" AND sc_parent IS NOT NULL AND (id = :id OR sc_parent = :parent_id)')
            ->setParameter(':id', $delRecord['id'])
            ->setParameter(':parent_id', $delRecord['sc_parent'])
            ->execute()
        ;
    }
}