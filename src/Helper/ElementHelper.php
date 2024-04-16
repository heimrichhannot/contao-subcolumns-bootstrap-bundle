<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Helper;

use Contao\BackendTemplate;
use Contao\Template;
use HeimrichHannot\SubColumnsBootstrapBundle\DataContainer\ColumnsetContainer;
use HeimrichHannot\SubColumnsBootstrapBundle\Subcolumn\SubcolumnSet;

class ElementHelper
{
    public const POSITION_START = 'start';
    public const POSITION_PART = 'part';
    public const POSITION_END = 'end';

    private ColumnsetContainer $columnsetContainer;

    public function __construct(ColumnsetContainer $columnsetContainer)
    {
        $this->columnsetContainer = $columnsetContainer;
    }

    public function getSet(string $identifier): ?SubcolumnSet
    {
        $exploded = explode('.', $identifier, 3);

        if (count($exploded) !== 3) {
            return null;
        }

        return new SubcolumnSet(
            $exploded[0],
            $exploded[1],
            $exploded[2],
            $this->getColumnset($identifier)
        );
    }

    public function generateTitle(?string $columnSet, string $name, ?string $position = null): string
    {
        $title = $columnSet ? $this->columnsetContainer->getTitle($columnSet) : '-- undefined --';
        $separator = '';
        if ($position) {
            switch ($position) {
                case static::POSITION_START:
                    $separator = "<span style='display:inline-block;width:80px;overflow:hidden;margin-right:1em;'>┌─────────</span>";
                    break;
                case static::POSITION_PART:
                    $separator = "<span style='display:inline-block;width:80px;overflow:hidden;margin-right:1em;'>├─────────</span>";
                    break;
                case static::POSITION_END:
                    $separator = "<span style='display:inline-block;width:80px;overflow:hidden;margin-right:1em;'>└─────────</span>";
                    break;
            }
        }

        return "$separator<strong>$title</strong>&emsp;<small>$name</small>";
    }

    public function generateBackendMiniTable(int $count, int $position): string
    {
        $isEndElement = $position === $count;

        $miniset = '<table style="margin-bottom: 6px;"><tr>';
        for ($i = 0; $i < $count; $i++)
        {
            if ($isEndElement) {
                $miniset .= '<td style="width:50px;height:25px;border: 1px solid black;text-align:center;background-color:red;"><span style="color: white;">' . ($i + 1) . '</span></div>';

            } else {
                $miniset .= '<td style="width:50px;height:25px;border: 1px solid black;text-align:center;'.(($position === $i) ? 'background-color:green;' : '').'"><span style="color: white;">' . ($i + 1) . '</span></div>';
            }

        }
        $miniset .= '</tr></table>';

        return $miniset;
    }

    public function getColumnset(string $identifier): array
    {
        $columnset = $this->columnsetContainer->getColumnSettings($identifier);
        if ($columnset === null) {
            throw new \Exception("The requested column-set \"$identifier\" could not be found.");
        }
        return $columnset;
    }

    public function renderBackendTemplate(string $title, string $color, ?string $visualSet, ?string $hint)
    {
        $this->Template = new BackendTemplate('be_subcolumns');
        $this->Template->setColor = $color;
        $this->Template->colsetTitle = $title;
        $this->Template->visualSet = $visualSet;
        $this->Template->hint = $hint;

        return $this->Template->parse();
    }

    public static function getColor(?string $color): string
    {
        $arrColor = unserialize($color);
        // avoid firing compileColor for php8 compatibility
        if (is_array($arrColor) && count($arrColor) === 2 && empty($arrColor[1])) {
            return '';
        } else {
            return static::compileColor($arrColor);
        }

    }

    /**
     * Compile a color value and return a hex or rgba color
     * @param mixed
     * @param boolean
     * @param array
     * @return string
     */
    public static function compileColor($color)
    {
        if (!is_array($color)) {
            return "#$color";
        } elseif (empty($color[1])) {
            return "#$color[0]";
        } else {
            return 'rgba(' . implode(',', ElementHelper::convertHexColor($color[0])) . ',' . ($color[1] / 100) . ')';
        }
    }

    /** @noinspection DuplicatedCode */
    public static function convertHexColor($color, $blnWriteToFile=false, $vars=array())
    {
        // Support global variables
        if (strncmp($color, '$', 1) === 0)
        {
            if (!$blnWriteToFile)
            {
                return array($color);
            }
            else
            {
                $color = str_replace(array_keys($vars), array_values($vars), $color);
            }
        }

        $rgb = array();

        // Try to convert using bitwise operation
        if (strlen($color) == 6)
        {
            $dec = hexdec($color);
            $rgb['red'] = 0xFF & ($dec >> 0x10);
            $rgb['green'] = 0xFF & ($dec >> 0x8);
            $rgb['blue'] = 0xFF & $dec;
        }

        // Shorthand notation
        elseif (strlen($color) == 3)
        {
            $rgb['red'] = hexdec(str_repeat(substr($color, 0, 1), 2));
            $rgb['green'] = hexdec(str_repeat(substr($color, 1, 1), 2));
            $rgb['blue'] = hexdec(str_repeat(substr($color, 2, 1), 2));
        }

        return $rgb;
    }

    public function legacyGridFormatting(Template $template, SubcolumnSet $colSet, bool $useGap, ?string $gap)
    {
        $hasGap = (bool)$GLOBALS['TL_SUBCL'][$colSet->name]['gap'] ?? false;
        $useInner = (bool)$GLOBALS['TL_SUBCL'][$colSet->name]['inside'] ?? false;

        if (!$useGap || !$hasGap)
        {
            $useInner = false;
        }
        else
        {
            ElementHelper::calculateGap($template, $gap, $colSet->getColCount());
        }

        $template->useInside = $useInner;
        $template->column = $colSet->sets[0][0] . ' col_1' . ' first';
        $template->inside = $container[0][1] ?? '';
    }

    public static function calculateGap(Template $template, ?string $gap, int $colCount)
    {
        $gap_value = $gap ?: ($GLOBALS['TL_CONFIG']['subcolumns_gapdefault'] ?? 12);

        $factor = [
            2 => 0.5,
            3 => 0.666,
            4 => 0.75,
            5 => 0.8,
        ][$colCount] ?? 0;

        if ($factor > 0) {
            $template->gap = ['right' => ceil($factor * $gap_value) . 'px'];
        }
    }
}