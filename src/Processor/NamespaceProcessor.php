<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Krlove\CodeGenerator\Model\NamespaceModel;
use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;

class NamespaceProcessor implements ProcessorInterface
{
    public function process(EloquentModel $model, Config $config): void
    {
        $model->setNamespace(new NamespaceModel($config->getNamespace()));
    }

    public function getPriority(): int
    {
        return 6;
    }
}
