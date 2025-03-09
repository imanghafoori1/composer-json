<?php

namespace ImanGhafoori\ComposerJson;

use ArrayAccess;
use Symfony\Component\Finder\SplFileInfo;

class Entity implements ArrayAccess
{
    /**
     * @var \Symfony\Component\Finder\SplFileInfo
     */
    private $classPath;

    /**
     * @var \ImanGhafoori\ComposerJson\ClassDefinition
     */
    private $classDefinition;

    /**
     * @var string
     */
    private $basePath;

    public static function make(SplFileInfo $classPath, ClassDefinition $definition, $base): self
    {
        $object = new self;
        $object->classPath = $classPath;
        $object->classDefinition = $definition;
        $object->basePath = $base;

        return $object;
    }

    public function getRelativePath()
    {
        return $this->classPath->getRelativePath();
    }

    public function getFileName()
    {
        return $this->classPath->getFilename();
    }

    public function getRelativePathname()
    {
        return $this->classPath->getRelativePathname();
    }

    public function getClassDefinition(): ClassDefinition
    {
        return $this->classDefinition;
    }

    public function toArray()
    {
        return [
            'relativePath' => trim(str_replace($this->basePath, '', $this->classPath->getRealPath()), '/\\'),
            'relativePathname' => $this->classPath->getRelativePathname(),
            'fileName' => $this->classPath->getFilename(),
            'currentNamespace' => $this->classDefinition->getNamespace(),
            'absFilePath' => $this->classPath->getRealPath(),
            'class' => $this->classDefinition->getEntityName(),
            'type' => $this->classDefinition->getType(),
        ];
    }

    #[\ReturnTypeWillChange]

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->toArray()[$offset];
    }

    #[\ReturnTypeWillChange]

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    #[\ReturnTypeWillChange]

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}
