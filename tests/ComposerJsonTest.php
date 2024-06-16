<?php

namespace ImanGhafoori\ComposerJson\Tests;

use ImanGhafoori\ComposerJson\ComposerJson;
use PHPUnit\Framework\TestCase;

class ComposerJsonTest extends TestCase
{
    public function test_getRelativePathFromNamespace()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs/shortcut_namespace');
        $relativePath = $reader->getRelativePathFromNamespace('App\\App\\Models\\Tests\\User');
        $ns = DIRECTORY_SEPARATOR;
        $this->assertEquals('app'.$ns.'App'.$ns.'Models'.$ns.'Tests'.$ns.'User', $relativePath);

        $relativePath = $reader->getRelativePathFromNamespace('Models\\Koo\\User');
        $this->assertEquals('app'.$ns.'Models'.$ns.'Koo'.$ns.'User', $relativePath);

        $relativePath = $reader->getRelativePathFromNamespace('Tests\\User');
        $this->assertEquals('tests'.$ns.'User', $relativePath);

        $reader = ComposerJson::make(__DIR__.'/Stubs');
        $relativePath = $reader->getRelativePathFromNamespace('App2\\Models\\Tests\\User');
        $this->assertEquals('a2'.$ns.'app2'.$ns.'Models'.$ns.'Tests'.$ns.'User', $relativePath);

        $reader = ComposerJson::make(__DIR__.'/Stubs');
        $relativePath = $reader->getRelativePathFromNamespace('Map\\Tests\\User');
        $this->assertEquals('m1'.$ns.'Tests'.$ns.'User', $relativePath);

