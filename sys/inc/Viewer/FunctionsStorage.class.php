<?php

/**
 * Class Fps_Viewer_FunctionsStorage
 *
 * Uses to store custom template functions & call them.
 */
class Fps_Viewer_FunctionsStorage
{

	private static $storage = array();


    /**
     * Call the custom template function
     *
     * @param $name string
     * @throws BadFunctionCallException
     */
    public static function run($name)
    {
        $name = (string)$name;
        $args = array_slice(func_get_args(), 1);

        if (!array_key_exists($name, self::$storage))
            throw new BadFunctionCallException('Template function "'
                . htmlspecialchars($name) . '" doesn\'t registered.');

        try {
            call_user_func_array(self::$storage[$name], $args);
        } catch (Exception $e) {
            throw new BadFunctionCallException($e->getMessage());
        }
    }

    /**
     * Register a custom template function by name
     *
     * @param $name string
     * @param $function function
     */
    public static function registerFunction($name, $function)
    {
        self::$storage[(string)$name] = $function;
    }


    /**
     * Remove the custom template function by name
     *
     * @param $name string
     */
    public static function unlink($name)
    {
        $name = (string)$name;
        if (array_key_exists($name, self::$storage))
            unset(self::$storage[$name]);
    }


    public static function functionExists($name)
    {
        return array_key_exists((string)$name, self::$storage);
    }
}