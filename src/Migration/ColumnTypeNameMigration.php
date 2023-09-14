<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Migration;

use Contao\Config;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class ColumnTypeNameMigration implements MigrationInterface
{

    public function getName(): string
    {
        return 'Subcolumns Bottstrap column type name migration';
    }

    public function shouldRun(): bool
    {
        if (SubColumnsBootstrapBundle::validateTypeString(Config::get('subcolumns') ?? '')
            && !in_array(Config::get('subcolumns'), [SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4, SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5])
        ) {
            return true;
        }

        return false;

    }

    public function run(): MigrationResult
    {
        Config::set('subcolumns', SubColumnsBootstrapBundle::validateTypeString(Config::get('subcolumns')));
        Config::persist('subcolumns', SubColumnsBootstrapBundle::validateTypeString(Config::get('subcolumns')));

        return new MigrationResult(true, 'Updated subcolumns type name.');
    }
}