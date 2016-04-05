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
     * @param array $array
     * @param       $functionName
     *
     * @return bool
     */
    protected function validateArray(array $array, $functionName)
    {
        foreach ($array as $ar) {
            $this->{$functionName}($ar);
        }
        return true;
    }

    /**
     * @param $var
     *
     * @return bool
     */
    protected function haveString($var)
    {
        if (!preg_match('/[a-zA-Z]/', $var)) {
            throw new InvalidArgumentException(key($var).' must be string and contains at least one character!');
        }

        return true;
    }

    /**
     * @param $var
     *
     * @return bool
     */
    protected function isInt($var)
    {
        if (!preg_match('/[0-9]/', $var)) {
            throw new InvalidArgumentException(key($var).' must be integer!');
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