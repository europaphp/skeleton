<?php

namespace Europa\Exception;

class Exception extends \Exception
{
    public static function toss()
    {
        throw new static(call_user_func_array('sprintf', func_get_args()), static::code());
    }

    public static function code()
    {
        return crc32(get_called_class());
    }
}