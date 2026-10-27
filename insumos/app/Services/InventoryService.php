<?php
namespace App\Services;
use App\Models\{Product,Stock,Movement};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InventoryService {
 public function move(array $data): Movement {
  return DB::transaction(function()use($data){
   Product::findOrFail($data['product_id']);
   $from=$data['type']==='entrada'?null:($data['from_id']??null);
   $to=$data['type']==='salida'?null:($data['to_id']??null);
   if(($data['type']!=='entrada'&&!$from)||($data['type']!=='salida'&&!$to)||($from&&$from==$to)) throw ValidationException::withMessages(['movement'=>'Selecciona un origen y destino válidos y diferentes.']);
   $qty=(int)$data['quantity'];
   if($from){$updated=Stock::where('product_id',$data['product_id'])->where('location_id',$from)->where('quantity','>=',$qty)->decrement('quantity',$qty);if(!$updated)throw ValidationException::withMessages(['quantity'=>'No hay suficientes unidades en el origen.']);}
   if($to){$stock=Stock::firstOrCreate(['product_id'=>$data['product_id'],'location_id'=>$to],['quantity'=>0]);$stock->increment('quantity',$qty);}
   return Movement::create([...$data,'from_id'=>$from,'to_id'=>$to]);
  });
 }
}
