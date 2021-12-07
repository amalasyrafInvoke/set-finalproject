<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var string[]
   */
  protected $fillable = [
    'name',
    'details',
    'amount',
    'process_type',
    'account_id',
    'status'
  ];

  public function account()
  {
    return $this->belongsTo(Account::class);
  }
}
