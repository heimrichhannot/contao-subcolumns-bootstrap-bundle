<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Exception\InternalServerErrorHttpException;
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
use HeimrichHannot\SubColumnsBootstrapBundle\Controller\ColsetIdentifierController;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractColsetContainer
{
    const COLSET_TYPE_START = 'colsetStart';
    const COLSET_TYPE_PART = 'colsetPart';
    const COLSET_TYPE_END = 'colsetEnd';

    public static function getColsetTypes(): array
    {
        return [
            static::COLSET_TYPE_START,
            static::COLSET_TYPE_PART,
            static::COLSET_TYPE_END
        ];
    }

    public static function getColsetTypeNames(): array
    {
        return [
            static::COLSET_TYPE_START => "Start",
            static::COLSET_TYPE_PART => "Part",
            static::COLSET_TYPE_END => "End"
        ];
    }

    public static abstract function getTable(): string;

    protected ?array $options = null;

    public function __construct(
        private ColsetIdentifierController $colIdController,
        private Connection                 $connection,
        private ContaoCsrfTokenManager     $tokenManager,
        private KernelInterface            $kernel
    ) {}

    public function createPalette(DataContainer $dc): void
    {
        PaletteManipulator::create()
            ->removeField('sc_type', 'colset_legend')
            ->removeField('columnset_id', 'colset_legend')
            ->applyToPalette(static::COLSET_TYPE_START, static::getTable());

        if (!class_exists('onemarshall\AosBundle\AosBundle')) {
            return;
        }

        $palette = $GLOBALS['TL_DCA'][static::getTable()]['palettes'][static::COLSET_TYPE_START];

        $palette = str_replace('invisible,', '', $palette) . ';{invisible_legend:hide},invisible;'
        . '{aos_legend:hide},aosAnimation,aosEasing,aosDuration,aosDelay,aosAnchor,aosAnchorPlacement,aosOffset,aosOnce'
        . '{invisible_legend:hide}';

        $GLOBALS['TL_DCA'][static::getTable()]['palettes'][static::COLSET_TYPE_START] = $palette;
    }

    public function getColsetOptions(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        return $this->options = array_merge(
            $this->getColsetOptionsFromDatabase(),
            $this->getColsetOptionsFromGlobals()
        );
    }

    /**
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    protected function getColsetOptionsFromDatabase(): array
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

    protected function getColsetOptionsFromGlobals(): array
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
     * @throws Exception
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public function onUpdate(DataContainer $dc): bool
    {
        /** @var Result $record */
        $record = $dc->activeRecord;

        $colsetIdentifier = $record->sc_columnset ?? null;
        if ($colsetIdentifier === null) {
            throw new InternalServerErrorHttpException("No columnset identifier found. Perhaps you need to update your database.");
        }

        $columnSettings = $this->colIdController->getColumnSettings($colsetIdentifier);

        $children = StringUtil::deserialize($record->sc_childs ?? null) ?: null;

        return $this->createOrUpdateColset($record, $columnSettings, $children);
    }

    /**
     * @throws Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private function updateEqualColset($record, array $children): bool
    {
        foreach (array_values($children) as $i => $childId)
        {
            $i++;
            $scName = $record->sc_name . ($i === count($children) ? '-End' : ("-Part-$i"));

            $update = [
                /*'sc_gap' => $record->sc_gap,*/
                /*'sc_gapdefault' => $record->sc_gapdefault,*/
                'sc_sortid' => $i,
                'sc_name' => $scName,
                /*'sc_color' => StringUtil::deserialize($record->sc_color)
                    ? $record->sc_color
                    : serialize($record->sc_color),*/
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
     * @throws Exception
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
     * @throws Exception
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
            // 'sc_type' => 'deprecated',
            'sc_parent' => $record->id,
            'sc_sortid' => 0,
            // 'sc_gap' => $record->sc_gap,
            // 'sc_gapdefault' => $record->sc_gapdefault,
            // 'sc_color' => $record->sc_color,
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function createOrUpdateColset(ContentModel|Result|stdClass $record, ?array $columnSettings, ?array $children = null): bool
    {
        /* Neues Spaltenset anlegen */
        if (empty($children))
        {
            return $this->setupColset($record, $columnSettings);
        }

        $newColCount = count($columnSettings);
        $oldColCount = count($children);

        /* Gleiche Spaltenzahl */
        if ($newColCount === $oldColCount)
        {
            return $this->updateEqualColset($record, $children);
        }

        /* Weniger Spalten */
        if ($newColCount < $oldColCount)
        {
            return $this->updateReduceColset($record, $columnSettings, $children);
        }

        /* Mehr Spalten */
        if ($newColCount > $oldColCount) {
            return $this->updateExpandColset($record, $columnSettings, $children);
        }

        return false;
    }

    /**
     * @return true
     * @throws Exception
     */
    private function setupColset(ContentModel|Result|stdClass $record, ?array $columnSettings): bool
    {
        $children = [];
        $columnCount = count($columnSettings);

        $this->moveRows($record->pid, $record->ptable, $record->sorting, 128 * ($columnCount + 1));

        $insert = [
            'pid' => $record->pid,
            'ptable' => $record->ptable,
            'tstamp' => time(),
            'sorting' => 0,
            'type' => 'colsetPart',
            'sc_name' => '',
            // 'sc_type' => 'deprecated',
            'sc_parent' => $record->id,
            'sc_sortid' => 0,
            // 'sc_gap' => $record->sc_gap,
            // 'sc_gapdefault' => $record->sc_gapdefault,
            // 'sc_color' => serialize($record->sc_color ?? []),
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
        $insert['type'] = static::COLSET_TYPE_END;
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

        if (!in_array($delRecord['type'], static::getColsetTypes()))
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

        if (!in_array($dc->activeRecord['type'], static::getColsetTypes()))
        {
            return;
        }

        $act = Input::get('act');

        if ($act === 'copy')
        {
            if ($dc->activeRecord['type'] === static::COLSET_TYPE_START)
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
            case static::COLSET_TYPE_START:
                $this->copyColsetStart($dc->activeRecord, $dc->id);
                break;
            case static::COLSET_TYPE_PART:
                $this->copyColsetPart($dc->activeRecord, $dc->id);
                break;
            case static::COLSET_TYPE_END:
                $this->copyColsetEnd($dc->activeRecord, $dc->id);
                break;
        }
    }

    /**
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function clipboardCopy(int|string $id, DataContainer $dc, bool $isGrouped): void
    {
        if ($isGrouped) {
            return;
        }

        $activeRecord = $this->connection->fetchAssociative("SELECT * FROM `" . static::getTable() . "` WHERE id=?", [$id]);

        if ($activeRecord['type'] !== static::COLSET_TYPE_START) {
            return;
        }

        $this->connection->update(static::getTable(), [
            'sc_childs' => '',
            'sc_parent' => '',
            'sc_name' => 'colset.' . $activeRecord['id']
        ], ['id' => $id]);

        $database = Database::getInstance();

        $stmt = $database->prepare("SELECT * FROM `" . static::getTable() . "` WHERE id=?");
        $result = $stmt->execute([$id]);
        $record = $result->next();
        if (!$record) {
            return;
        }

        $scType = $record->sc_type;

        $colsetIdentifier = $record->sc_columnset ?? null;
        $colset = $this->colIdController->getColumnSettings($colsetIdentifier);

        if (empty($colset)) {
            return;
        }

        $logger = System::getContainer()->get('logger');
        $logger->info("Values: sc_type=$scType, sc-colset-count=".count($colset).' :: SpaltensetHilfe clipboardCopy()');

        $this->setupColset($record, $colset);
    }

    /**
     * HOOK: $GLOBALS['TL_HOOKS']['clipboardCopyAll']
     */
    public function clipboardCopyAll(array $arrIds): void
    {
        // $arrIds = array_keys(array_flip($arrIds));
        $arrIds = array_unique(array_values($arrIds));

        $in = implode(',', $arrIds);
        $result = Database::getInstance()
            ->execute("SELECT DISTINCT pid FROM `" . static::getTable() . "` WHERE id IN ($in)");

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
        $row = Database::getInstance()
            ->prepare("SELECT id, sc_childs, sc_parent FROM " . static::getTable() . " WHERE pid=? AND type=? ORDER BY sorting")
            ->execute($pid, static::COLSET_TYPE_START);

        if ($row->numRows < 1) {
            return;
        }

        $typeToNameMap = static::getColsetTypeNames();

        while ($row->next())
        {
            $parent = $row->id;
            $oldParent = $row->sc_parent;
            $newSCName = "colset.$row->id";
            $oldChildren = unserialize($row->sc_childs);

            if (!is_array($oldChildren)) {
                continue;
            }

            $database = Database::getInstance();

            $database->prepare("UPDATE `" . static::getTable() . "` SET %s WHERE pid=? AND sc_parent=?")
                ->set(['sc_parent' => $parent])
                ->execute($pid, $oldParent);

            $child = $database
                ->prepare("SELECT id, type FROM `" . static::getTable() . "` WHERE pid=? AND sc_parent=? AND id!=? ORDER BY sorting")
                ->execute($pid, $parent, $parent);

            if ($child->numRows < 1) {
                continue;
            }

            $childIds = [];
            $childTypes = [];
            while ($child->next()) {
                $childIds[] = $child->id;
                $childTypes[$child->id] = $child->type;
            }
            sort($childIds);

            $database->prepare("UPDATE `" . static::getTable() . "` %s WHERE id=?")
                ->set(['sc_name' => $newSCName, 'sc_childs' => $childIds])
                ->execute($parent);

            $partNum = 1;
            foreach ($childTypes as $id => $type) {
                $newChildSCName = $newSCName . "-$typeToNameMap[$type]" . ($type === "colsetPart" ? "-" . $partNum++ : '');
                $database->prepare("UPDATE `" . static::getTable() . "` %s WHERE id=?")
                    ->set(['sc_name' => $newChildSCName])
                    ->execute($id);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function toggleAdditionalElements($varValue, DataContainer $dc)
    {
        if ($dc->id)
        {
            $stmt = $this->connection
                ->prepare("UPDATE :table SET tstamp=:tstamp, invisible=:inv WHERE sc_parent=? AND type!=?");
            $stmt->bindValue('table', static::getTable());
            $stmt->bindValue('tstamp', time());
            $stmt->bindValue('inv', $varValue ? 1 : '');
            $stmt->executeStatement([$dc->id, static::COLSET_TYPE_START]);
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

        $dca['palettes'][static::COLSET_TYPE_START] = str_replace('{colset_legend}', '{colset_legend},sc_columnset', $dca['palettes'][static::COLSET_TYPE_START]);
    }

    /**
     * Write the other Sets
     * @throws Exception
     * */
    public function setElementProperties(DataContainer $dc): void
    {
        if ($dc->activeRecord->type !== static::COLSET_TYPE_START
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
     * get all column-sets
     */
    public function getColumnsetIdOptions(DataContainer $dc): array
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

    public function getColumnsetIdWizard(DataContainer $dc): string
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

    public function getAllSubcolumnTypeOptions(DataContainer $dc): array
    {
        if (!SubColumnsBootstrapBundle::validProfile($GLOBALS['TL_CONFIG']['subcolumns'])) {
            $strSet = SubColumnsBootstrapBundle::getProfile();
            return array_keys($GLOBALS['TL_SUBCL'][$strSet]['sets']);
        }

        $collection = Database::getInstance()
            ->execute('SELECT columns FROM tl_columnset GROUP BY columns ORDER BY columns');

        $types = [];

        while ($collection->next()) {
            $types[] = $collection->numCols;
        }

        /*
        while ($collection->next()) {
            $types['Aus Datenbank'][] = $collection->columns;
        }

        foreach ($GLOBALS['TL_SUBCL'] as $subType => $config) {
            foreach ($config['sets'] as $set => $columns) {
                $types[$config['label']][$subType . '.' . $set] = $set;
            }
        }

        ksort($types);
        */

        return $types;
    }

    /**
     * @throws Exception
     */
    public function onCopyCallback(int|string $id = 0): void
    {
        if (is_string($id)) {
            $id = intval($id);
        }

        if ($id < 1) {
            return;
        }

        $this->copyColset($id);
    }

    /**
     * Copy a colset
     *
     * @throws Exception
     */
    public function copyColset(int|string $pid): void
    {
        $result = $this->connection
            ->prepare("SELECT id, sc_childs, sc_parent FROM tl_content WHERE pid=? AND type=? ORDER BY sorting")
            ->executeQuery([$pid, 'colsetStart']);

        if ($result->columnCount() < 1) {
            return;
        }

        while ($row = $result->fetchAssociative())
        {
            $parent = $row['id'];
            $oldParent = $row['sc_parent'];
            $newSCName = "colset.{$row['id']}";
            $oldChildren = StringUtil::deserialize($row['sc_childs']);

            if (!is_array($oldChildren)) {
                continue;
            }

            $stmt = $this->connection->prepare("UPDATE tl_content SET sc_parent=:scParent WHERE pid=? AND sc_parent=?");
            $stmt->bindValue('scParent', $parent);
            $stmt->executeStatement([$pid, $oldParent]);

            $stmt = $this->connection->prepare("SELECT id, type FROM tl_content WHERE pid=? AND sc_parent=? AND id!=? ORDER BY sorting");
            $children = $stmt->executeQuery([$pid, $parent, $parent]);

            if ($children->columnCount() < 1) {
                continue;
            }

            $childIds = [];
            while ($child = $children->fetchAssociative())
            {
                $childId = $child['id'];
                $childIds[] = $childId;
                $childTypes[$childId] = $child['type'];
            }
            sort($childIds);

            $stmt = $this->connection->prepare("UPDATE tl_content SET sc_name=:scName, sc_childs=:scChilds WHERE id=?");
            $stmt->bindValue('scName', $newSCName);
            $stmt->bindValue('scChilds', serialize($childIds));
            $stmt->executeStatement([$parent]);

            $partNum = 1;
            foreach ($childTypes as $id => $type) {
                $typeName = ContentContainer::getColsetTypeNames()[$type];
                $partName = $type === "colsetPart" ? "-" . $partNum++ : '';
                $newChildSCName = "$newSCName-$typeName$partName";
                $stmt = $this->connection->prepare("UPDATE tl_content SET sc_name=:scName WHERE id=?");
                $stmt->bindValue('scName', $newChildSCName);
                $stmt->executeStatement([$id]);
            }
        }
    }
}