<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;
use Symfony\Component\HttpKernel\KernelInterface;

class ContentContainer extends AbstractColsetContainer
{
    const TABLE = 'tl_content';

    public static function getTable(): string
    {
        return static::TABLE;
    }

    public function __construct(
        ColumnsetContainer $columnsetContainer,
        Connection $connection,
        Database $database,
        KernelInterface $kernel
    ) {
        parent::__construct($columnsetContainer, $connection, $database, $kernel);
    }
}