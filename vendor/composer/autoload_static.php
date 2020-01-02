<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfaf4b4b9b0441202220deb2747f3068d
{
    public static $prefixLengthsPsr4 = [
        'S' =>
            [
                'Sms77\\Api\\' => 10,
            ],
    ];

    public static $prefixDirsPsr4 = [
        'Sms77\\Api\\' =>
            [
                0 => __DIR__ . '/..' . '/sms77/api/src',
            ],
    ];

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfaf4b4b9b0441202220deb2747f3068d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfaf4b4b9b0441202220deb2747f3068d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}