<?php

namespace Ihasan\ReportBuilder\Exceptions;

use Illuminate\Database\Eloquent\Model;

class InvalidDataSourceConfigurationException extends ReportBuilderException
{
    public static function invalidField(string $sourceKey, mixed $field): self
    {
        return new self(sprintf(
            'Report data source [%s] contains an invalid field definition of type [%s].',
            $sourceKey,
            get_debug_type($field)
        ));
    }

    public static function invalidModel(string $sourceKey, mixed $model): self
    {
        return new self(sprintf(
            'Report data source [%s] must use an Eloquent model instance or class-string extending [%s], [%s] given.',
            $sourceKey,
            Model::class,
            get_debug_type($model)
        ));
    }

    public static function duplicateField(string $sourceKey, string $fieldKey): self
    {
        return new self(sprintf(
            'Report data source [%s] contains the field key [%s] more than once.',
            $sourceKey,
            $fieldKey
        ));
    }
}
