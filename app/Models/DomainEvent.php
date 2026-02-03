<?php

namespace App\Models;

use App\Models\Traits\HasTableName;
use Illuminate\Database\Eloquent\Model;

class DomainEvent extends Model
{
    use HasTableName;

    protected $table = 'domain_events';

    public $timestamps = false;
}
