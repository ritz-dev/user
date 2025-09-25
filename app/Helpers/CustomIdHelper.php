<?php

if (!function_exists('generateCustomId')) {
    function generateCustomId(int $index): string
    {
        $indexStr = strval($index);
        $base = '1000000000000000000000000000000000';
        return substr($base, 0, 36 - strlen($indexStr)) . $indexStr;
    }
}