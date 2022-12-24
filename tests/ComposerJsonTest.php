<?php

namespace ImanGhafoori\ComposerJson\Tests;

use ImanGhafoori\ComposerJson\ComposerJson;
use PHPUnit\Framework\TestCase;

class ComposerJsonTest extends TestCase
{
    /** @test */
    public function read_autoload()
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
                'Imanghafoori\LaravelMicroscope\Tests\\' => 'tests/',
            ],
        ];

        $this->assertEquals($expected, $reader->readAutoload());

        $this->assertEquals("iman/ghafoori", $reader->readKey('name'));
    }

    /** @test */
    public function expects_real_paths()
    {
        $this->expectException(\InvalidArgumentException::class);
        ComposerJson::make(__DIR__.'/Stubs/absent');
    }

    /** @test */
    public function read_file()
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
                'hello/how' => '~5.0'
            ],
            'require-dev' => [
                'phpunit/phpunit' => '~4.0'
            ],
            'autoload' => [
                'classmap' => [
                    'database', 'tests/TestCase.php'
                ],
                'psr-4' => [
                    'App\\' => 'app/'
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Imanghafoori\\LaravelMicroscope\\Tests\\' => 'tests'
                ],
            ],
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => './a2'
                ]
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
}
