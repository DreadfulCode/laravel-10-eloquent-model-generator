<?php

namespace Dreadfulcode\EloquentModelGenerator\Command;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Schema;
use Dreadfulcode\EloquentModelGenerator\Generator;
use Dreadfulcode\EloquentModelGenerator\Helper\EmgHelper;
use Dreadfulcode\EloquentModelGenerator\Helper\Prefix;
use Symfony\Component\Console\Input\InputOption;

class GenerateModelsCommand extends Command
{
    use GenerateCommandTrait;

    protected $name = 'dreadfulcode:generate:models';

    public function __construct(private Generator $generator, private DatabaseManager $databaseManager)
    {
        parent::__construct();
    }

    public function handle()
    {
        $config = $this->createConfig();
        $connection = $config->getConnection();
        Prefix::setPrefix($this->databaseManager->connection($connection)->getTablePrefix());

        $tables = Schema::connection($connection)->getTables();
        $skipTables = $this->option('skip-table');

        foreach ($tables as $table) {
            $tableName = Prefix::remove($table['name']);
            if (in_array($tableName, $skipTables)) {
                continue;
            }

            $config->setClassName(EmgHelper::getClassNameByTableName($tableName));
            $model = $this->generator->generateModel($config);
            $this->saveModel($model);

            $this->output->writeln(sprintf('Model %s generated', $model->getName()->getName()));
        }
    }

    protected function getOptions()
    {
        return array_merge(
            $this->getCommonOptions(),
            [
                ['skip-table', 'sk', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Tables to skip generating models for', null],
            ],
        );
    }
}
