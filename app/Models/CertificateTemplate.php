<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
   protected $fillable = [
    'slug',
    'name',
    'view_name'
];
}
        