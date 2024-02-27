<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Doctrine\DBAL\Driver\Connection;
use Contao\StringUtil;
use Doctrine\DBAL\Driver\Exception;

class ArticleContainer
{
    const TYPE_COLSET_START = 'colsetStart';
    const TYPE_COLSET_PART = 'colsetPart';
    const TYPE_COLSET_END = 'colsetEnd';
    const TYPE_NAMES = [
        self::TYPE_COLSET_START => "Start",
        self::TYPE_COLSET_PART => "Part",
        self::TYPE_COLSET_END => "End"
    ];

    public function __construct(
        private Connection $connection
    ) {}

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
            ->execute([$pid, 'colsetStart']);

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
            $stmt->execute([$pid, $oldParent]);

            $stmt = $this->connection->prepare("SELECT id, type FROM tl_content WHERE pid=? AND sc_parent=? AND id!=? ORDER BY sorting");
            $children = $stmt->execute([$pid, $parent, $parent]);

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
            $stmt->execute($parent);

            $partNum = 1;
            foreach ($childTypes as $id => $type) {
                $typeName = static::TYPE_NAMES[$type];
                $partName = $type === "colsetPart" ? "-" . $partNum++ : '';
                $newChildSCName = "$newSCName-$typeName$partName";
                $stmt = $this->connection->prepare("UPDATE tl_content SET sc_name=:scName WHERE id=?");
                $stmt->bindValue('scName', $newChildSCName);
                $stmt->execute($id);
            }
        }

    }
}