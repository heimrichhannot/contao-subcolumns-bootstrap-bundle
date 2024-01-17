<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

class ColumnsetContainer
{
    private Connection $connection;
    private RequestStack $requestStack;
    private ScopeMatcher $scopeMatcher;
    private KernelInterface $kernel;
    private ?array $options = null;

    public function __construct(
        Connection $connection,
        RequestStack $requestStack,
        ScopeMatcher $scopeMatcher,
        KernelInterface $kernel
    )
    {
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
        $this->kernel = $kernel;
    }

    /**
     * @Callback(table="tl_columnset", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$this->scopeMatcher->isBackendRequest($request) || 'edit' !== $request->get('act')) {
            return;
        }

        $this->preparePalette((int)$dc->id);

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

    protected function preparePalette(int $id): void
    {
        $model  = ColumnsetModel::findByPk($id);
        $sizes  = array_merge(StringUtil::deserialize($model->sizes, true));

        $pm = PaletteManipulator::create();

        foreach ($sizes as $size) {
            $pm->addField('columnset_' . $size, 'sizes', PaletteManipulator::POSITION_APPEND);
        }

        $pm->applyToPalette('default', 'tl_columnset');
    }

    /* ===== ERICs WORK BELOW (this comment is temporary) ===== */

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getColumnSettings(string $identifier): ?array
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

