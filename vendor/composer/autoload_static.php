<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaa233729c9e0abdc4c704a8ba1ce00e3
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SnapBi\\' => 7,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'M' => 
        array (
            'Midtrans\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SnapBi\\' => 
        array (
            0 => __DIR__ . '/..' . '/midtrans/midtrans-php/SnapBi',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Midtrans\\' => 
        array (
            0 => __DIR__ . '/..' . '/midtrans/midtrans-php/Midtrans',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaa233729c9e0abdc4c704a8ba1ce00e3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaa233729c9e0abdc4c704a8ba1ce00e3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitaa233729c9e0abdc4c704a8ba1ce00e3::$classMap;

        }, null, ClassLoader::class);
    }
}
