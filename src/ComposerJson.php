<?php

namespace ImanGhafoori\ComposerJson;

use InvalidArgumentException;
use Symfony\Component\Finder\Finder;

class ComposerJson
{
    private $result = [];

    public static $buffer = 800;

    /**
     * Used for testing purposes.
     */
    public $basePath = null;

    public $ignoredNamespaces = [];

    public static function make($folderPath, $ignoredNamespaces = [])
    {
        $folderPath = rtrim($folderPath, '/\\ ');
        $folderPath = str_replace('/\\', DIRECTORY_SEPARATOR, $folderPath);
        if (file_exists($folderPath.DIRECTORY_SEPARATOR.'composer.json')) {
            return new static($folderPath, $ignoredNamespaces);
        } else {
            throw new InvalidArgumentException('The path ('.$folderPath.') does not contain a composer.json file.');
        }
    }

    private function __construct($basePath, $ignoredNamespaces)
    {
        $this->basePath = $basePath;
        $this->ignoredNamespaces = $ignoredNamespaces;
    }

    public function readAutoloadClassMap()
    {
        $result = [];

        foreach ($this->collectLocalRepos() as $relativePath) {
            $result[$relativePath] = $this->readKey('autoload.classmap', $relativePath) + $this->readKey('autoload-dev.classmap', $relativePath);
        }

        $result['/'] = $this->readKey('autoload.classmap') + $this->readKey('autoload-dev.classmap');

        return $result;
    }

    public function readAutoloadPsr4($purgeShortcuts = false)
    {
        return $this->readAutoload($purgeShortcuts);
    }

    public function readAutoload($purgeShortcuts = false)
    {
        $result = [];

        foreach ($this->collectLocalRepos() as $relativePath) {
            // We avoid autoload-dev for repositories.
            $result[$relativePath] = $this->readKey('autoload.psr-4', $relativePath) + $this->readKey('autoload-dev.psr-4', $relativePath);
        }

        // add the root composer.json
        $result['/'] = $this->readKey('autoload.psr-4') + $this->readKey('autoload-dev.psr-4');

        $results = $purgeShortcuts ? self::purgeAutoloadShortcuts($result) : $result;

        return self::removedIgnored($results, $this->ignoredNamespaces);
    }

    public function readAutoloadFiles()
    {
        $result = [];
        $repos = $this->collectLocalRepos();
        $repos[] = '/';

        foreach ($repos as $relativePath) {
            $result[$relativePath]['autoload'] = $this->readKey('autoload.files', $relativePath);
            $result[$relativePath]['autoload-dev'] = $this->readKey('autoload-dev.files', $relativePath);
        }

        return $result;
    }

