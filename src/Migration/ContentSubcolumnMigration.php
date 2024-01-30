<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\System;
use Doctrine\DBAL\Connection;
use Throwable;

class ContentSubcolumnMigration implements MigrationInterface
{
    private Connection $connection;
    private string $projectDir;

    public function __construct(Connection $connection, string $projectDir)
    {
        $this->connection = $connection;
        $this->projectDir = $projectDir;
    }

    public function getName(): string
    {
        return 'Migrate to new column set identifier column.';
    }

    public function shouldRun(): bool
    {
        try
        {
            $fileName = $this->projectDir . '/vendor/heimrichhannot/contao-subcolumns-bootstrap-bundle/.migrated';

            if (file_exists($fileName)) {
                return false;
            }

            $qb = $this->connection->createQueryBuilder()
                ->select('count(id) AS count')
                ->from('tl_content')
                ->where('type IN (:types)')
                ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                ->andWhere('sc_parent > "0" AND sc_parent != "" AND sc_parent IS NOT NULL')
                ->andWhere('sc_type IS NOT NULL AND sc_type != "" AND sc_type != "deprecated"')
                ->setParameter('types', ['colsetStart', 'colsetPart', 'colsetEnd'], Connection::PARAM_STR_ARRAY)
            ;

            return intval($qb->execute()->fetchOne()) > 0;
        }
        catch (Throwable $e)
        {
            return false;
        }
    }

    public function run(): MigrationResult
    {
        try
        {
            $qb = $this->connection->createQueryBuilder()
                ->select('count(id) AS count')
                ->from('tl_content')
                ->where('columnset_id != "" AND columnset_id IS NOT NULL');

            $count = (int)($qb->execute()->fetchOne() ?: 0);

            if ($count > 0)
            {
                /* ============================================================================================================ */
                /* Migrate from database sources to new identifier                                                              */
                /* ============================================================================================================ */

                $qb = $this->connection->createQueryBuilder()
                    ->select('id, type, sc_parent, columnset_id')
                    ->from('tl_content')
                    ->where('type IN (:types)')
                    ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                    ->andWhere('sc_parent > "0" AND sc_parent != "" AND sc_parent IS NOT NULL')
                    ->andWhere('sc_type IS NOT NULL AND sc_type != "" AND sc_type != "deprecated"')
                    ->orderBy('type', 'DESC')
                    ->addOrderBy('columnset_id', 'DESC')
                    ->addOrderBy('sc_parent', 'ASC')
                    ->setParameter('types', ['colsetStart', 'colsetPart', 'colsetEnd'], Connection::PARAM_STR_ARRAY);

                $iterator = $qb->execute()->iterateAssociative();

                $scParentsColumnsetMap = [];

                foreach ($iterator as $item)
                {
                    if ($item['type'] === 'colsetStart' && $item['columnset_id']) {
                        $scParentsColumnsetMap[$item['id']] = $item['columnset_id'];
                    }

                    $columnsetId = $scParentsColumnsetMap[$item['sc_parent']] ?? null;
                    if (!$columnsetId) {
                        continue;
                    }

                    $scColumnset = "db.tl_columnset.$columnsetId";
                    $this->connection->update('tl_content', ['sc_columnset' => $scColumnset], ['id' => $item['id']]);
                }
            }
            else
            {
                /* ============================================================================================================ */
                /* Migrate from old config sources to new identifier                                                            */
                /* ============================================================================================================ */

                $qb = $this->connection->createQueryBuilder()
                    ->select('id, type, sc_type')
                    ->from('tl_content')
                    ->where('type IN (:types)')
                    ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                    ->andWhere('sc_parent > "0" AND sc_parent != "" AND sc_parent IS NOT NULL')
                    ->andWhere('sc_type IS NOT NULL AND sc_type != "" AND sc_type != "deprecated"')
                    ->setParameter('types', ['colsetStart', 'colsetPart', 'colsetEnd'], Connection::PARAM_STR_ARRAY);

                $iterator = $qb->execute()->iterateAssociative();

                foreach ($iterator as $item) {
                    $scType = $item['sc_type'] ?? null;
                    if ($scType === null) {
                        continue;
                    }
                    $scColumnset = "globals.bootstrap3.$scType";
                    $this->connection->update('tl_content', ['sc_columnset' => $scColumnset], ['id' => $item['id']]);
                }
            }

            $fileName = $this->projectDir . '/vendor/heimrichhannot/contao-subcolumns-bootstrap-bundle/.migrated';
            file_put_contents($fileName, '');

            return new MigrationResult(true, 'Migrated sc_columnset in content elements successfully.');
        }
        catch (Throwable $e)
        {
            return new MigrationResult(false, 'Error migrating content elements: ' . $e->getMessage());
        }
    }
}