<?php

namespace App\Models;

use App\Classes\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int            $id
 * @property string         $uuid
 * @property string         $description
 * @property int            $author
 * @property \Carbon\Carbon $updated_at
 */
class UnitGroup extends NsModel
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'nexopos_units_groups';

    public function setDependencies()
    {
        return [
            Unit::class => Model::dependant(
                local_name: 'name',
                local_index: 'id',
                foreign_name: 'name',
                foreign_index: 'group_id',
            )
        ];
    }

    public function units()
    {
        return $this->hasMany( Unit::class, 'group_id' );
    }
}
