<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitef17410c5350abc31eed98708c174fc4
{
    public static $files = array (
        '437117a15e07e1ce868ad3872a22cd39' => __DIR__ . '/../..' . '/System/Init.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'System\\' => 7,
            'Symfony\\Component\\Dotenv\\' => 25,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'System\\' => 
        array (
            0 => __DIR__ . '/../..' . '/System',
        ),
        'Symfony\\Component\\Dotenv\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/dotenv',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App',
        ),
    );

    public static $classMap = array (
        'App\\Controller\\AboutController' => __DIR__ . '/../..' . '/App/Controller/AboutController.php',
        'App\\Controller\\UserController' => __DIR__ . '/../..' . '/App/Controller/UserController.php',
        'App\\Model\\Contact' => __DIR__ . '/../..' . '/App/Model/Contact.php',
        'App\\Model\\User' => __DIR__ . '/../..' . '/App/Model/User.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Symfony\\Component\\Dotenv\\Command\\DebugCommand' => __DIR__ . '/..' . '/symfony/dotenv/Command/DebugCommand.php',
        'Symfony\\Component\\Dotenv\\Command\\DotenvDumpCommand' => __DIR__ . '/..' . '/symfony/dotenv/Command/DotenvDumpCommand.php',
        'Symfony\\Component\\Dotenv\\Dotenv' => __DIR__ . '/..' . '/symfony/dotenv/Dotenv.php',
        'Symfony\\Component\\Dotenv\\Exception\\ExceptionInterface' => __DIR__ . '/..' . '/symfony/dotenv/Exception/ExceptionInterface.php',
        'Symfony\\Component\\Dotenv\\Exception\\FormatException' => __DIR__ . '/..' . '/symfony/dotenv/Exception/FormatException.php',
        'Symfony\\Component\\Dotenv\\Exception\\FormatExceptionContext' => __DIR__ . '/..' . '/symfony/dotenv/Exception/FormatExceptionContext.php',
        'Symfony\\Component\\Dotenv\\Exception\\PathException' => __DIR__ . '/..' . '/symfony/dotenv/Exception/PathException.php',
        'System\\App\\Controller' => __DIR__ . '/../..' . '/System/App/Controller.php',
        'System\\App\\Data' => __DIR__ . '/../..' . '/System/App/Data.php',
        'System\\App\\Model' => __DIR__ . '/../..' . '/System/App/Model.php',
        'System\\App\\Route' => __DIR__ . '/../..' . '/System/App/Route.php',
        'System\\App\\Session' => __DIR__ . '/../..' . '/System/App/Session.php',
        'System\\App\\Session\\DBBased' => __DIR__ . '/../..' . '/System/App/Session/DBBased.php',
        'System\\App\\Tenant' => __DIR__ . '/../..' . '/System/App/Tenant.php',
        'System\\App\\Tenant\\Base' => __DIR__ . '/../..' . '/System/App/Tenant/Base.php',
        'System\\App\\Trait\\UDFModel' => __DIR__ . '/../..' . '/System/App/Trait/UDFModel.php',
        'System\\Config\\DB' => __DIR__ . '/../..' . '/System/Config/DB.php',
        'System\\Domain' => __DIR__ . '/../..' . '/System/Domain.php',
        'System\\Init' => __DIR__ . '/../..' . '/System/Init.php',
        'System\\Preload\\Precocious' => __DIR__ . '/../..' . '/System/Preload/Precocious.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitef17410c5350abc31eed98708c174fc4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitef17410c5350abc31eed98708c174fc4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitef17410c5350abc31eed98708c174fc4::$classMap;

        }, null, ClassLoader::class);
    }
}