    private function getGlobalColumnSettings(string $columnSetContainer, string $columnSet): ?array
    {
        return $GLOBALS['TL_SUBCL'][$columnSetContainer]['sets'][$columnSet] ?? null;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    private function getDBColumnSettings(string $table, string $id): ?array
    {
        if ($table !== 'tl_columnset') {
            return null;
        }

        $result = $this->connection->fetchAssociative('SELECT * FROM `tl_columnset` WHERE `id`=? LIMIT 1', [$id]);

        if (empty($result)) {
            return null;
        }

        $breakpoints = StringUtil::deserialize($result['sizes']) ?: [];
        $cssClasses = [];

        foreach ($breakpoints as $breakpoint) {
            $colSetup = StringUtil::deserialize($result["columnset_$breakpoint"] ?? null);
            if (!$colSetup) {
                continue;
            }

            foreach ($colSetup as $i => $col) {
                $classes = &$cssClasses[$i];

                if ($width = $col['width']) {
                    $classes[] = "col-$breakpoint-$width";
                }

                if ($offset = $col['offset']) {
                    $classes[] = "offset-$breakpoint-$offset";
                }

                if ($order = $col['order']) {
                    $order = explode('-', $order);
                    if (sizeof($order) === 2) {
                        $classes[] = "$order[0]-$breakpoint-$order[1]";
                    }
                }
            }
        }

        return array_map(function ($arrClasses) {
            return [join(' ', $arrClasses)];
        }, $cssClasses);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getOptions(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        return $this->options = array_merge(
            $this->getOptionsFromDatabase(),
            $this->getOptionsFromGlobals()
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    private function getOptionsFromDatabase(): array
    {
        $columnSets = $this->connection
            ->fetchAllAssociative('SELECT id, title, columns, description FROM `tl_columnset` ORDER BY columns');

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

    /**
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    private function moveRows($pid, $ptable, $sorting, $ammount=128)
    {
        $database = System::getContainer()->get('database_connection');
        $database
            ->prepare("UPDATE `tl_content` SET sorting = sorting + ? WHERE pid=? AND ptable=? AND sorting > ?")
            ->executeQuery([$ammount, $pid, $ptable, $sorting]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function onUpdate(DataContainer $dc): bool
    {
        /** @var Result $record */
        $record = $dc->activeRecord;
        $colset = $record->sc_columnset ?? null;

        if (!$colset)
        {
            return false;
        }

        $colSettings = $this->getColumnSettings($colset);

        if (empty($colSettings))
        {
            return false;
        }

        $children = StringUtil::deserialize($record->sc_childs ?? null) ?: null;

        return $this->createColset($record, $colset, $colSettings, $children);
    }

    /**
     * @throws Exception
     */
    private function updateEqualColset($record, array $children): bool
    {
        foreach ($children as $i => $childId)
        {
            $i++;
            $scName = $record->sc_name . ($i === count($children) ? '-End' : ("-Part-$i"));

            $update = [
                'sc_gap' => $record->sc_gap,
                'sc_gapdefault' => $record->sc_gapdefault,
                'sc_sortid' => $i,
                'sc_name' => $scName,
                'sc_color' => StringUtil::deserialize($record->sc_color)
                    ? $record->sc_color
                    : serialize($record->sc_color),
                'sc_columnset' => $record->sc_columnset
            ];

            $this->connection->update('tl_content', $update, ['id' => $childId]);
        }

        return true;
    }

    private function updateReduceColset($record, array $colSettings, array $children = null): bool
    {
        $diff = count($children) - count($colSettings);

        $toDelete = array_slice($children, count($colSettings) - 1, $diff);

        return false;

        for ($i = 1; $i <= $diff; $i++)
        {
            $intChildId = array_pop($children);
            $this->Database->prepare("DELETE FROM tl_content WHERE id=?")
                ->execute($intChildId);
        }

        $this->Database->prepare("UPDATE tl_content %s WHERE id=?")
            ->set(array('sc_childs'=>$children))
            ->execute($record->id);

        /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
        $arrSet = array(
            'sc_type'=>$sc_type,
            'sc_gap' => $record->sc_gap,
            'sc_gapdefault' => $record->sc_gapdefault,
            'sc_color' => $record->sc_color
        );

        foreach($children as $value)
        {

            $this->Database->prepare("UPDATE tl_content %s WHERE id=?")
                ->set($arrSet)
                ->execute($value);

        }

        /*  Den Typ des letzten Elements auf End-ELement umsetzen und FSC-namen anpassen */
        $intChildId = array_pop($children);

        $arrSet['sc_name'] = $record->sc_name.'-End';
        $arrSet['type'] = 'colsetEnd';

        $this->Database->prepare("UPDATE tl_content %s WHERE id=?")
            ->set($arrSet)
            ->execute($intChildId);

        return true;
    }

    /**
     * @param ContentModel|Result $record
     * @param string $colset
     * @param array $colSettings
     * @param array|null $children
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    private function createColset($record, string $colset, array $colSettings, array $children = null): bool
    {
        /* Neues Spaltenset anlegen */
        if (empty($children))
        {
            return $this->setupColset($record, $colset, $colSettings);
        }

        /* Gleiche Spaltenzahl */
        if (count($colSettings) === count($children))
        {
            return $this->updateEqualColset($record, $children);
        }

        /* Weniger Spalten */
        if (count($colSettings) < count($children))
        {
            return $this->updateReduceColset($record, $colSettings, $children);
        }

        return false;

        /* Mehr Spalten */
        if (count($children) < count($colSettings))
        {
            $intDiff = count($colSettings) - count($children);

            $objEnd = $this->Database->prepare("SELECT id,sorting,sc_sortid FROM tl_content WHERE id=?")->execute($children[count($children)-1]);

            $this->moveRows($record->pid,$record->ptable,$objEnd->sorting,64 * ( $intDiff) );

            /*  Den Typ des letzten Elements auf End-ELement umsetzen und SC-namen anpassen */
            $intChildId	= count($children);
            $arrSet['sc_name'] = $record->sc_name.'-Part-'.($intChildId);
            $arrSet['type'] = 'colsetPart';

            $this->Database->prepare("UPDATE tl_content %s WHERE id=?")
                ->set($arrSet)
                ->execute($objEnd->id);



            $intFscSortId = $objEnd->sc_sortid;
            $intSorting = $objEnd->sorting;

            $arrSet = array('type' => 'colsetPart',
                'pid' => $record->pid,
                'ptable' => $record->ptable,
                'tstamp' => time(),
                'sorting' => 0,
                'sc_name' => '',
                'sc_type'=>$sc_type,
                'sc_parent' => $record->id,
                'sc_sortid' => 0,
                'sc_gap' => $record->sc_gap,
                'sc_gapdefault' => $record->sc_gapdefault,
                'sc_color' => $record->sc_color
            );

            if(in_array('GlobalContentelements',$this->Config->getActiveModules()))
            {
                $arrSet['do'] = $this->Input->get('do');
            }

            if($intDiff>0)
            {

                /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
                for($i=1;$i<$intDiff;$i++)
                {
                    ++$intChildId;
                    ++$intFscSortId;
                    $intSorting += 64;
                    $arrSet['sc_name'] = $record->sc_name.'-Part-'.($intChildId);
                    $arrSet['sc_sortid'] = $intFscSortId;
                    $arrSet['sorting'] = $intSorting;

                    $objInsertElement = $this->Database->prepare("INSERT INTO tl_content %s")
                        ->set($arrSet)
                        ->execute();

                    $insertElement = $objInsertElement->insertId;

                    $children[] = $insertElement;

                }

            }

            /* Andere Daten im Colset anpassen - Spaltenabstand und SpaltenSet-Typ */
            $arrData = array(
                'sc_type'=>$sc_type,
                'sc_gap' => $record->sc_gap,
                'sc_gapdefault' => $record->sc_gapdefault,
                'sc_color' => $record->sc_color
            );

            foreach($children as $value)
            {

                $this->Database->prepare("UPDATE tl_content %s WHERE id=?")
                    ->set($arrData)
                    ->execute($value);

            }

            /* Neues End-element erzeugen */
            $arrSet['sorting'] = $intSorting + 64;
            $arrSet['type'] = 'colsetEnd';
            $arrSet['sc_name'] = $record->sc_name.'-End';
            $arrSet['sc_sortid'] = ++$intFscSortId;

            $insertElement = $this->Database->prepare("INSERT INTO tl_content %s")
                ->set($arrSet)
                ->execute()
                ->insertId;

            $children[] = $insertElement;

            /* Kindelemente in Startelement schreiben */
            $insertElement = $this->Database->prepare("UPDATE tl_content %s WHERE id=?")
                ->set(array('sc_childs'=>$children))
                ->execute($record->id);

            return true;

        }

        return false;
    }

    /**
     * @param ContentModel|Result $record
     * @return true
     * @throws \Doctrine\DBAL\Exception
     */
    private function setupColset($record, string $colset, array $colSettings): bool
    {
        $children = [];
        $columnCount = count($colSettings);

        $this->moveRows($record->pid, $record->ptable, $record->sorting, 128 * ($columnCount + 1));

        $insert = [
            'pid' => $record->pid,
            'ptable' => $record->ptable,
            'tstamp' => time(),
            'sorting' => 0,
            'type' => 'colsetPart',
            'sc_name' => '',
            'sc_type' => 'deprecated',
            'sc_parent' => $record->id,
            'sc_sortid' => 0,
            'sc_gap' => $record->sc_gap,
            'sc_gapdefault' => $record->sc_gapdefault,
            'sc_color' => serialize($record->sc_color ?? []),
            'sc_columnset' => $record->sc_columnset
        ];

        try {
            if ($this->kernel->getBundle('GlobalContentelements'))
            {
                $insert['do'] = Input::get('do');
            }
        } catch (InvalidArgumentException $e) {
            // do nothing
        }

        for ($i = 1; $i < $columnCount; $i++)
        {
            $insert['sorting'] = $record->sorting + ($i + 1) * 64;
            $insert['sc_name'] = "{$record->sc_name}-Part-$i";
            $insert['sc_sortid'] = $i;

            if ($this->connection->insert("tl_content", $insert) > 0)
            {
                $children[] = $this->connection->lastInsertId();
            }
        }

        $insert['sorting'] = $record->sorting + ($i + 1) * 64;
        $insert['type'] = 'colsetEnd';
        $insert['sc_name'] = "{$record->sc_name}-End";
        $insert['sc_sortid'] = $columnCount;

        if ($this->connection->insert("tl_content", $insert) > 0) {
            $children[] = $this->connection->lastInsertId();
        }

        $updatedRows = $this->connection->update('tl_content', [
            'sc_childs' => serialize($children),
            'sc_parent' => $record->id
        ], ['id' => $record->id]);

        return $updatedRows > 0;
    }

    /**
     * @throws Exception
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    public function onDelete(DataContainer $dc)
    {
        $delRecord = $this->connection->fetchAssociative(
            "SELECT id, type, sc_parent FROM tl_content WHERE id=?",
            [$dc->id]
        );

        if (!in_array($delRecord['type'], ['colsetStart', 'colsetPart', 'colsetEnd']))
        {
            return;
        }

        if ($delRecord['type'] === 'colsetStart')
        {
            $parent = $delRecord;
        }
        else
        {
            $parent = $this->connection->fetchAssociative(
                "SELECT id, sc_childs FROM tl_content WHERE id=?",
                [$delRecord['sc_parent'] ?? -1]
            );
        }

        $toDelete = StringUtil::deserialize($parent['sc_childs']) ?: [];
        $toDelete[] = $parent['id'];

        if (empty($toDelete))
        {
            return;
        }

        $this->connection->createQueryBuilder()
            ->delete('tl_content')
            ->where('id IN (:ids)')
            ->setParameter(':ids', $toDelete, Connection::PARAM_INT_ARRAY)
            ->execute()
        ;
    }
}