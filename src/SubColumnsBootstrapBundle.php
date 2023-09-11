<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SubColumnsBootstrapBundle extends Bundle {
    const SUBCOLUMNS_TYPE_BOOTSTRAP4 = 'bootstrap4';
    const SUBCOLUMNS_TYPE_BOOTSTRAP5 = 'bootstrap5';

    public static function validateTypeString(string $scType): ?string {
        return $scType ? [
            'boostrap4' => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4,
            'boostrap5' => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5,
            SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4 => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP4,
            SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5 => SubColumnsBootstrapBundle::SUBCOLUMNS_TYPE_BOOTSTRAP5
        ][$scType] ?? null : null;
    }

    public static function validSubtype(string $scType, ?int $bootstrapVersion = null): bool {
        return $scType === self::validateTypeString($scType) && ($bootstrapVersion === null || substr($scType, -1) == (string) $bootstrapVersion);
    }
}
