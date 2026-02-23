<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Illuminate\Support\Facades\Schema;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\PropertyModel;
use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Helper\Prefix;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;
use Dreadfulcode\EloquentModelGenerator\TypeRegistry;

class CustomPrimaryKeyProcessor implements ProcessorInterface
{
    public function __construct(private TypeRegistry $typeRegistry)
    {
    }

    public function process(EloquentModel $model, Config $config): void
    {
        $tableName = Prefix::add($model->getTableName());
        $connection = $config->getConnection();
        $schema = Schema::connection($connection);

        $primaryColumnName = $this->findPrimaryKeyColumn($schema, $tableName);
        if ($primaryColumnName === null) {
            return;
        }

        $column = $this->findColumn($schema, $tableName, $primaryColumnName);
        if ($column === null) {
            return;
        }

        if ($column['name'] !== 'id') {
            $primaryKeyProperty = new PropertyModel('primaryKey', 'protected', $column['name']);
            $primaryKeyProperty->setDocBlock(
                new DocBlockModel('The primary key for the model.', '', '@var string')
            );
            $model->addProperty($primaryKeyProperty);
        }

        if ($column['type_name'] !== 'integer' && $column['type_name'] !== 'int' && $column['type_name'] !== 'int4') {
            $keyTypeProperty = new PropertyModel(
                'keyType',
                'protected',
                $this->typeRegistry->resolveType($column['type_name'])
            );
            $keyTypeProperty->setDocBlock(
                new DocBlockModel('The "type" of the auto-incrementing ID.', '', '@var string')
            );
            $model->addProperty($keyTypeProperty);
        }

        if (!($column['auto_increment'] ?? false)) {
            $autoincrementProperty = new PropertyModel('incrementing', 'public', false);
            $autoincrementProperty->setDocBlock(
                new DocBlockModel('Indicates if the IDs are auto-incrementing.', '', '@var bool')
            );
            $model->addProperty($autoincrementProperty);
        }
    }

    private function findPrimaryKeyColumn($schema, string $tableName): ?string
    {
        $indexes = $schema->getIndexes($tableName);

        foreach ($indexes as $index) {
            if (!($index['primary'] ?? false)) {
                continue;
            }
            if (count($index['columns']) !== 1) {
                return null;
            }
            return $index['columns'][0];
        }

        return null;
    }

    private function findColumn($schema, string $tableName, string $columnName): ?array
    {
        $columns = $schema->getColumns($tableName);

        foreach ($columns as $column) {
            if ($column['name'] === $columnName) {
                return $column;
            }
        }

        return null;
    }

    public function getPriority(): int
    {
        return 6;
    }
}
