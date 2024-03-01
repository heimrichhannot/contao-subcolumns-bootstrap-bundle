<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\DataContainer;

class ContentContainer extends AbstractColsetContainer
{
    const TABLE = 'tl_content';

    public static function getTable(): string
    {
        return static::TABLE;
    }
}