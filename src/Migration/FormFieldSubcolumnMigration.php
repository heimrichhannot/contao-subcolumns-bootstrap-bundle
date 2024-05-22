<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Migration;

use Contao\Config;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Throwable;

class FormFieldSubcolumnMigration implements MigrationInterface
{
    private const TYPES = ['formcolstart', 'formcolpart', 'formcolend'];
    private const VERSION = 1;

    private Connection $connection;
    private string $projectDir;

    public function __construct(Connection $connection, string $projectDir)
    {
        $this->connection = $connection;
        $this->projectDir = $projectDir;
    }

    public function getName(): string
    {
        return 'Subcolumns Bootstrap form field columnset name migration';
    }

    public function shouldRun(): bool
    {
        if (static::VERSION === Config::get('sc_bs_formfield_type_migration')) {
            return false;
        }

        try
        {
            $qb = $this->connection->createQueryBuilder()
                ->select('count(id) AS count')
                ->from('tl_form_field')
                ->where('type IN (:types)')
                ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                ->andWhere('fsc_parent > "0" AND fsc_parent != "" AND fsc_parent IS NOT NULL')
                ->andWhere('fsc_type IS NOT NULL AND fsc_type != "" AND fsc_type != "deprecated"')
                ->setParameter('types', static::TYPES, Connection::PARAM_STR_ARRAY)
            ;

            return intval($qb->executeQuery()->fetchOne()) > 0;
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

            /* ============================================================================================================ */
            /* Migrate from old config sources to new identifier                                                            */
            /* ============================================================================================================ */

            $qb = $this->connection->createQueryBuilder()
                ->select('id, type, fsc_type')
                ->from('tl_form_field')
                ->where('type IN (:types)')
                ->andWhere('sc_columnset = "" OR sc_columnset IS NULL')
                ->andWhere('fsc_parent > "0" AND fsc_parent != "" AND fsc_parent IS NOT NULL')
                ->andWhere('fsc_type IS NOT NULL AND fsc_type != "" AND fsc_type != "deprecated"')
                ->setParameter('types', static::TYPES, Connection::PARAM_STR_ARRAY);

            $result = $qb->execute();
            $iterator = $result->iterateAssociative();

            foreach ($iterator as $item) {
                $scType = $item['fsc_type'] ?? null;
                if ($scType === null) {
                    continue;
                }
                $scColumnset = "globals.bootstrap3.$scType";
                $updates[] = ['tl_form_field', ['sc_columnset' => $scColumnset], ['id' => $item['id']]];
            }

            $result->free();


            foreach ($updates as $update) {
                $this->connection->update(...$update);
            }

            static::VERSION === Config::persist('sc_bs_formfield_type_migration', static::VERSION);

            return new MigrationResult(true, static::getName().' successful!');
        }
        catch (Throwable $e)
        {
            return new MigrationResult(false, 'Error '.static::getName(). ':' . $e->getMessage());
        }
    }
}