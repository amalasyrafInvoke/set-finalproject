<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingTransaction extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'amount',
    'process_type',
    'savings_id',
  ];

  public function savings()
  {
    return $this->belongsTo(Saving::class); 
  }
}
