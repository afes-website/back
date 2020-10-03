<?php


namespace App\Libs;


class Boolean
{
    const truthy = ['1', 1, 'true', true];
    const falsy = ['0', 0, 'false', false];

    public static function validate($value){

        foreach (static::truthy as $i){
            if($i == $value) return true;
        }
        foreach (static::falsy as $i){
            if($i == $value) return true;
        }
        return false;
    }

    public static function value($value){
        foreach (static::truthy as $i){
            if($i === $value) return true;
        }
        foreach (static::falsy as $i){
            if($i === $value) return false;
        }
        return null;
    }
}
