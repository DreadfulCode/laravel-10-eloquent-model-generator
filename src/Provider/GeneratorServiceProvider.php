<?php

namespace Dreadfulcode\EloquentModelGenerator\Provider;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Dreadfulcode\EloquentModelGenerator\Command\GenerateModelCommand;
use Dreadfulcode\EloquentModelGenerator\Command\GenerateModelsCommand;
use Dreadfulcode\EloquentModelGenerator\EventListener\GenerateCommandEventListener;
use Dreadfulcode\EloquentModelGenerator\Generator;
use Dreadfulcode\EloquentModelGenerator\Processor\CustomPrimaryKeyProcessor;
use Dreadfulcode\EloquentModelGenerator\Processor\CustomPropertyProcessor;
use Dreadfulcode\EloquentModelGenerator\Processor\FieldProcessor;
use Dreadfulcode\EloquentModelGenerator\Processor\NamespaceProcessor;
use Dreadfulcode\EloquentModelGenerator\Processor\RelationProcessor;
use Dreadfulcode\EloquentModelGenerator\Processor\TableNameProcessor;
use Dreadfulcode\EloquentModelGenerator\TypeRegistry;

class GeneratorServiceProvider extends ServiceProvider
{
    public const PROCESSOR_TAG = 'eloquent_model_generator.processor';

    public function register()
    {
        $this->commands([
            GenerateModelCommand::class,
            GenerateModelsCommand::class,
        ]);

        $this->app->singleton(TypeRegistry::class);
        $this->app->singleton(GenerateCommandEventListener::class);

        $this->app->tag([
            FieldProcessor::class,
            NamespaceProcessor::class,
            RelationProcessor::class,
            CustomPropertyProcessor::class,
            TableNameProcessor::class,
            CustomPrimaryKeyProcessor::class,
        ], self::PROCESSOR_TAG);

        $this->app->bind(Generator::class, function ($app) {
            return new Generator($app->tagged(self::PROCESSOR_TAG));
        });
    }

    public function boot()
    {
        Event::listen(CommandStarting::class, [GenerateCommandEventListener::class, 'handle']);
    }
}
