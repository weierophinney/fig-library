<?php
/**
 * Setup autoloading
 */
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');

    if (!preg_match('#^((Zend|Fig)(Test)?|PHPUnit)(\\\\|_)#', $class)) {
        return false;
    }

    $segments = preg_split('#[\\\\_]#', $class); // preg_split('#\\\\|_#', $class);//
    $ns       = array_shift($segments);

    switch ($ns) {
        case 'Fig':
        case 'Zend':
            $file = dirname(__DIR__) . "/library/$ns/";
            break;
        case 'ZendTest':
            $file = __DIR__ . '/ZendTest/';
            break;
        default:
            $file = false;
            break;
    }

    if ($file) {
        $file .= implode('/', $segments) . '.php';
        if (file_exists($file)) {
            return include_once $file;
        }
    }

    $segments = explode('_', $class);
    $ns       = array_shift($segments);

    switch ($ns) {
        case 'PHPUnit':
            return include_once str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        case 'Fig':
        case 'Zend':
            $file = dirname(__DIR__) . "/library/$ns/";
            break;
        default:
            return false;
    }
    $file .= implode('/', $segments) . '.php';
    if (file_exists($file)) {
        return include_once $file;
    }

    return false;
}, true, true);
