<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle;

use Contao\Config;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SubColumnsBootstrapBundle extends Bundle
{
    public const SUBCOLUMNS_PROFILE_BOOTSTRAP3 = 'bootstrap3';
    public const SUBCOLUMNS_PROFILE_BOOTSTRAP4 = 'bootstrap4';
    public const SUBCOLUMNS_PROFILE_BOOTSTRAP5 = 'bootstrap5';

    protected static string $profile;

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
    public static function filterProfile(string $scType): ?string
    {
        return $scType ? [
            'bootstrap' => SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP3,
            'boostrap4' => SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4,
            'boostrap5' => SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5,
            SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP3 => SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP3,
            SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4 => SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP4,
            SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5 => SubColumnsBootstrapBundle::SUBCOLUMNS_PROFILE_BOOTSTRAP5,
        ][$scType] ?? null : null;
    }

    /**
     * Checks, if a sub-column type string belongs to this package.
     * A specific Bootstrap version (4/5) MAY be supplied.
     *
     * @param string|null $scType
     * @param int|null $bootstrapVersion
     * @return bool
     */
    public static function validProfile(string $scType = null, ?int $bootstrapVersion = null): bool
    {
        $scType ??= static::getProfile();
        return $scType === static::filterProfile($scType) && ($bootstrapVersion === null || substr($scType, -1) === (string)$bootstrapVersion);
    }

    /**
     * Sets the current sub-column type to the given value.
     *
     * @param string $profile The sub-column type string to set. Must be a valid sub-column type string.
     * @return void
     */
    public static function setProfile(string $profile): void
    {
        static::$profile = static::filterProfile($profile);
    }

    /**
     * Retrieves the current sub-column type, if it is already set,
     * otherwise, it will be retrieved from the configuration and set.
     * If no sub-columns configuration is found, the default value is 'bootstrap4'.
     *
     * @return string The current sub-column type.
     */
    public static function getProfile(): string
    {
        if (isset(static::$profile)) {
            return static::$profile;
        }

        $subcolumns = Config::get('subcolumns') ?: 'bootstrap4';
        static::$profile = SubColumnsBootstrapBundle::filterProfile($subcolumns) ?: $subcolumns;

        return static::$profile;
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
