<?php

namespace Evand\Recommenderir;

trait Helpers
{
    protected function json_decode_recommender($string)
    {
        $string = $this->str_replace_last(']', '}', $string);
        $json   = $this->str_replace_first('[', '{', $string);

        return json_decode($json);
    }

    protected function str_replace_last($search, $replace, $subject)
    {

        if (($pos = strrpos($subject, $search)) !== false) {
            $search_length = strlen($search);
            $subject       = substr_replace($subject, $replace, $pos, $search_length);
        }
        return $subject;
    }

    protected function str_replace_first($search, $replace, $subject)
    {
        $search = '/' . preg_quote($search, '/') . '/';
        return preg_replace($search, $replace, $subject, 1);
    }
}