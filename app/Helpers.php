<?php

if (!function_exists('stack_path')) {
    function stack_path($path = '')
    {
        return base_path($path);
    }
}

if (!function_exists('stack_project_path')) {
    function stack_project_path($path = '')
    {
        return getcwd() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('stack_config_path')) {
    function stack_config_path($path = '')
    {
        return stack_project_path('.stack' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (!function_exists('stack_project_name')) {
    function stack_project_name()
    {
        return basename(stack_project_path());
    }
}
