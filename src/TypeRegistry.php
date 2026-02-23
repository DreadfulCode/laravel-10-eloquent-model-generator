<?php

namespace Dreadfulcode\EloquentModelGenerator;

class TypeRegistry
{
    protected array $types = [
        'array'        => 'array',
        'simple_array' => 'array',
        'json_array'   => 'string',
        'json'         => 'string',
        'jsonb'        => 'string',
        'bigint'       => 'integer',
        'bigserial'    => 'integer',
        'boolean'      => 'boolean',
        'bool'         => 'boolean',
        'datetime'     => 'string',
        'datetimetz'   => 'string',
        'date'         => 'string',
        'time'         => 'string',
        'timestamp'    => 'string',
        'timestamptz'  => 'string',
        'decimal'      => 'float',
        'numeric'      => 'float',
        'double'       => 'float',
        'real'         => 'float',
        'integer'      => 'integer',
        'int'          => 'integer',
        'int4'         => 'integer',
        'int8'         => 'integer',
        'serial'       => 'integer',
        'smallint'     => 'integer',
        'smallserial'  => 'integer',
        'mediumint'    => 'integer',
        'tinyint'      => 'integer',
        'object'       => 'object',
        'string'       => 'string',
        'varchar'      => 'string',
        'char'         => 'string',
        'character varying' => 'string',
        'text'         => 'string',
        'mediumtext'   => 'string',
        'longtext'     => 'string',
        'binary'       => 'string',
        'blob'         => 'string',
        'float'        => 'float',
        'guid'         => 'string',
        'uuid'         => 'string',
        'enum'         => 'string',
    ];

    public function registerType(string $sqlType, string $phpType): void
    {
        $this->types[$sqlType] = $phpType;
    }

    public function resolveType(string $type): string
    {
        return $this->types[$type] ?? 'mixed';
    }
}
