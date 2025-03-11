<?php

namespace Modules\ServiceManagement\Models;

use App\Classes\Model as ClassesModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Service extends Model
{
    protected $table = 'service_managements'; // Sesuaikan dengan nama tabel di database
    protected $fillable = ['service_name', 'service_price']; // Sesuaikan dengan kolom yang ada di tabel
}