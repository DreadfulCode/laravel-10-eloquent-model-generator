<?php

namespace Dreadfulcode\EloquentModelGenerator\Command;

use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Exception\GeneratorException;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Input\InputOption;

trait GenerateCommandTrait
{
    protected function createConfig(): Config
    {
        $namespace = $this->option('namespace');
        if ($namespace !== null && ! preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $namespace)) {
            throw new GeneratorException(sprintf('Invalid namespace %s', $namespace));
        }
        $baseClassName = $this->option('base-class-name');
        if ($baseClassName !== null && ! preg_match('/^\\\\?[A-Za-z_][A-Za-z0-9_]*(?:\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $baseClassName)) {
            throw new GeneratorException(sprintf('Invalid base class name %s', $baseClassName));
        }

        return (new Config)
            ->setTableName($this->option('table-name'))
            ->setNamespace($namespace)
            ->setBaseClassName($baseClassName)
            ->setNoTimestamps($this->option('no-timestamps'))
            ->setDateFormat($this->option('date-format'))
            ->setConnection($this->option('connection'));
    }

    protected function saveModel(EloquentModel $model): void
    {
        $content = $model->render();

        $modelName = $model->getName()->getName();
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $modelName)) {
            throw new GeneratorException(sprintf('Invalid model class name %s', $modelName));
        }
        $outputFilepath = $this->resolveOutputPath().'/'.$modelName.'.php';
        if (! $this->option('no-backup') && file_exists($outputFilepath)) {
            rename($outputFilepath, $outputFilepath.'~');
        }
        file_put_contents($outputFilepath, $content);
    }

    protected function resolveOutputPath(): string
    {
        $path = $this->option('output-path');
        if ($path === null) {
            /* @phpstan-ignore-next-line */
            $path = app()->path('Models');
        } elseif (! str_starts_with($path, '/')) {
            /* @phpstan-ignore-next-line */
            $path = app()->path($path);
        }

        if (! is_dir($path)) {
            if (! mkdir($path, 0755, true)) {
                throw new GeneratorException(sprintf('Could not create directory %s', $path));
            }
        }

        if (! is_writable($path)) {
            throw new GeneratorException(sprintf('%s is not writeable', $path));
        }

        return $path;
    }

    protected function getCommonOptions(): array
    {
        return [
            ['table-name', 'tn', InputOption::VALUE_OPTIONAL, 'Name of the table to use', null],
            ['output-path', 'op', InputOption::VALUE_OPTIONAL, 'Directory to store generated model', config('eloquent_model_generator.output_path')],
            ['namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Namespace of the model', config('eloquent_model_generator.namespace', 'App\Models')],
            ['base-class-name', 'bc', InputOption::VALUE_OPTIONAL, 'Model parent class', config('eloquent_model_generator.base_class_name', Model::class)],
            ['no-timestamps', 'ts', InputOption::VALUE_OPTIONAL, 'Set timestamps property to false', config('eloquent_model_generator.no_timestamps', false)],
            ['date-format', 'df', InputOption::VALUE_OPTIONAL, 'dateFormat property', config('eloquent_model_generator.date_format')],
            ['connection', 'cn', InputOption::VALUE_OPTIONAL, 'Connection property', config('eloquent_model_generator.connection')],
            ['no-backup', 'b', InputOption::VALUE_OPTIONAL, 'Backup existing model', config('eloquent_model_generator.no_backup', false)],
        ];
    }
}
