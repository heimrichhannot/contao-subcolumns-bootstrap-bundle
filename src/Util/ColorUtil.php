<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Util;

class ColorUtil
{
    /**
     * Compile a color value and return a hex or rgba color
     */
    public static function compileColor(array|string $color): string
    {
        if (!is_array($color))
        {
            return "#$color";
        }
        elseif (empty($color[1]))
        {
            return "#$color[0]";
        }
        else
        {
            $rgb = static::convertHexColor($color[0], $blnWriteToFile ?? false, $vars ?? []);
            $csv = implode(',', $rgb);
            $alpha = $color[1] / 100;
            return "rgba($csv,$alpha)";
        }
    }

    /**
     * Convert hex colors to rgb
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    public static function convertHexColor(string $color, bool $writeToFile = false, array $vars = []): array
    {
        // Support global variables
        if (str_starts_with($color, '$'))
        {
            if (!$writeToFile)
            {
                return [$color];
            }
            else
            {
                $color = str_replace(array_keys($vars), array_values($vars), $color);
            }
        }

        $rgb = [];

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
}