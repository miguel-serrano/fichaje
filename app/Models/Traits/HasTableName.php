<?php

namespace App\Models\Traits;

trait HasTableName
{
    public static function tableName(): string
    {
        return (new static())->getTable();
    }
}