    public function collectLocalRepos()
    {
        $composers = [];

        foreach ($this->readKey('repositories') as $repo) {
            if (($repo['type'] ?? '') !== 'path') {
                continue;
            }

            $dirPath = ltrim($repo['url'], '.\\/');

            $path = $this->basePath.DIRECTORY_SEPARATOR.$dirPath.DIRECTORY_SEPARATOR.'composer.json';
            // sometimes php can not detect relative paths, so we use the absolute path here.
            if (file_exists($path)) {
                $composers[$dirPath] = $dirPath;
            }
        }

        return $composers;
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

    public function getClasslists(?\Closure $filter, ?\Closure $pathFilter)
    {
        $classLists = [];

        foreach ($this->readAutoload(true) as $composerFilePath => $autoload) {
            foreach ($autoload as $namespace => $psr4Path) {
                $classLists[$composerFilePath][$namespace] = $this->getClassesWithin($psr4Path, $filter, $pathFilter);
            }
        }

        return $classLists;
    }

    public function getClassesWithin($composerPath, \Closure $filterClass, ?\Closure $pathFilter = null)
    {
        $results = [];
        foreach ($this->getAllPhpFiles($composerPath) as $classFilePath) {
            $absFilePath = $classFilePath->getRealPath();

            if ($pathFilter && ! $pathFilter($absFilePath, $classFilePath->getFilename())) {
                continue;
            }

            // Exclude blade files
            if (substr_count($classFilePath->getFilename(), '.') !== 1) {
                continue;
            }

            [$currentNamespace, $class, $parent, $type] = $this->readClass($absFilePath);

            // Skip if there is no class/trait/interface definition found.
            // For example a route file or a config file.
            if (! $class) {
                continue;
            }

            if ($filterClass($classFilePath, $currentNamespace, $class, $parent) === false) {
                continue;
            }

            $results[] = [
                'relativePath' => $classFilePath->getRelativePath(),
                'relativePathname' => $classFilePath->getRelativePathname(),
                'fileName' => $classFilePath->getFilename(),
                'currentNamespace' => $currentNamespace,
                'absFilePath' => $absFilePath,
                'class' => $class,
                'type' => $type,
            ];
        }

        return $results;
    }

    /**
     * Checks all the psr-4 loaded classes to have correct namespace.
     *
     * @param  array  $autoloads
     * @return array
     */
    public static function purgeAutoloadShortcuts($autoloads)
    {
        foreach ($autoloads as $composerPath => $psr4Mappings) {
            foreach ($psr4Mappings as $namespace1 => $psr4Path1) {
                foreach ($psr4Mappings as $psr4Path2) {
                    if (strlen($psr4Path1) > strlen($psr4Path2) && self::startsWith($psr4Path1, $psr4Path2)) {
                        unset($autoloads[$composerPath][$namespace1]);
                    }
                }
            }
        }

        return $autoloads;
    }

    public function getErrorsLists(array $classLists, ?\Closure $onCheck)
    {
        $errorsLists = [];
        $autoloads = $this->readAutoload();
        foreach ($classLists as $composerPath => $classList) {
            $errorsLists[$composerPath] = NamespaceCalculator::findPsr4Errors($this->basePath, $autoloads[$composerPath], $classList, $onCheck);
        }

        return $errorsLists;
    }

    public function getRelativePathFromNamespace($namespace)
    {
        $autoload = $this->readAutoload();
        [$namespaces, $paths] = self::getSortedAutoload($autoload);
        [$namespaces, $paths] = self::flatten($paths, $namespaces);
        $path = '';
        foreach ($namespaces as $i => $ns) {
            if (0 === strpos($namespace, $ns)) {
                $path = \substr_replace($namespace, $paths[$i], 0, strlen($ns));

                break;
            }
        }

        return \str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    public function getNamespacedClassFromPath($absPath)
    {
        $psr4Mappings = $this->readAutoload();
        // Converts "absolute path" to "relative path":
        $relativePath = trim(str_replace($this->basePath, '', $absPath), '/\\');
        $className = str_replace('.php', '', basename($absPath));

        foreach ($psr4Mappings as $composerPath => $psr4Mapping) {
            if (0 === strpos($relativePath, $composerPath)) {
                $correctNamespaces = NamespaceCalculator::getCorrectNamespaces($psr4Mapping, $relativePath);

                return NamespaceCalculator::findShortest($correctNamespaces).'\\'.$className;
            }
        }

        $correctNamespaces = NamespaceCalculator::getCorrectNamespaces($psr4Mappings['/'], $relativePath);

        return NamespaceCalculator::findShortest($correctNamespaces).'\\'.$className;
    }

    /**
     * get all ".php" files in directory by giving a path.
     *
     * @param  string  $path  Directory path
     * @return \Symfony\Component\Finder\Finder
     */
    public function getAllPhpFiles($path, $basePath = '')
    {
        if ($basePath === '') {
            $basePath = $this->basePath;
        }

        $basePath = rtrim($basePath, '/\\');
        $path = ltrim($path, '/\\');
        $path = $basePath.DIRECTORY_SEPARATOR.$path;

        try {
            return Finder::create()->files()->name('*.php')->in($path);
        } catch (Exception $e) {
            return [];
        }
    }

    private function readClass($absFilePath)
    {
        $buffer = self::$buffer;
        do {
            [
                $currentNamespace,
                $class,
                $type,
                $parent,
            ] = GetClassProperties::fromFilePath($absFilePath, $buffer);
            $buffer = $buffer + 1000;
        } while ($currentNamespace && ! $class && $buffer < 6000);

        return [$currentNamespace, $class, $parent, $type];
    }

    private static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    private static function getSortedAutoload($autoloads)
    {
        $namespaces = [];
        $paths = [];

        foreach ($autoloads as $autoload) {
            $namespaces = array_merge($namespaces, array_keys($autoload));
            $paths = array_merge($paths, array_values($autoload));
        }

        return [$namespaces, $paths];
    }

    private static function flatten($paths, $namespaces)
    {
        $_namespaces = [];
        $_paths = [];
        $counter = 0;
        foreach ($paths as $k => $_p) {
            foreach ((array) $_p as $p) {
                $counter++;
                $_namespaces[$counter] = $namespaces[$k];
                $_paths[$counter] = $p;
            }
        }

        return [$_namespaces, $_paths];
    }

    private function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    private function data_get($target, $key, $default = null)
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

    private static function removedIgnored($mapping, $ignored = [])
    {
        $result = [];

        foreach ($mapping as $i => $map) {
            foreach ($map as $namespace => $path) {
                if (! in_array($namespace, $ignored)) {
                    $result[$i][$namespace] = $path;
                }
            }
        }

        return $result;
    }
}
