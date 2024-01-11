<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle;

use Contao\Config;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SubColumnsBootstrapBundle extends Bundle
{
    public const SUBCOLUMNS_TYPE_BOOTSTRAP4 = 'bootstrap4';
    public const SUBCOLUMNS_TYPE_BOOTSTRAP5 = 'bootstrap5';

    protected static string $subType;

    /**
     * This is the overhead of a typo fix within the constants above.
     * In order to ensure backward compatibility, any sub-column type string that is read from db should be run through
     * this method.
     *
     * It takes a subtype string, either of the correct options or the legacy typo ones.
     * Returns its refactored version.
     *
     * @param string $scType
     * @return string|null
     */
    public static function filterTypeString(string $scType): ?string
    {
        return $scType ? [
            'boostrap4' => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4,
            'boostrap5' => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5,
            SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4 => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4,
            SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5 => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5
        ][$scType] ?? null : null;
    }

    /**
     * Checks, if a sub-column type string belongs to this package.
     * A specific Bootstrap version (4/5) MAY be supplied.
     *
     * @param string $scType
     * @param int|null $bootstrapVersion
     * @return bool
     */
    public static function validSubType(string $scType, ?int $bootstrapVersion = null): bool
    {
        return $scType === self::filterTypeString($scType) && ($bootstrapVersion === null || substr($scType, -1) === (string)$bootstrapVersion);
    }

    /**
     * Sets the current sub-column type to the given value.
     *
     * @param string $subType The sub-column type string to set. Must be a valid sub-column type string.
     * @return void
     */
    public static function setSubType(string $subType): void
    {
        static::$subType = static::filterTypeString($subType);
    }

    /**
     * Retrieves the current sub-column type, if it is already set,
     * otherwise, it will be retrieved from the configuration and set.
     * If no sub-columns configuration is found, the default value is 'bootstrap4'.
     *
     * @return string The current sub-column type.
     */
    public static function getSubType(): string
    {
        if (isset(static::$subType)) {
            return static::$subType;
        }

        $subcolumns = Config::get('subcolumns') ?: 'bootstrap4';
        static::$subType = SubColumnsBootstrapBundle::filterTypeString($subcolumns) ?: $subcolumns;

        return static::$subType;
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
