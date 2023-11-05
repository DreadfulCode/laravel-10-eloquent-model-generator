<?php

namespace Dreadfulcode\EloquentModelGenerator\EventListener;

use Illuminate\Console\Events\CommandStarting;
use Dreadfulcode\EloquentModelGenerator\TypeRegistry;

class GenerateCommandEventListener
{
    private const SUPPORTED_COMMANDS = [
        'dreadfulcode:generate:model',
        'dreadfulcode:generate:models',
    ];

    public function __construct(private TypeRegistry $typeRegistry)
    {
    }

    public function handle(CommandStarting $event): void
    {
        if (!in_array($event->command, self::SUPPORTED_COMMANDS)) {
            return;
        }

        /* @phpstan-ignore-next-line */
        $userTypes = config('eloquent_model_generator.db_types', []);
        foreach ($userTypes as $type => $value) {
            $this->typeRegistry->registerType($type, $value);
        }
    }
}
