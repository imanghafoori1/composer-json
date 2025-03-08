<?php

namespace ImanGhafoori\ComposerJson\NamespaceErrors;

use ImanGhafoori\ComposerJson\Entity;
use JetBrains\PhpStorm\ExpectedValues;

class FilenameError
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var \ImanGhafoori\ComposerJson\Entity
     */
    public $entity;

    public function __construct($filename, Entity $entity)
    {
        $this->filename = $filename;
        $this->entity = $entity;
    }

    #[ExpectedValues(['filename', 'namespace'])]
    public function errorType()
    {
        return 'filename';
    }
}
