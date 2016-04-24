<?php

namespace Evand\Recommenderir;

use InvalidArgumentException;

/**
 * Class Validator
 * @package Evand\Recommenderir
 */
trait Validator
{

    /**
     * @param array  $array
     * @param        $functionName
     * @param string $key
     *
     * @return bool
     */
    protected function validateArray(array $array, $functionName, $key = 'item')
    {
        foreach ($array as $ar) {
            $this->{$functionName}($ar, $key);
        }
        return true;
    }

    /**
     * @param        $var
     * @param string $key
     *
     * @return bool
     */
    protected function haveString($var, $key = 'item')
    {
        if (!preg_match('/[a-zA-Z]/', $var)) {
            throw new InvalidArgumentException($key.' must be string and contains at least one character!');
        }

        return true;
    }

    /**
     * @param        $var
     * @param string $key
     *
     * @return bool
     */
    protected function isInt($var, $key = 'item')
    {
        if (!is_numeric($var)) {
            throw new InvalidArgumentException($key.' must be integer!');
        }

        return true;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    protected function validateValue($value)
    {
        if ($value < -255 and $value > 255) {
            throw new InvalidArgumentException('value must be between -255 and 255');
        }

        return true;
    }
}