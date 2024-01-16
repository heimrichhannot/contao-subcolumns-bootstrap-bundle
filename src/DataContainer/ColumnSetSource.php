<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class ColumnSetSource
{
    protected DatabaseUtil $database;

    public function __construct(DatabaseUtil $database)
    {
        $this->database = $database;
    }

    public function getOptions()
    {
        return $this->getOptionsFromGlobals();
    }

    private function getOptionsFromGlobals(): array
    {
        $types = [];
        foreach ($GLOBALS['TL_SUBCL'] as $subType => $config) {
            foreach ($config['sets'] as $set => $columns) {
                $types[$config['label']][$subType . '.' . $set] = $set;
            }
        }
        return $types;
    }
}