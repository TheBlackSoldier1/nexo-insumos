<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model {use SoftDeletes; protected $guarded=[]; public function stocks(){return $this->hasMany(Stock::class);} public function movements(){return $this->hasMany(Movement::class);} }
