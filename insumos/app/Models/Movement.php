<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Movement extends Model {protected $guarded=[];protected function casts():array{return ['occurred_at'=>'datetime'];} public function product(){return $this->belongsTo(Product::class)->withTrashed();} public function from(){return $this->belongsTo(Location::class,'from_id');}public function to(){return $this->belongsTo(Location::class,'to_id');} }
