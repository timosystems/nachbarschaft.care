<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7b1b780aeda50ee09526fb254f74c462
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        'bdf151f62a69e3ca51f07e0bd032de74' => __DIR__ . '/..' . '/lincanbin/php-pdo-mysql-class/src/PDO.class.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twig\\' => 5,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Ctype\\' => 23,
            'Securimage\\' => 11,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twig\\' => 
        array (
            0 => __DIR__ . '/..' . '/twig/twig/src',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Securimage\\' => 
        array (
            0 => __DIR__ . '/..' . '/dapphp/securimage',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Securimage' => __DIR__ . '/..' . '/dapphp/securimage/securimage.php',
        'Securimage_Color' => __DIR__ . '/..' . '/dapphp/securimage/securimage.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7b1b780aeda50ee09526fb254f74c462::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7b1b780aeda50ee09526fb254f74c462::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7b1b780aeda50ee09526fb254f74c462::$classMap;

        }, null, ClassLoader::class);
    }
}
