<?php


namespace App\Libs;

class Boolean {

    const TRUTHY = ['1', 1, 'true', true];
    const FALSY = ['0', 0, 'false', false];

    public static function validate($value) {

        foreach (static::TRUTHY as $i) {
            if ($i === $value) return true;
        }
        foreach (static::FALSY as $i) {
            if ($i === $value) return true;
        }
        return false;
    }

    public static function value($value) {
        foreach (static::TRUTHY as $i) {
            if ($i === $value) return true;
        }
        foreach (static::FALSY as $i) {
            if ($i === $value) return false;
        }
        return null;
    }
}
