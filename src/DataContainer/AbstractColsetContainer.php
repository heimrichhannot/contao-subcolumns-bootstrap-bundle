<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractColsetContainer
{
    protected ?array $options = null;

    public static abstract function getTable(): string;

    public function __construct(
        private ColumnsetContainer $columnsetContainer,
        private Connection $connection,
        private Database $database,
        private KernelInterface $kernel
    ) {}

    public function createPalette(DataContainer $dc): void
    {
        PaletteManipulator::create()
            ->removeField('sc_type', 'colset_legend')
            ->removeField('columnset_id', 'colset_legend')
            ->applyToPalette('colsetStart', static::getTable());

        if (!class_exists('onemarshall\AosBundle\AosBundle')) {
            return;
        }

        $palette = $GLOBALS['TL_DCA'][static::getTable()]['palettes']['colsetStart'];

        $palette = str_replace('invisible,', '', $palette) . ';{invisible_legend:hide},invisible;'
        . '{aos_legend:hide},aosAnimation,aosEasing,aosDuration,aosDelay,aosAnchor,aosAnchorPlacement,aosOffset,aosOnce'
        . '{invisible_legend:hide}';

        $GLOBALS['TL_DCA'][static::getTable()]['palettes']['colsetStart'] = $palette;
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
    protected function getOptionsFromDatabase(): array
    {
        try {
            $columnSets = $this->connection
                ->fetchAllAssociative('SELECT id, title, columns, description FROM `tl_columnset` ORDER BY columns');
        } catch (\Exception $e) {
            return [];
        }

        $types = [];
        $dbLegend = $GLOBALS['TL_LANG']['tl_columnset']['db_legend'] ?? '[DB]';

        foreach ($columnSets as $columnSet)
        {
            $types[$dbLegend]['db.tl_columnset.'.$columnSet['id']] =
                "[{$columnSet['columns']}] "
                . $columnSet['title']
                . (!empty($columnSet['description']) ? " ({$columnSet['description']})" : '');
        }

        return $types;
    }

    protected function getOptionsFromGlobals(): array
    {
        $types = [];
        $dbLegend = $GLOBALS['TL_LANG'][static::getTable()]['globals_legend'] ?? '[GLOBALS]';
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
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    protected function moveRows($pid, $ptable, $sorting, int $amount = 128): void
    {
        $stmt = $this->connection
            ->prepare("UPDATE `:table` SET sorting = sorting + ? WHERE pid=? AND ptable=? AND sorting > ?");
        $stmt->bindValue('table', static::getTable());
        $stmt->executeStatement([$amount, $pid, $ptable, $sorting]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public function onUpdate(DataContainer $dc): bool
    {
        /** @var Result $record */
        $record = $dc->activeRecord;
        $colsetIdentifier = $record->sc_columnset ?? null;
        $colset = $this->columnsetContainer->getColumnSettings($colsetIdentifier);

        if (empty($colset))
        {
            return false;
        }

        $children = StringUtil::deserialize($record->sc_childs ?? null) ?: null;

        return $this->createOrUpdateColset($record, $colsetIdentifier, $colset, $children);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
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

            $this->connection->update(static::getTable(), $update, ['id' => $childId]);
        }

        return true;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private function updateReduceColset($record, array $colSettings, array $children): bool
    {
        $diff = count($children) - count($colSettings);

        $toDelete = array_slice($children, count($colSettings) - 1, $diff);

        if (empty($toDelete)) {
            return false;
        }

        $type = class_exists(ArrayParameterType::class) ? ArrayParameterType::INTEGER : null;
        $type ??= defined(Connection::PARAM_INT_ARRAY) ? Connection::PARAM_INT_ARRAY : 101;

        $this->connection->createQueryBuilder()
            ->delete(static::getTable())
            ->where('id IN (:ids)')
            ->setParameter('ids', $toDelete, $type)
            // ->setParameter(':ids', $toDelete, $type)
            ->executeStatement()
        ;

        $remainingChildren = array_values(array_diff($children, $toDelete));

        $this->connection->update(static::getTable(), [
            'sc_childs' => serialize($remainingChildren),
        ], ['id' => $record->id]);

        return $this->updateEqualColset($record, $remainingChildren);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateExpandColset(ContentModel|Result $record, array $colSettings, array $children): bool
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
            $this->connection->insert(static::getTable(), $insert);
            $insertedChildren[] = $this->connection->lastInsertId();
        }

        array_splice($children, count($children) - 1, 0, $insertedChildren);

        $this->connection->update(static::getTable(), [
            'sc_childs' => serialize($children),
        ], ['id' => $record->id]);

        return $this->updateEqualColset($record, $children);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private function createOrUpdateColset(ContentModel|Result $record, string $colset, array $colSettings, ?array $children = null): bool
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
     * @return true
     * @throws \Doctrine\DBAL\Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private function setupColset(ContentModel|Result $record, string $colset, array $colSettings): bool
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

            if ($this->connection->insert(static::getTable(), $insert) > 0)
            {
                $children[] = $this->connection->lastInsertId();
            }
        }

        $insert['sorting'] = $record->sorting + ($i + 1) * 64;
        $insert['type'] = 'colsetEnd';
        $insert['sc_name'] = "{$record->sc_name}-End";
        $insert['sc_sortid'] = $columnCount;

        if ($this->connection->insert(static::getTable(), $insert) > 0) {
            $children[] = $this->connection->lastInsertId();
        }

        $updatedRows = $this->connection->update(static::getTable(), [
            'sc_childs' => serialize($children),
            'sc_parent' => $record->id
        ], ['id' => $record->id]);

        return $updatedRows > 0;
    }

    /**
     * @throws Exception
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    public function onDelete(DataContainer $dc): void
    {
        $delRecord = $this->connection->fetchAssociative(
            "SELECT * FROM `" . static::getTable() . "` WHERE id=? LIMIT 1",
            [$dc->id]
        );

        if (!in_array($delRecord['type'], ['colsetStart', 'colsetPart', 'colsetEnd']))
        {
            return;
        }

        $this->connection->createQueryBuilder()
            ->delete(static::getTable())
            ->where('sc_parent != "" AND sc_parent IS NOT NULL AND (id = :id OR sc_parent = :parent_id)')
            ->setParameter(':id', $delRecord['id'])
            ->setParameter(':parent_id', $delRecord['sc_parent'])
            ->executeStatement()
        ;
    }

    /**
     * @throws Exception
     */
    public function onCopy(int|string $id, DataContainer $dc): void
    {
        $dc->activeRecord = $this->connection
            ->fetchAssociative("SELECT * FROM `" . static::getTable() . "` WHERE id=? LIMIT 1", [$id]);

        if (!in_array($dc->activeRecord['type'], ['colsetStart', 'colsetPart', 'colsetEnd']))
        {
            return;
        }

        $act = Input::get('act');

        if ($act === 'copy')
        {
            if ($dc->activeRecord['type'] === 'colsetStart')
            {
                $this->connection->update(static::getTable(), [
                    'sc_parent' => 0,
                    'sc_childs' => ''
                ], ['id' => $id]);
            }
            return;
        }

        if ($act !== 'copyAll') {
            return;
        }

        switch ($dc->activeRecord['type'])
        {
            case 'colsetStart':
                $this->copyColsetStart($dc->activeRecord, $dc->id);
                break;
            case 'colsetPart':
                $this->copyColsetPart($dc->activeRecord, $dc->id);
                break;
            case 'colsetEnd':
                $this->copyColsetEnd($dc->activeRecord, $dc->id);
                break;
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function copyColsetStart(array $record, int|string $pid): void
    {
        $session = [
            'parentId' => $pid,
            'count'    => 1,
            'childs'   => []
        ];

        $GLOBALS['scglobal']['sc'.$record['sc_parent']] = $session;

        $this->connection->update(static::getTable(), [
            'sc_name'   => 'colset.' . $pid,
            'sc_parent' => $pid
        ], ['id' => $pid]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function copyColsetPart(mixed $activeRecord, int|string $id): void
    {
        $session = &$GLOBALS['scglobal']['sc'.$activeRecord['sc_parent']];

        $newParent = $session['parentId'];
        $count = $session['count'];

        $this->connection->update(static::getTable(), [
            'sc_name'   => 'colset.' . $newParent . '-Part-' . $count,
            'sc_parent' => $newParent
        ], ['id' => $id]);

        $session['childs'][] = $id;
        $session['count']++;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function copyColsetEnd(mixed $activeRecord, int|string $id): void
    {
        $session = &$GLOBALS['scglobal']['sc'.$activeRecord['sc_parent']];

        $newParent = $session['parentId'];

        $this->connection->update(static::getTable(), [
            'sc_name'   => 'colset.' . $newParent . '-End',
            'sc_parent' => $newParent
        ], ['id' => $id]);

        $session['childs'][] = $id;
    }

    /**
     * HOOK: $GLOBALS['TL_HOOKS']['clipboardCopy']
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function clipboardCopy(int|string $id, DataContainer $dc, bool $isGrouped): void
    {
        if ($isGrouped) {
            return;
        }

        $activeRecord = $this->connection->fetchAssociative("SELECT * FROM `" . static::getTable() . "` WHERE id=?", [$id]);

        if ($activeRecord['type'] !== 'colsetStart') {
            return;
        }

        $this->connection->update(static::getTable(), [
            'sc_childs' => '',
            'sc_parent' => '',
            'sc_name' => 'colset.' . $activeRecord['id']
        ], ['id' => $id]);

        $stmt = $this->database->prepare("SELECT * FROM `" . static::getTable() . "` WHERE id=?");
        $result = $stmt->execute([$id]);
        $record = $result->next();
        if (!$record) {
            return;
        }

        $scType = $record->sc_type;

        $colsetIdentifier = $record->sc_columnset ?? null;
        $colset = $this->columnsetContainer->getColumnSettings($colsetIdentifier);

        if (empty($colset)) {
            return;
        }

        $logger = System::getContainer()->get('logger');
        $logger->info("Values: sc_type=$scType, sc-colset-count=".count($colset).' :: SpaltensetHilfe clipboardCopy()');

        $this->setupColset($record, $scType, $colset);
    }

    /**
     * HOOK: $GLOBALS['TL_HOOKS']['clipboardCopyAll']
     */
    public function clipboardCopyAll(array $arrIds): void
    {
        // $arrIds = array_keys(array_flip($arrIds));
        $arrIds = array_unique(array_values($arrIds));

        $in = implode(',', $arrIds);
        $result = $this->database->execute("SELECT DISTINCT pid FROM `" . static::getTable() . "` WHERE id IN ($in)");

        if ($result->numRows > 0)
        {
            while ($result->next())
            {
                $this->copyCheck($result->pid);
            }
        }
    }

    /**
     * Copy a colset
     */
    public function copyCheck(int|string $pid): void
    {
        $row = $this->database
            ->prepare("SELECT id, sc_childs, sc_parent FROM " . static::getTable() . " WHERE pid=? AND type=? ORDER BY sorting")
            ->execute($pid, 'colsetStart');

        if ($row->numRows < 1) {
            return;
        }

        $typeToNameMap = [
            "colsetStart" => "Start",
            "colsetPart" => "Part",
            "colsetEnd" => "End"
        ];

        while ($row->next())
        {
            $parent = $row->id;
            $oldParent = $row->sc_parent;
            $newSCName = "colset.$row->id";
            $oldChildren = unserialize($row->sc_childs);

            if (!is_array($oldChildren)) {
                continue;
            }

            $this->database
                ->prepare("UPDATE `" . static::getTable() . "` SET %s WHERE pid=? AND sc_parent=?")
                ->set(['sc_parent' => $parent])
                ->execute($pid, $oldParent);

            $child = $this->database
                ->prepare("SELECT id, type FROM `" . static::getTable() . "` WHERE pid=? AND sc_parent=? AND id!=? ORDER BY sorting")
                ->execute($pid, $parent, $parent);

            if ($child->numRows < 1) {
                continue;
            }

            $childIds = [];
            while ($child->next()) {
                $childIds[] = $child->id;
                $childTypes[$child->id] = $child->type;
            }
            sort($childIds);

            $this->database
                ->prepare("UPDATE `" . static::getTable() . "` %s WHERE id=?")
                ->set(['sc_name' => $newSCName, 'sc_childs' => $childIds])
                ->execute($parent);

            $partNum = 1;
            foreach ($childTypes as $id => $type) {
                $newChildSCName = $newSCName . "-$typeToNameMap[$type]" . ($type === "colsetPart" ? "-" . $partNum++ : '');
                $this->database
                    ->prepare("UPDATE `" . static::getTable() . "` %s WHERE id=?")
                    ->set(['sc_name' => $newChildSCName])
                    ->execute($id);
            }
        }
    }

    public function toggleAdditionalElements($varValue, DataContainer $dc)
    {
        if ($dc->id)
        {
            $stmt = $this->connection
                ->prepare("UPDATE :table SET tstamp=:tstamp, invisible=:inv WHERE sc_parent=? AND type!=?");
            $stmt->bindValue('table', static::getTable());
            $stmt->bindValue('tstamp', time());
            $stmt->bindValue('inv', $varValue ? 1 : '');
            $stmt->executeStatement([$dc->id, 'colsetStart']);
        }
        return $varValue;
    }

    /**
     * add column set field to the colsetStart content element. We need to do it dynamically because subcolumns
     * creates its palette dynamically
     *
     * @param DataContainer $dc
     */
    public function appendColumnsetIdToPalette(DataContainer $dc): void
    {
        $dca = &$GLOBALS['TL_DCA'][static::getTable()];

        $dca['palettes']['colsetStart'] = str_replace('{colset_legend}', '{colset_legend},sc_columnset', $dca['palettes']['colsetStart']);

        if (!SubColumnsBootstrapBundle::validProfile($GLOBALS['TL_CONFIG']['subcolumns'])) return;

        $content = ContentModel::findByPK($dc->id);

        $dca['palettes']['colsetStart'] = str_replace('sc_name', '', $dca['palettes']['colsetStart']);
        $dca['palettes']['colsetStart'] = str_replace('sc_type', 'sc_type,sc_name', $dca['palettes']['colsetStart']);

        if ($content && isset($content->sc_type) && $content->sc_type > 0) {
            $dca['palettes']['colsetStart'] = str_replace('sc_type', 'sc_type,columnset_id,addContainer', $dca['palettes']['colsetStart']);
            $dca['palettes']['colsetStart'] = str_replace('sc_color', '', $dca['palettes']['colsetStart']);
        }
    }

    /**
     * Write the other Sets
     * @throws Exception
     * */
    public function setElementProperties(DataContainer $dc): void
    {
        if ($dc->activeRecord->type !== 'colsetStart'
            || $dc->activeRecord->sc_type === "")
        {
            return;
        }

        $endPart = $this->connection->fetchAssociative(
            "SELECT sorting FROM `" . static::getTable() . "` WHERE sc_name=?",
            [$dc->activeRecord->sc_name . '-End']
        );

        $stmt = $this->connection
            ->prepare("UPDATE :table SET protected=:protected, groups=:groups, guests=:guests WHERE pid=? AND sorting > ? AND sorting <= ?");
        $stmt->bindValue('table', static::getTable());
        $stmt->bindValue('protected', $dc->activeRecord->protected);
        $stmt->bindValue('groups', $dc->activeRecord->groups);
        $stmt->bindValue('guests', $dc->activeRecord->guests);
        $stmt->executeStatement([
            $dc->activeRecord->pid,
            $dc->activeRecord->sorting,
            $endPart['sorting']
        ]);
    }
}