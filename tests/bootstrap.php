<?php
/**
 * File bootstrap.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */

/**
 * PHPUnit Bootstrap
 *
 * Registers the Composer autoloader
 *
 * @package ERP
 * @subPackage Tests
 */
call_user_func(function() {
    if ( ! is_file($autoloadFile = realpath(dirname(__FILE__).'/../vendor/autoload.php'))) {
        throw new \RuntimeException('Did not find vendor/autoload.php. Did you run "composer install --dev"?');
    }

    require_once $autoloadFile;
});
