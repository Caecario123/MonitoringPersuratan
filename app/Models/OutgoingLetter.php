<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outgoingletter extends Model
{
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'outgoing_letter_date',
        'reference_number2',
        'letter_id',
        'note',
        'user_id',
    ];  
    use HasFactory;
}
