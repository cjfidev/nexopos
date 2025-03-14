<?php

namespace Modules\ProcurementReturn\Models;

use App\Classes\Model as ClassesModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ProcurementReturn extends Model
{
    protected $table = 'procurements_returns'; // Sesuaikan dengan nama tabel di database
    protected $guarded = []; // Sesuaikan dengan kolom yang ada di tabel
}