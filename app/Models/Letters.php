<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Letters extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'received_date',
        'letters_type',
        'reference_number',
        'letter_date',
        'from',
        'description',
        'disposition_date',
        'disposition_note',
        'disposition_process',
        'status',
        'user_id',
        'read_status',
    ];

    /**
     * Get the user that owns the letter.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}