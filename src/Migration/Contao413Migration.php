<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\LayoutModel;
use Contao\StringUtil;

class Contao413Migration implements MigrationInterface
{
    public function getName(): string
    {
        return '50Hertz Contao 4.13 Migration';
    }

    public function shouldRun(): bool
    {
        return $this->migrateLayoutJqueryField();
    }

    public function run(): MigrationResult
    {
        $this->migrateLayoutJqueryField(true);

        return new MigrationResult(true, 'Finished '.$this->getName());
    }

    private function migrateLayoutJqueryField(bool $run = false): bool
    {
        try {
            $layouts = LayoutModel::findMultipleByIds([1,2,4,5]);
        } catch (\Exception $exception) {
            if ($run) {
                throw $exception;
            } else {
                return false;
            }
        }

        foreach ($layouts as $layout) {
            if (!empty(StringUtil::deserialize($layout->jquery, true))) {
                if ($run) {
                    $layout->jquery = serialize([]);
                    $layout->save();
                } else {
                    return true;
                }
            }
        }

        return false;
    }
}