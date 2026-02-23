<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Illuminate\Support\Facades\Schema;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\PropertyModel;
use Krlove\CodeGenerator\Model\VirtualPropertyModel;
use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Helper\Prefix;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;
use Dreadfulcode\EloquentModelGenerator\TypeRegistry;

class FieldProcessor implements ProcessorInterface
{
    public function __construct(private TypeRegistry $typeRegistry)
    {
    }

    public function process(EloquentModel $model, Config $config): void
    {
        $tableName = Prefix::add($model->getTableName());
        $connection = $config->getConnection();
        $schema = Schema::connection($connection);

        $columns = $schema->getColumns($tableName);
        $primaryColumnNames = $this->getPrimaryKeyColumns($schema, $tableName);

        $columnNames = [];
        foreach ($columns as $column) {
            $model->addProperty(new VirtualPropertyModel(
                $column['name'],
                $this->typeRegistry->resolveType($column['type_name'])
            ));

            if (!in_array($column['name'], $primaryColumnNames)) {
                $columnNames[] = $column['name'];
            }
        }

        $fillableProperty = new PropertyModel('fillable');
        $fillableProperty->setAccess('protected')
            ->setValue($columnNames)
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($fillableProperty);
    }

    private function getPrimaryKeyColumns($schema, string $tableName): array
    {
        $indexes = $schema->getIndexes($tableName);

        foreach ($indexes as $index) {
            if ($index['primary'] ?? false) {
                return $index['columns'];
            }
        }

        return [];
    }

    public function getPriority(): int
    {
        return 5;
    }
}
