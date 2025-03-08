<?php

namespace ImanGhafoori\ComposerJson;

use JetBrains\PhpStorm\ExpectedValues;

class ClassDefinition
{
    private $type = null;

    private $interfaces = [];

    private $parent = null;

    private $namespace = null;

    private $entityName = null;

    public static function make(string $name, int $type, string $interfaces, $namespace = null, string $parent = null): self
    {
        $self = new self;
        $self->entityName = $name;
        $self->type = $type;
        if ($interfaces) {
            $self->interfaces = explode('|', $interfaces);
        }
        $self->namespace = ltrim($namespace, '\\');
        $self->parent = $parent;

        return $self;
    }

    #[ExpectedValues(values: ['interface', 'class', 'enum', 'trait', null])]
    public function getType(): ?string
    {
        if ($this->type === T_INTERFACE) {
            return 'interface';
        } elseif ($this->type === T_CLASS) {
            return 'class';
        } elseif ($this->type === T_ENUM) {
            return 'enum';
        } elseif ($this->type === T_TRAIT) {
            return 'trait';
        } else {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getEntityName()
    {
        return $this->entityName;
    }
}
