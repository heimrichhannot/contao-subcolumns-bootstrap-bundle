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
        return 'Subcolumns Bootstrap column type name migration';
    }

    public function shouldRun(): bool
    {
        $filtered = SubColumnsBootstrapBundle::filterTypeString(Config::get('subcolumns') ?? '');
        return $filtered && Config::get('subcolumns') !== $filtered;
    }

    public function run(): MigrationResult
    {
        $filtered = SubColumnsBootstrapBundle::filterTypeString(Config::get('subcolumns') ?? '');
        Config::set('subcolumns', $filtered);
        Config::persist('subcolumns', $filtered);

        return new MigrationResult(true, 'Updated subcolumns type name.');
    }
}