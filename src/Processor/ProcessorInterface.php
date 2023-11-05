<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;

interface ProcessorInterface
{
    public function process(EloquentModel $model, Config $config): void;
    public function getPriority(): int;
}
