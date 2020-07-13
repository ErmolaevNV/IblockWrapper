<?php

namespace ErmolaevNV\Traits;

trait GetCode
{
    protected static $code = '';
    
    public static function getCode(): string {
        if (static::$code === '') {
            $arClass = explode('\\', static::class);
            return $arClass[count($arClass)-1];
        }
        return static::$code;
    }
}