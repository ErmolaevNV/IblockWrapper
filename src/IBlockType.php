<?php


namespace ErmolaevNV;

abstract class IBlockType
{
    protected static $code;

    public static function getCode() {
        return static::$code;
    }
}
