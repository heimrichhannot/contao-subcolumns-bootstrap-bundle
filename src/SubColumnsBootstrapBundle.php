<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SubColumnsBootstrapBundle extends Bundle
{
    public const SUBCOLUMNS_TYPE_BOOTSTRAP4 = 'bootstrap4';
    public const SUBCOLUMNS_TYPE_BOOTSTRAP5 = 'bootstrap5';

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
    public static function validateTypeString(string $scType): ?string
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
    public static function validSubtype(string $scType, ?int $bootstrapVersion = null): bool
    {
        return $scType === self::validateTypeString($scType) && ($bootstrapVersion === null || substr($scType, -1) == (string)$bootstrapVersion);
    }
}
