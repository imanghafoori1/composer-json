<?php

namespace ImanGhafoori\ComposerJson;

use InvalidArgumentException;

class ComposerJson
{
    private $result = [];

    /**
     * Used for testing purposes.
     */
    public $basePath = null;

    public static function make($folderPath)
    {
        $folderPath = rtrim($folderPath, '/\\ ');
        $folderPath = str_replace('/\\', DIRECTORY_SEPARATOR, $folderPath);
        if (file_exists($folderPath.DIRECTORY_SEPARATOR.'composer.json')) {
            return new static($folderPath);
        } else {
            throw new InvalidArgumentException('The path ('.$folderPath.') does not contain a composer.json file.');
        }
    }

    private function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function readAutoload()
    {
        $result = [];

        foreach ($this->collectLocalRepos() as $relativePath) {
            // We avoid autoload-dev for repositories.
            $result[$relativePath] = $this->readKey('autoload.psr-4', $relativePath) + $this->readKey('autoload-dev.psr-4', $relativePath);
        }

        // add the root composer.json
        $result['/'] = $this->readKey('autoload.psr-4') + $this->readKey('autoload-dev.psr-4');

        return $result;
    }

    public function collectLocalRepos()
    {
        $composers = [];

        foreach ($this->readKey('repositories') as $repo) {
            if (! isset($repo['type']) || $repo['type'] !== 'path') {
                continue;
            }

            $dirPath = \trim(\trim($repo['url'], '.'), '/\\');
            $path = $this->basePath.DIRECTORY_SEPARATOR.$dirPath.DIRECTORY_SEPARATOR.'composer.json';
            // sometimes php can not detect relative paths, so we use the absolute path here.
            if (file_exists($path)) {
                $composers[$dirPath] = $dirPath;
            }
        }

        return $composers;
    }

    private function normalizePaths($value, $path)
    {
        $path && $path = $this->finish($path, '/');
        foreach ($value as $namespace => $_path) {
            if (is_array($_path)) {
                foreach ($_path as $i => $p) {
                    $value[$namespace][$i] = str_replace('//', '/', $path.$this->finish($p, '/'));
                }
            } else {
                $value[$namespace] = str_replace('//', '/', $path.$this->finish($_path, '/'));
            }
        }

        return $value;
    }

    public function readKey($key, $composerPath = '')
    {
        $composer = $this->readComposerFileData($composerPath);

        $value = $this->data_get($composer, $key, []);

        if (\in_array($key, ['autoload.psr-4', 'autoload-dev.psr-4'])) {
            $value = $this->normalizePaths($value, $composerPath);
        }

        return $value;
    }

    /**
     * @param  string  $path
     * @return array
     */
    public function readComposerFileData($path = '')
    {
        $absPath = $this->basePath.DIRECTORY_SEPARATOR.$path;

        $absPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absPath);

        // ensure it does not end with slash
        $absPath = rtrim($absPath, DIRECTORY_SEPARATOR);

        if (! isset($this->result[$absPath])) {
            $this->result[$absPath] = \json_decode(\file_get_contents($absPath.DIRECTORY_SEPARATOR.'composer.json'), true);
        }

        return $this->result[$absPath];
    }

    public function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    public function data_get($target, $key, $default = null)
    {
        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $i => $segment) {
            unset($key[$i]);

            if (! array_key_exists($segment, $target)) {
                return $default;
            }

            $target = $target[$segment];
        }

        return $target;
    }
}
