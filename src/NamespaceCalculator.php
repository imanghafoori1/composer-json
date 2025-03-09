<?php

namespace ImanGhafoori\ComposerJson;

use Closure;
use ImanGhafoori\ComposerJson\NamespaceErrors\FilenameError;
use ImanGhafoori\ComposerJson\NamespaceErrors\NamespaceError;

class NamespaceCalculator
{
    public static function calculateCorrectNamespace($relativeClassPath, $composerPath, $rootNamespace)
    {
        $classPath = \explode(DIRECTORY_SEPARATOR, $relativeClassPath);
        // Removes the filename
        array_pop($classPath);

        $classPath = \implode('\\', $classPath);

        // Ensure backslashes in All Operating Systems.
        $composerPath = \str_replace('/', '\\', $composerPath);

        // replace composer base_path with composer namespace
        /**
         *  "psr-4": {
         *      "App\\": "app/"
         *  }.
         */
        return self::replaceFirst(
            \trim($composerPath, '\\'),
            \trim($rootNamespace, '\\/'),
            $classPath
        );
    }

    public static function findPsr4Errors($psr4Mapping, $classLists, ?Closure $onCheck)
    {
        $errors = [];

        foreach ($classLists as $list) {
            foreach ($list as $entity) {
                /**
                 * @var $entity \ImanGhafoori\ComposerJson\Entity
                 */
                $onCheck && $onCheck($entity);
                $error = self::checkNamespace($entity->getRelativePath(), $psr4Mapping, $entity);
                $error && ($errors[] = $error);
            }
        }

        return $errors;
    }

    public static function checkNamespace($relativePath, $psr4Mapping, Entity $class)
    {
        $correctNamespaces = self::getCorrectNamespaces($psr4Mapping, $relativePath);

        if (! in_array($class->getClassDefinition()->getNamespace(), $correctNamespaces)) {
            return new NamespaceError($correctNamespaces, $class);
        } elseif (($class->getEntityName().'.php') !== $class->getFileName()) {
            return new FilenameError($class);
        }
    }

    public static function getNamespaceFromFullClass($class)
    {
        $segments = explode('\\', $class);
        array_pop($segments); // removes the last part

        return trim(implode('\\', $segments), '\\');
    }

    public static function haveSameNamespace($class1, $class2)
    {
        return self::getNamespaceFromFullClass($class1) === self::getNamespaceFromFullClass($class2);
    }

    public static function findShortest($correctNamespaces)
    {
        // finds the shortest namespace
        return array_reduce($correctNamespaces, function ($a, $b) {
            return ($a === null || strlen($a) >= strlen($b)) ? $b : $a;
        });
    }

    /**
     * @param $psr4Mapping
     * @param $relativePath
     *
     * @return string[]
     */
    public static function getCorrectNamespaces($psr4Mapping, $relativePath)
    {
        $correctNamespaces = [];
        $relativePath = str_replace(['\\', '.php'], ['/', ''], $relativePath);
        foreach ($psr4Mapping as $namespacePrefix => $paths) {
            foreach ((array) $paths as $path) {
                if (strpos($relativePath, $path) === 0) {
                    $correctNamespace = substr_replace($relativePath, $namespacePrefix, 0, strlen($path));
                    $correctNamespace = str_replace('/', '\\', $correctNamespace);
                    $correctNamespaces[] = self::getNamespaceFromFullClass($correctNamespace);
                }
            }
        }

        return $correctNamespaces;
    }

    private static function replaceFirst($search, $replace, $subject)
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    public static function getNamespaceFromPath($absFilePath, $basePath, $psr4Path, $psr4Namespace): string
    {
        $className = basename(str_replace(['.php', '\\'], ['', '/'], $absFilePath));
        $relativePath = str_replace($basePath, '', $absFilePath);
        $namespace = self::calculateCorrectNamespace($relativePath, $psr4Path, $psr4Namespace);

        return $namespace ? ($namespace.'\\'.$className) : $className;
    }
}
