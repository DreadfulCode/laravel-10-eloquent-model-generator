<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;
use Krlove\CodeGenerator\Model\NamespaceModel;

class NamespaceProcessor implements ProcessorInterface
{
    public function process(EloquentModel $model, Config $config): void
    {
        $namespace = $config->getNamespace();
        if (! is_string($namespace) || ! preg_match('/^[A-Za-z_][A-Za-z0-9_\\\\]*$/', $namespace)) {
            throw new \InvalidArgumentException('Invalid namespace provided');
        }
        $model->setNamespace(new NamespaceModel($namespace));
    }

    public function getPriority(): int
    {
        return 6;
    }
}
