<?php

namespace Dreadfulcode\EloquentModelGenerator\Command;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Dreadfulcode\EloquentModelGenerator\Generator;
use Dreadfulcode\EloquentModelGenerator\Helper\Prefix;
use Symfony\Component\Console\Input\InputArgument;

class GenerateModelCommand extends Command
{
    use GenerateCommandTrait;

    protected $name = 'dreadfulcode:generate:model';

    public function __construct(private Generator $generator, private DatabaseManager $databaseManager)
    {
        parent::__construct();
    }

    public function handle()
    {
        $config = $this->createConfig();
        $config->setClassName($this->argument('class-name'));
        Prefix::setPrefix($this->databaseManager->connection($config->getConnection())->getTablePrefix());

        $model = $this->generator->generateModel($config);
        $this->saveModel($model);

        $this->output->writeln(sprintf('Model %s generated', $model->getName()->getName()));
    }

    protected function getArguments()
    {
        return [
            ['class-name', InputArgument::REQUIRED, 'Model class name'],
        ];
    }

    protected function getOptions()
    {
        return $this->getCommonOptions();
    }
}
