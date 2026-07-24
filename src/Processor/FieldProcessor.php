<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Helper\Prefix;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;
use Dreadfulcode\EloquentModelGenerator\TypeRegistry;
use Illuminate\Database\DatabaseManager;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\PropertyModel;
use Krlove\CodeGenerator\Model\VirtualPropertyModel;

class FieldProcessor implements ProcessorInterface
{
    public function __construct(private DatabaseManager $databaseManager, private TypeRegistry $typeRegistry) {}

    public function process(EloquentModel $model, Config $config): void
    {
        $schemaManager = $this->databaseManager->connection($config->getConnection())->getDoctrineSchemaManager();

        $tableDetails = $schemaManager->listTableDetails(Prefix::add($model->getTableName()));
        $primaryColumnNames = $tableDetails->getPrimaryKey() ? $tableDetails->getPrimaryKey()->getColumns() : [];

        $columnNames = [];
        foreach ($tableDetails->getColumns() as $column) {
            $model->addProperty(new VirtualPropertyModel(
                $column->getName(),
                $this->typeRegistry->resolveType($column->getType()->getName())
            ));

            if (! in_array($column->getName(), $primaryColumnNames)) {
                $columnNames[] = $column->getName();
            }
        }

        // Secure default: avoid mass-assignment by not auto-filling all columns.
        // Developers should curate fillable explicitly.
        $fillableProperty = new PropertyModel('fillable');
        $fillableProperty->setAccess('protected')
            ->setValue([])
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($fillableProperty);
    }

    public function getPriority(): int
    {
        return 5;
    }
}
