<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit169319ad715f97c64e9d011f5595823f
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GroovyMenu\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GroovyMenu\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit169319ad715f97c64e9d011f5595823f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit169319ad715f97c64e9d011f5595823f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
