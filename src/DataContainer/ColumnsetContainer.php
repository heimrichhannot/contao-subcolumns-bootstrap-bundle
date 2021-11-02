<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\Config;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\Message;
use Doctrine\DBAL\Connection;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;

class ColumnsetContainer
{
    /**
     * @var Connection
     */
    private              $connection;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    public function __construct(Connection $connection, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }


    public function onLoadCallback($dc): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$this->scopeMatcher->isBackendRequest($request) || 'edit' !== $request->get('act')) {
            return;
        }

        $sizes = $GLOBALS['TL_SUBCL'][Config::get('subcolumns')]['sizes'] ?? null;
        if (!$sizes) {
            return;
        }

        $tableColumns = $this->connection->getSchemaManager()->listTableColumns('tl_columnset');

        foreach ($sizes as $size)
        {
            if (!array_key_exists('columnset_'.strtolower($size), $tableColumns)) {
                Message::addError($GLOBALS['TL_LANG']['ERR']['huhSubColMissingTableRow']);
                return;
            }
        }
    }
}