<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class FormContainer extends AbstractColsetParentContainer
{
    const DB_COL_SC_PARENT = 'fsc_parent';
    const DB_COL_SC_CHILDREN = 'fsc_childs';
    const DB_COL_SC_NAME = 'fsc_name';
    const DB_COL_SC_TYPE = 'fsc_type';
    const DB_COL_SORTING = 'fsc_sortid';

    public function __construct(
        private Connection $connection,
        private FormFieldContainer $formFieldContainer
    ) {
        parent::__construct($connection);
    }

    public function getColsetContainer(): AbstractColsetContainer
    {
        return $this->formFieldContainer;
    }

    /**
     * @deprecated This is kept here only for debugging reasons
     * @internal
     * @throws Exception
     */
    private function _onCopyCallback(int|string $intId, DataContainer $dc): void
    {
        if (is_string($intId)) {
            $intId = intval($intId);
        }

        if ($intId < 1) {
            return;
        }

        $result = $this->connection
            ->prepare("SELECT id, fsc_parent FROM tl_form_field WHERE pid=? AND type=?")
            ->execute([$intId, 'formcolstart']);

        if ($result->columnCount() < 1) {
            return;
        }

        while ($row = $result->fetchAssociative())
        {
            $strName = 'colset.' . $row['id'];

            $stmt = $this->connection
                ->prepare("UPDATE tl_form_field SET fsc_parent=:fscParent, fsc_name=:fscName WHERE pid=? AND fsc_parent=?");
            $stmt->bindValue('fscParent', $row['id']);
            $stmt->bindValue('fscName', $strName);
            $stmt->execute([$intId, $row['fsc_parent']]);

            $parts = $this->connection
                ->prepare("SELECT * FROM tl_form_field WHERE fsc_parent=? AND type!=? ORDER BY fsc_sortid")
                ->execute([$result['id'], 'formcolstart']);

            $children = [];

            while ($part = $parts->fetchAssociative())
            {
                $strName = $part['type'] == 'formcolend' ? "colset.{$row['id']}-End" : "colset.{$row['id']}-Part-{$part['fsc_sortid']}";
                $stmt = $this->connection->prepare("UPDATE tl_form_field SET fsc_name=:fscName WHERE id=?");
                $stmt->bindValue('fscName', $strName);
                $stmt->execute([$part['id']]);

                $children[] = $part['id'];
            }

            $stmt = $this->connection->prepare("UPDATE tl_form_field SET fsc_childs=:fscChilds WHERE id=?");
            $stmt->bindValue('fscChilds', serialize($children));
            $stmt->execute([$row['id']]);
        }
    }
}