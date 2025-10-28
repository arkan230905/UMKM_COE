<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = ['tanggal','ref_type','ref_id','memo'];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }
}
