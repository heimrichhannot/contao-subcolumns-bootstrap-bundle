<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao/test", name=TestController::class, defaults={"_scope" = "backend", "_token_check" = false})
 */
class TestController extends AbstractController
{
    private ?array $options = null;

    public function __construct()
    {
    }

    public function __invoke(): Response
    {
        $y = $this->getOptions();
        var_dump($y);
        var_dump($this->getColumnSettings('globals.bootstrap3.box-3/3/3/3'));
        var_dump($this->getColumnSettings('db.tl_columnset.6'));
        return new Response('Oke.');
    }

    public function getColumnSettings(string $identifier)
    {
        $exploded = explode('.', $identifier, 3);

        if (count($exploded) !== 3) {
            return null;
        }

        $source = $exploded[0];

        switch ($source) {
            case 'globals':
                return $this->getGlobalColumnSettings($exploded[1], $exploded[2]);
            case 'db':
                return $this->getDBColumnSettings($exploded[1], $exploded[2]);
        }

        return null;
    }

    private function getGlobalColumnSettings(string $columnSetContainer, string $columnSet)
    {
        return $GLOBALS['TL_SUBCL'][$columnSetContainer]['sets'][$columnSet] ?? null;
    }

    private function getDBColumnSettings(string $table, string $id)
    {
        if ($table !== 'tl_columnset') {
            return null;
        }

        /** @var Connection $db */
        $db = System::getContainer()->get('database_connection');
        $result = $db
            ->prepare('SELECT * FROM `tl_columnset` WHERE `id`=? LIMIT 1')
            ->executeQuery([$id])
            ->fetchAssociative();

        if (empty($result)) {
            return null;
        }

        $sizesSet = StringUtil::deserialize($result['sizes']);
        $sizes = [];

        foreach ($sizesSet as $size) {
            // todo
        }

        $x = $result;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getOptions(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        return $this->options = array_merge($this->getOptionsFromDatabase(), $this->getOptionsFromGlobals());
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getOptionsFromDatabase(): array
    {
        /** @var Connection $db */
        $db = System::getContainer()->get('database_connection');
        $columnSets = $db
            ->executeQuery('SELECT id, title, columns, description FROM `tl_columnset` ORDER BY columns')
            ->fetchAllAssociative();

        $types = [];

        foreach ($columnSets as $columnSet) {
            $types['[DB]']['db.tl_columnset.' . $columnSet['id']] = "[{$columnSet['columns']}] " . $columnSet['title']
                . (!empty($columnSet['description']) ? " ({$columnSet['description']})" : '');
        }

        return $types;
    }

    private function getOptionsFromGlobals(): array
    {
        $types = [];
        foreach ($GLOBALS['TL_SUBCL'] as $subType => $config) {
            if (str_contains($subType, 'yaml')) {
                continue;
            }
            foreach ($config['sets'] as $set => $columns) {
                $types['[GLOBALS] ' . $config['label']]['globals.' . $subType . '.' . $set] = $set;
            }
        }
        ksort($types);
        return $types;
    }

}