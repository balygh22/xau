<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Align with existing DB naming if you already have a table
    protected $table = 'categories'; // use lowercase for portability
    protected $primaryKey = 'CategoryID';
    public $timestamps = false;

    protected $fillable = [
        'CategoryName',
    ];
}