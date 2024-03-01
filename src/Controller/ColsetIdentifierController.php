<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Controller;

use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetIdentifier;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;

class ColsetIdentifierController
{
    public function getColumnSettings(string|ColumnsetIdentifier $identifier): ?array
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        return match ($identifier->getSource()) {
            'globals' => $this->getGlobalColumnSettings(...$identifier->getParams()),
            'db' => $this->getDBColumnSettings(...$identifier->getParams()),
            default => null,
        };
    }

    private function getGlobalColumnSettings(string $profile, string $columnSet): ?array
    {
        return $GLOBALS['TL_SUBCL'][$profile]['sets'][$columnSet] ?? null;
    }

    public function getGlobalColumnsetProfile(string $profile): ?array
    {
        return $GLOBALS['TL_SUBCL'][$profile] ?? null;
    }

    public function tryGlobalColumnsetProfileByIdentifier(string|ColumnsetIdentifier $identifier): ?array
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        return $this->getGlobalColumnsetProfile(...$identifier->getParams());
    }

    public function tryColumnsetModelByIdentifier(string|ColumnsetIdentifier $identifier): ?ColumnsetModel
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);

        if (!$identifier) {
            return null;
        }

        if ($identifier->getSource() !== 'db') {
            return null;
        }

        return $this->tryColumnsetModel(...$identifier->getParams());
    }


    /**
     * @noinspection SqlResolve, SqlNoDataSourceInspection
     */
    public function tryColumnsetModel(string $table, string $id): ?ColumnsetModel
    {
        if ($table === 'tl_columnset')
        {
            return ColumnsetModel::findByPk($id);
        }

        return null;
    }

    public function getTitle(string|ColumnsetIdentifier $identifier): string
    {
        $identifier = ColumnsetIdentifier::deconstruct($identifier);
        $columnsetModel = $this->tryColumnsetModelByIdentifier($identifier);
        $globalProfile = $this->tryGlobalColumnsetProfileByIdentifier($identifier);
        $title = $columnsetModel->title ?? '';
        if (!$title && $globalProfile) {
            $title = $globalProfile['label'] ?? '';
            if ($title) {
                $title .= ':&ensp;' . ($identifier->getParams()[1] ?? '');
            } else {
                $title = implode(' ', $identifier->getParams());
            }
        }
        return $title;
    }

    private function getDBColumnSettings(string $table, string $id): ?array
    {
        $columnsetModel = $this->tryColumnsetModel($table, $id);

        if ($columnsetModel === null) {
            return null;
        }

        $breakpoints = $columnsetModel->getSizes();
        $cssClasses = [];

        foreach ($breakpoints as $breakpoint)
        {
            $colSetup = $columnsetModel->getColumnset($breakpoint);

            if (!$colSetup)
            {
                continue;
            }

            foreach ($colSetup as $i => $col)
            {
                $classes = &$cssClasses[$i];

                if ($width = $col['width']) {
                    $classes[] = "col-$breakpoint-$width";
                }

                if ($offset = $col['offset']) {
                    $offset = ($offset === 'reset') ? 0 : $offset;
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

}