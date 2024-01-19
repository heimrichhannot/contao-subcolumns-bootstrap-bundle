<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Model;


use Contao\Model;
use Contao\StringUtil;

/**
 * @property int $id
 * @property int $pid
 * @property string $tstamp
 * @property string $title
 * @property string $description
 * @property int $columns
 * @property bool|int $useOutside
 * @property string $outsideClass
 * @property bool|int $useInside
 * @property string $insideClass
 * @property string $sizes
 * @property bool|int $published
 * @property string $cssID
 */
class ColumnsetModel extends Model
{
    protected static $strTable = 'tl_columnset';

    public function getSizes(): array
    {
        return StringUtil::deserialize($this->sizes) ?: [];
    }

    public function getColumnset(string $size): ?array
    {
        return StringUtil::deserialize($this->{"columnset_$size"} ?? null);
    }

    public function getCssID(): array
    {
        return StringUtil::deserialize($this->cssID) ?: [];
    }

    public function hasCssID(): bool
    {
        return !empty(array_filter($this->getCssID()));
    }
}
