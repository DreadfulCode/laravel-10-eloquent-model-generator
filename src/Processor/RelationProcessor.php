<?php

namespace Dreadfulcode\EloquentModelGenerator\Processor;

use Illuminate\Support\Facades\Schema;
use Dreadfulcode\EloquentModelGenerator\Config\Config;
use Dreadfulcode\EloquentModelGenerator\Helper\EmgHelper;
use Dreadfulcode\EloquentModelGenerator\Helper\Prefix;
use Dreadfulcode\EloquentModelGenerator\Model\BelongsTo;
use Dreadfulcode\EloquentModelGenerator\Model\BelongsToMany;
use Dreadfulcode\EloquentModelGenerator\Model\EloquentModel;
use Dreadfulcode\EloquentModelGenerator\Model\HasMany;
use Dreadfulcode\EloquentModelGenerator\Model\HasOne;

class RelationProcessor implements ProcessorInterface
{
    public function process(EloquentModel $model, Config $config): void
    {
        $connection = $config->getConnection();
        $schema = Schema::connection($connection);
        $prefixedTableName = Prefix::add($model->getTableName());

        $tables = $schema->getTables();

        foreach ($tables as $table) {
            $currentTableName = $table['name'];
            $foreignKeys = $schema->getForeignKeys($currentTableName);
            $columnCount = count($schema->getColumns($currentTableName));

            foreach ($foreignKeys as $fkIndex => $foreignKey) {
                $localColumns = $foreignKey['columns'];
                if (count($localColumns) !== 1) {
                    continue;
                }

                if ($currentTableName === $prefixedTableName) {
                    $relation = new BelongsTo(
                        Prefix::remove($foreignKey['foreign_table']),
                        $foreignKey['columns'][0],
                        $foreignKey['foreign_columns'][0]
                    );
                    $model->addRelation($relation);
                } elseif ($foreignKey['foreign_table'] === $prefixedTableName) {
                    if (count($foreignKeys) === 2 && $columnCount === 2) {
                        $secondForeignKey = $foreignKeys[$fkIndex === 0 ? 1 : 0];
                        $secondForeignTable = Prefix::remove($secondForeignKey['foreign_table']);

                        $relation = new BelongsToMany(
                            $secondForeignTable,
                            Prefix::remove($currentTableName),
                            $localColumns[0],
                            $secondForeignKey['columns'][0]
                        );
                        $model->addRelation($relation);

                        break;
                    } else {
                        $tableName = Prefix::remove($currentTableName);
                        $foreignColumn = $localColumns[0];
                        $localColumn = $foreignKey['foreign_columns'][0];

                        if (EmgHelper::isColumnUnique($currentTableName, $foreignColumn, $connection)) {
                            $relation = new HasOne($tableName, $foreignColumn, $localColumn);
                        } else {
                            $relation = new HasMany($tableName, $foreignColumn, $localColumn);
                        }

                        $model->addRelation($relation);
                    }
                }
            }
        }
    }

    public function getPriority(): int
    {
        return 5;
    }
}
