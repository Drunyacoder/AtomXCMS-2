<?php

/**
 * Class AtmCustomTemplateFunctions
 *
 * Uses to define a custom template functions.
 */
class AtmCustomTemplateFunctions
{
    public static function get()
    {
        $functions = array();


        /**
         * Example.
         * It function will be available in a template files as "someFunctionName".
         * You can use any kind & quantity of the params. In this example
         * we used (string)$someParam, but it could be int, array or even object.
         *
         * Usage example: {{ someFunctionName('string') }}.
         *
         *
         * @param $someParam string
         *
         * $functions['someFunctionName'] = function($someParam)
         * {
         *    // Some code here ...
         *    return $someParam;
         * };
         */


        return $functions;
    }
}