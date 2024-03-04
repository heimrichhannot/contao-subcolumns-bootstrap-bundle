<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Doctrine\DBAL\Connection;

class ArticleContainer extends AbstractColsetParentContainer
{
    const DB_COL_SC_PARENT = 'sc_parent';
    const DB_COL_SC_CHILDREN = 'sc_childs';
    const DB_COL_SC_NAME = 'sc_name';
    const DB_COL_SC_TYPE = 'sc_type';
    const DB_COL_SORTING = 'sorting';

    public function __construct(
        private ContentContainer $contentContainer,
        Connection $connection
    ) {
        parent::__construct($connection);
    }

    public function getColsetContainer(): AbstractColsetContainer
    {
        return $this->contentContainer;
    }
}