        $reader = ComposerJson::make(__DIR__.'/Stubs');
        $relativePath = $reader->getRelativePathFromNamespace('Dapp\\Tests\\User');
        $this->assertEquals('dapp'.$ns.'Tests'.$ns.'User', $relativePath);
    }

    public function test_getNamespacedClassFromPath()
    {
        $ds = function ($subject) {
            return str_replace('/', DIRECTORY_SEPARATOR, $subject);
        };

        $reader = ComposerJson::make($p = $ds(__DIR__.'/Stubs/shortcut_namespace'));
        $namespace = $reader->getNamespacedClassFromPath($ds($p.'/app/G1/G2.php'));
        $this->assertEquals('App\G1\G2', $namespace);

        $namespace = $reader->getNamespacedClassFromPath($ds($p.'/app/Models/G1/G2.php'));
        $this->assertEquals('Models\G1\G2', $namespace);

        $reader = ComposerJson::make($p = $ds(__DIR__.'/Stubs'));
        $namespace = $reader->getNamespacedClassFromPath($ds($p.'/m1/G1/G2.php'));
        $this->assertEquals('Map\G1\G2', $namespace);

        $namespace = $reader->getNamespacedClassFromPath($ds($p.'/m2/G1/G2.php'));
        $this->assertEquals('Map\G1\G2', $namespace);

        $namespace = $reader->getNamespacedClassFromPath($ds($p.'/dapp/dapp/G1/G2.php'));
        $this->assertEquals('Dapp\dapp\G1\G2', $namespace);

        $namespace = $reader->getNamespacedClassFromPath($ds($p.'/a2/ref/ref/G2.php'));
        $this->assertEquals('G2\ref\G2', $namespace);
    }

    public function test_read_autoload_psr4_purged()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs/shortcut_namespace');
        $this->assertEquals([
            '/' => [
                'App\\' => 'app/',
                'Tests\\' => 'tests/',
            ],
        ], $reader->readAutoload(true));

        $this->assertEquals(
            $reader->readAutoload(true),
            $reader->readAutoloadPsr4(true)

        );
    }

    public function test_read_autoload_psr4()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs');

        $expected = [
            'a2' => [
                'G2\\' => 'a2/ref/',
                'App2\\' => 'a2/app2/',
                'Imanghafoori\LaravelMicroscope\Tests\\' => 'a2/tests/',
            ],
            '/' => [
                'App\\' => 'app/',
                'Imanghafoori\\LaravelMicroscope\\Tests\\' => 'tests/',
                'Dapp\\' => 'dapp/', // <==== is normalized
                'Map\\' => ['m1/', 'm2/'],
            ],
        ];

        $this->assertEquals($expected, $reader->readAutoload());
    }

    public function test_getClasslists()
    {
        $d = DIRECTORY_SEPARATOR;
        $reader = ComposerJson::make($p = __DIR__.$d.'Stubs'.$d.'a3');
        $classList = $reader->getClasslists(null, null);
        $expected = [
            "/" => [
                "App\\" => [
                    0 => [
                        "relativePath" => "",
                        "relativePathname" => "a.php",
                        "fileName" => "a.php",
                        "currentNamespace" => "App",
                        "absFilePath" => "{$p}{$d}app{$d}a.php",
                        "class" => "a",
                        "type" => T_CLASS,
                    ],
                ],
                "Database\\Seeders\\" => [],
            ],
        ];

        $this->assertEquals($expected, $classList);
    }

    public function test_get_namespace_from_relative_path()
    {
        $reader = ComposerJson::make($p = __DIR__.'/Stubs/a3');
        $result = $reader->getNamespacedClassFromPath('app/Hello.php');
        $this->assertEquals('App\\Hello', $result);

        $result = $reader->getNamespacedClassFromPath('app/appollo.php');
        $this->assertEquals('App\\appollo', $result);

        $result = $reader->getNamespacedClassFromPath('app/Models/Hello.php');
        $this->assertEquals('Models\\Hello', $result);

        $result = $reader->getNamespacedClassFromPath('app/appollo.php');
        $this->assertEquals('App\\appollo', $result);

        $result = $reader->getNamespacedClassFromPath('app/d/appollo.php');
        $this->assertEquals('Test\\appollo', $result);
    }

    public function test_readKey()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs');
        $this->assertEquals('iman/ghafoori', $reader->readKey('name'));
        $this->assertEquals(['hello/how' => '~5.0'], $reader->readKey('require'));
        $this->assertEquals('~5.0', $reader->readKey('require.hello/how'));
        $this->assertEquals(['framework', 'package'], $reader->readKey('keywords'));
    }

    public function test_read_autoload_files()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs');

        $expected = [
            'a2' => [
                'autoload' => ['src/MyLibrary/functions.php'],
                'autoload-dev' => [],
            ],
            '/' => [
                'autoload' => [
                    'src/MyLib/functions.php',
                    'src/MyLib/functions2.php',
                ],
                'autoload-dev' => [
                    'src/MyLib/functions.php',
                    'src/MyLib/functions2.php',
                ],
            ],
        ];

        $this->assertEquals($expected, $reader->readAutoloadFiles());
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            __DIR__.str_replace('|', $ds, '|Stubs|a2|src|MyLibrary|functions.php'),
            __DIR__.str_replace('|', $ds, '|Stubs|src|MyLib|functions.php'),
            __DIR__.str_replace('|', $ds, '|Stubs|src|MyLib|functions2.php'),
            __DIR__.str_replace('|', $ds, '|Stubs|src|MyLib|functions.php'),
            __DIR__.str_replace('|', $ds, '|Stubs|src|MyLib|functions2.php'),
        ], $reader->autoloadedFilesList(__DIR__.'/Stubs'));
    }

    public function test_expects_real_paths()
    {
        $this->expectException(\InvalidArgumentException::class);
        ComposerJson::make(__DIR__.'/Stubs/absent');
    }

    public function test_expects_composer_json_file_to_exist()
    {
        $this->expectException(\InvalidArgumentException::class);
        ComposerJson::make(__DIR__.'/Stubs/empty');
    }

    public function test_readComposerFileData()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs');
        $actual = $reader->readComposerFileData();
        $expected = [
            'name' => 'iman/ghafoori',
            'description' => 'Some description.',
            'keywords' => ['framework', 'package'],
            'license' => 'MIT',
            'type' => 'project',
            'require' => [
                'hello/how' => '~5.0',
            ],
            'require-dev' => [
                'phpunit/phpunit' => '~4.0',
            ],
            'autoload' => [
                'classmap' => [
                    'database', 'tests/TestCase.php',
                ],
                'psr-4' => [
                    'App\\' => 'app/',
                    'Dapp\\' => 'dapp',
                    'Map\\' => ['m1/', 'm2/'],
                ],
                'files' => [
                    'src/MyLib/functions.php',
                    'src/MyLib/functions2.php',
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Imanghafoori\\LaravelMicroscope\\Tests\\' => 'tests',
                ],
                'files' => [
                    'src/MyLib/functions.php',
                    'src/MyLib/functions2.php',
                ],
            ],
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => './a2',
                ],
            ],
            'extra' => [
                'some_key' => [
                    'dont-discover' => ['*'],
                ],
            ],
            'minimum-stability' => 'dev',
        ];

        $this->assertEquals($expected, $actual);
    }
    public function test_get_classmap()
    {
        $reader = ComposerJson::make(__DIR__.'/Stubs/a3');
        $result = $reader->readAutoloadClassMap();
        $this->assertEquals([
            '/' => [
                0 => 'asc',
            ],
        ], $result);
    }
}
