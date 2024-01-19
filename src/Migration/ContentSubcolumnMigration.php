<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Throwable;

class ContentSubcolumnMigration implements MigrationInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Migrate to new column set identifier column.';
    }

    public function shouldRun(): bool
    {
        try
        {
            $qb = $this->connection->createQueryBuilder()
                ->select('count(id) AS count')
                ->from('tl_content')
                ->where('type IN (:types)')
                ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                ->andWhere('sc_type != "" AND sc_type IS NOT NULL')
                ->andWhere('sc_parent > "0" AND sc_parent != "" AND sc_parent IS NOT NULL')
                ->setParameter('types', ['colsetStart', 'colsetPart', 'colsetEnd'], Connection::PARAM_STR_ARRAY)
            ;
            return (bool)$qb->execute()->fetchOne();
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
                ->select('id, type, sc_type')
                ->from('tl_content')
                ->where('type IN (:types)')
                ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                ->andWhere('sc_parent > "0" AND sc_parent != "" AND sc_parent IS NOT NULL')
                ->setParameter('types', ['colsetStart', 'colsetPart', 'colsetEnd'], Connection::PARAM_STR_ARRAY);

            $iterator = $qb->execute()->iterateAssociative();

            foreach ($iterator as $item) {
                $scType = $item['sc_type'] ?? '';
                $scColumnset = empty($scType) ? 'error.sc_type_missing' : (
                    $scType === 'deprecated'
                        ? 'error.integrity_constraint_violation'
                        : "globals.bootstrap3.$scType"
                );
                $this->connection->update('tl_content', ['sc_columnset' => $scColumnset], ['id' => $item['id']]);
            }

            return new MigrationResult(true, 'Migrated sc_columnset in content elements successfully.');
        }
        catch (Throwable $e)
        {
            return new MigrationResult(false, 'Error migrating content elements: ' . $e->getMessage());
        }
    }
}