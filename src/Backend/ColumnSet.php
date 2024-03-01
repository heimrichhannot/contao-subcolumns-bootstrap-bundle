<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;

use Contao\Backend;
use Contao\ContentModel;
use Contao\DataContainer;
use Contao\StringUtil;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

/**
 * @deprecated
 */
class ColumnSet extends Backend
{
    /**
     * store container so it has not be regenerated for every column set part
     * @var array
     */
    protected static $container = [];

    /**
     * prepare the container which sub columns expects
     *
     * @param int $id id of the columnset
     * @return array
     * @deprecated I guess this won't be need in the future. -- Eric.
     */
    public static function prepareContainer($id)
    {
        // use array key exists so non existing column will researched
        if (array_key_exists('id', self::$container)) {
            return static::$container[$id];
        }

        $model = ColumnsetModel::findByPk($id);

        if ($model === null) {
            static::$container[$id] = null;
            return null;
        }

        $sizes     = StringUtil::deserialize($model->sizes, true);
        $container = [];

        foreach ($sizes as $size) {
            $key     = 'columnset_' . $size;
            $columns = StringUtil::deserialize($model->{$key}, true);

            foreach ($columns as $index => $column) {
                if (isset($container[$index][0])) {
                    $container[$index][0] .= ' ' . self::prepareSize($size, StringUtil::deserialize($column, true));
                } else {
                    $container[$index][0] = self::prepareSize($size, StringUtil::deserialize($column, true));
                }
            }
        }

        static::$container[$id] = $container;

        return $container;
    }


    /**
     * generates the css class defnition for one column
     *
     * @param string $size the selected size
     * @param array $definition the column definition
     * @return string
     */
    protected static function prepareSize(string $size, array $definition): string
    {
        $css = match($size) {
            'xs' => sprintf('col-%s', $definition['width']),
            default => sprintf('col-%s-%s', $size, $definition['width'])
        };

        if ($definition['offset'])
        {
            $css .= match($size) {
                'xs' => sprintf(' offset-%s', $definition['offset']),
                default => sprintf(' offset-%s-%s', $size, $definition['offset'])
            };
        }

        if ($definition['order'])
        {
            $css .= match($size) {
                'xs' => sprintf(' %s', $definition['order']),
                default => ' ' . str_replace('-', '-' . $size . '-', $definition['order'])
            };
        }

        return $css;
    }
}
