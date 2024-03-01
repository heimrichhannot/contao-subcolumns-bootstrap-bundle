<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Exception;

abstract class AbstractColsetParentContainer
{
    const DB_COL_SC_PARENT = 'sc_parent';
    const DB_COL_SC_CHILDREN = 'sc_children';
    const DB_COL_SC_NAME = 'sc_name';
    const DB_COL_SORTING = 'sc_sorting';

    public function __construct(
        private Connection $connection
    ) {}

    public abstract function getColsetContainer(): AbstractColsetContainer;

    /**
     * @throws Exception
     */
    public function onCopyCallback(int|string $id = null, DataContainer $dc = null): void
    {
        if ($id === null) {
            return;
        }

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
    protected function copyColset(int|string $pid): void
    {
        $colsetContainer = $this->getColsetContainer();

        $stmt = $this->connection->prepare("SELECT id, :children, :parent FROM :table WHERE pid=? AND type=? ORDER BY sorting");
        $stmt->bindValue('children', static::DB_COL_SC_CHILDREN);
        $stmt->bindValue('parent', static::DB_COL_SC_PARENT);
        $stmt->bindValue('table', $colsetContainer->getTable());
        $result = $stmt->execute([$pid, $colsetContainer::COLSET_TYPE_START]);

        if ($result->columnCount() < 1) {
            return;
        }

        while ($row = $result->fetchAssociative())
        {
            $this->copyColsetUpdateRow($row, $pid);
        }
    }

    /**
     * @throws Exception
     */
    protected function copyColsetUpdateRow($row, int|string $pid): void
    {
        $colsetContainer = $this->getColsetContainer();
        $cTable = $colsetContainer->getTable();

        $parent = $row['id'];
        $oldParent = $row['sc_parent'];
        $newSCName = "colset.{$row['id']}";
        $oldChildren = StringUtil::deserialize($row['sc_childs']);

        if (!is_array($oldChildren)) {
            return;
        }

        $stmt = $this->connection->prepare("UPDATE :table SET :parent = :scParent WHERE pid = ? AND :parent = ?");
        $stmt->bindValue('parent', static::DB_COL_SC_PARENT);
        $stmt->bindValue('table', $cTable);
        $stmt->bindValue('scParent', $parent);
        $stmt->execute([$pid, $oldParent]);

        $stmt = $this->connection->prepare("SELECT id, type FROM :table WHERE pid = ? AND :parent = ? AND id != ? ORDER BY :sorting");
        $stmt->bindValue('parent', static::DB_COL_SC_PARENT);
        $stmt->bindValue('table', $cTable);
        $stmt->bindValue('sorting', static::DB_COL_SORTING);
        $children = $stmt->execute([$pid, $parent, $parent]);

        if ($children->columnCount() < 1) {
            return;
        }

        $childIds = [];
        $childTypes = [];
        while ($child = $children->fetchAssociative())
        {
            $childId = $child['id'];
            $childIds[] = $childId;
            $childTypes[$childId] = $child['type'];
        }
        sort($childIds);

        $stmt = $this->connection->prepare("UPDATE :table SET :name = :scName, :children = :scChilds WHERE id=?");
        $stmt->bindValue('table', $cTable);
        $stmt->bindValue('name', static::DB_COL_SC_NAME);
        $stmt->bindValue('children', static::DB_COL_SC_CHILDREN);
        $stmt->bindValue('scName', $newSCName);
        $stmt->bindValue('scChilds', serialize($childIds));
        $stmt->execute($parent);

        $colsetTypeNames = $colsetContainer::getColsetTypeNames();

        $partNum = 1;
        foreach ($childTypes as $id => $type) {
            $typeName = $colsetTypeNames[$type];
            $partName = $type === $colsetContainer::COLSET_TYPE_PART ? "-" . $partNum++ : '';
            $newChildSCName = "$newSCName-$typeName$partName";
            $stmt = $this->connection->prepare("UPDATE :table SET :name = :scName WHERE id=?");
            $stmt->bindValue('table', $cTable);
            $stmt->bindValue('name', static::DB_COL_SC_NAME);
            $stmt->bindValue('scName', $newChildSCName);
            $stmt->execute($id);
        }
    }
}