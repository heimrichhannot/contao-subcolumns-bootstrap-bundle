<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Util;

/**
 * @internal Do not use this class in your code.
 */
class Color
{
    /**
     * @param $color string|array An array with the first element being the hex color and the second element being the alpha value.
     * @param bool $expectArray
     * @return string
     */
    public static function compile($color, bool $expectArray = false): string
    {
        if (empty($color)) {
            return '';
        }

        if (!\is_array($color))
        {
            if ($expectArray) {
                return '';
            }

            return "#$color";
        }

        if (empty($color[1]) || !\is_numeric($color[1]))
        {
            if (empty($color[0])) {
                return '';
            }

            return "#$color[0]";
        }

        $rgb = \implode(', ', self::hexColor2rgb($color[0]));
        $alpha = \round(((int) $color[1]) / 100, 2);
        return "rgba($rgb, $alpha)";
    }

    /**
     * Convert hex colors to rgb
     */
    public static function hexColor2rgb($color): array
    {
        $rgb = [];

        if (\strlen($color) === 6)
            // Try to convert using bitwise operation
        {
            $dec = \hexdec($color);
            $rgb['r'] = 0xFF & ($dec >> 0x10);
            $rgb['g'] = 0xFF & ($dec >> 0x8);
            $rgb['b'] = 0xFF & $dec;
        }
        elseif (\strlen($color) == 3)
            // Shorthand notation
        {
            $rgb['r'] = \hexdec(\str_repeat(\substr($color, 0, 1), 2));
            $rgb['g'] = \hexdec(\str_repeat(\substr($color, 1, 1), 2));
            $rgb['b'] = \hexdec(\str_repeat(\substr($color, 2, 1), 2));
        }

        return $rgb;
    }
}