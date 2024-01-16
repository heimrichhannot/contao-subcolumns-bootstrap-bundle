<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Backend;

use Contao\Backend;
use Contao\ContentModel;
use Contao\DataContainer;
use Contao\StringUtil;
use HeimrichHannot\SubColumnsBootstrapBundle\Model\ColumnsetModel;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

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
    protected static function prepareSize($size, array $definition)
    {
        if ($size === 'xs')
        {
            $css = sprintf('col-%s', $definition['width']);
        }
        else
        {
            $css = sprintf('col-%s-%s', $size, $definition['width']);
        }

        if ($definition['offset']) {
            if ($size === 'xs')
            {
                $css .= sprintf(' offset-%s', $definition['offset']);
            }
            else
            {
                $css .= sprintf(' offset-%s-%s', $size, $definition['offset']);
            }
        }

        if ($definition['order']) {
            if ($size === 'xs')
            {
                $css .= sprintf(' %s', $definition['order']);
            }
            else
            {
                $css .= ' ' . str_replace('-', '-' . $size . '-', $definition['order']);
            }
        }

        return $css;
    }


    /**
     * add column set field to the colsetStart content element. We need to do it dynamically because subcolumns
     * creates its palette dynamically
     *
     * @param $dc
     */
    public function appendColumnsetIdToPalette(\DataContainer $dc)
    {
        if (!SubColumnsBootstrapBundle::validSubType($GLOBALS['TL_CONFIG']['subcolumns'])) return;

        $arrDca = &$GLOBALS['TL_DCA']['tl_content'];

        $content = ContentModel::findByPK($dc->id);

        $arrDca['palettes']['colsetStart'] = str_replace('sc_name', '', $arrDca['palettes']['colsetStart']);
        $arrDca['palettes']['colsetStart'] = str_replace('sc_type', 'sc_type,sc_name', $arrDca['palettes']['colsetStart']);

        if ($content && isset($content->sc_type) && $content->sc_type > 0) {
            $arrDca['palettes']['colsetStart'] = str_replace('sc_type', 'sc_type,columnset_id,addContainer', $arrDca['palettes']['colsetStart']);
            $arrDca['palettes']['colsetStart'] = str_replace('sc_color', '', $arrDca['palettes']['colsetStart']);
        }
    }


    /**
     * create a MCW row for each column
     *
     * @param string $value deseriazable value, for getting an array
     * @param DataContainer $mcw multi column wizard or DC_Table
     * @return mixed
     */
    public function createColumns($value, $mcw)
    {
        $columns = (int)$mcw->activeRecord->columns;
        $value   = StringUtil::deserialize($value, true);
        $count   = count($value);

        // initialize columns
        if ($count == 0) {
            for ($i = 0; $i < $columns; $i++) {
                $value[$i]['width'] = floor(12 / $columns);
            }
        } // reduce columns if necessary
        elseif ($count > $columns) {
            $count = count($value) - $columns;

            for ($i = 0; $i < $count; $i++) {
                array_pop($value);
            }
        } // make sure that column numbers has not changed
        else {
            for ($i = 0; $i < ($columns - $count); $i++) {
                $value[$i + $count]['width'] = floor(12 / $columns);
            }
        }

        return $value;
    }


    /**
     * replace subcolumns getAllTypes method, to load all created columnsets.
     *
     * @param DataContainer $dc
     * @return array
     */
    public function getAllTypes($dc)
    {
        if (!SubColumnsBootstrapBundle::validSubType($GLOBALS['TL_CONFIG']['subcolumns'])) {
            $sc = new \tl_content_sc();
            return @$sc->getAllTypes();
        }

        $this->import('Database');
        $collection = $this->Database->execute('SELECT columns FROM tl_columnset GROUP BY columns ORDER BY columns');

        $types = [];

        while ($collection->next()) {
            $types[] = $collection->columns;
        }

        /*while ($collection->next()) {
            $types['Aus Datenbank'][] = $collection->columns;
        }

        foreach ($GLOBALS['TL_SUBCL'] as $subType => $config) {
            foreach ($config['sets'] as $set => $columns) {
                $types[$config['label']][$subType . '.' . $set] = $set;
            }
        }

        ksort($types);*/

        return $types;
    }


    /**
     * get all columnsets which fits to the selected type
     * @param $dc
     * @return array
     */
    public function getAllColumnsets($dc)
    {
        $collection = ColumnsetModel::findBy('published=1 AND columns', $dc->activeRecord->sc_type, ['order' => 'title']);
        $set        = [];

        if ($collection !== null) {
            while ($collection->next()) {
                $set[$collection->id] = $collection->title;
            }
        }

        return $set;
    }
}
