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

    public function getRelativePath(): string
    {
        return trim(str_replace($this->basePath, '', $this->classPath->getRealPath()), '/\\');
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

    public function getEntityName(): string
    {
        return $this->classDefinition->getEntityName();
    }

    public function getAbsolutePath(): string
    {
        return $this->classPath->getRealPath();
    }

    public function getNamespace()
    {
        return $this->classDefinition->getNamespace();
    }

    public function getType()
    {
        return $this->classDefinition->getType();
    }

    public function toArray()
    {
        return [
            'relativePath' => $this->getRelativePath(),
            'relativePathname' => $this->classPath->getRelativePathname(),
            'fileName' => $this->classPath->getFilename(),
            'currentNamespace' => $this->getNamespace(),
            'absFilePath' => $this->classPath->getRealPath(),
            'class' => $this->classDefinition->getEntityName(),
            'type' => $this->getType(),
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
