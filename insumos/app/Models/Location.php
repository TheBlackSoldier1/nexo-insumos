<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Location extends Model {protected $guarded=[];public function stocks(){return $this->hasMany(Stock::class);} }
