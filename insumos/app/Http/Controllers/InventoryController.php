<?php
namespace App\Http\Controllers;
use App\Models\{Product,Location,Movement};
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class InventoryController extends Controller {
 public function index(Request $r){
  $page=$r->route('page')??'dashboard';
  abort_unless(in_array($page,['dashboard','products','movements','locations','reports','audit']),404);
  $all=Product::with('stocks.location')->withCount(['movements', 'movements as transfers_count'=>fn($q)=>$q->where('type','traslado')])->get();
  $query=Product::with('stocks.location')->withCount(['movements', 'movements as transfers_count'=>fn($q)=>$q->where('type','traslado')]);
  if($r->filled('q'))$query->where(fn($q)=>$q->where('name','like','%'.$r->q.'%')->orWhere('sku','like','%'.$r->q.'%'));
  if($r->filled('category'))$query->where('category',$r->category);
  $products=$query->orderBy('name')->get();
  if($r->boolean('low'))$products=$products->filter(fn($p)=>$p->stocks->sum('quantity')<=$p->minimum);
  $mq=Movement::with(['product','from','to'])->latest('occurred_at')->latest('id');
  if($r->filled('product_id'))$mq->where('product_id',$r->product_id);
  if($r->filled('type'))$mq->where('type',$r->type);
  if($r->filled('start'))$mq->whereDate('occurred_at','>=',$r->start);
  if($r->filled('end'))$mq->whereDate('occurred_at','<=',$r->end);
  $movements=$mq->paginate(20)->withQueryString();
  return view('inventory',compact('page','all','products','movements')+['locations'=>Location::withSum('stocks','quantity')->get(),'audits'=>DB::table('audits')->latest('id')->paginate(20),'categories'=>Product::distinct()->pluck('category'),'totalMovements'=>Movement::count()]);
 }
 private function audit($action,$detail){DB::table('audits')->insert(['action'=>$action,'detail'=>$detail,'created_at'=>now(),'updated_at'=>now()]);}
 public function product(Request $r,?Product $product=null){
  $data=$r->validate(['sku'=>['required','max:60',Rule::unique('products')->ignore($product?->id)],'name'=>'required|max:150','category'=>'required|max:60','minimum'=>'required|integer|min:0|max:1000000','cost'=>'required|numeric|min:0|max:999999999','description'=>'nullable|max:2000']);
  DB::transaction(function()use($product,$data){if($product?->exists){$before=$product->only(array_keys($data));$product->update($data);$this->audit('Producto editado',json_encode(['antes'=>$before,'después'=>$data],JSON_UNESCAPED_UNICODE));}else{Product::create($data);$this->audit('Producto creado',$data['sku'].' · '.$data['name']);}});
  return back()->with('success','Producto guardado. Registra una entrada para agregar existencias.');
 }
 public function delete(Product $product){
  DB::transaction(function()use($product){if($product->stocks()->sum('quantity')>0)throw \Illuminate\Validation\ValidationException::withMessages(['product'=>'Registra la salida de las existencias antes de dar de baja el producto.']);$product->delete();$this->audit('Producto dado de baja',$product->sku.' · '.$product->name);});
  return back()->with('success','Producto dado de baja. Su historial se conserva.');
 }
 public function move(Request $r,InventoryService $service){$data=$r->validate(['product_id'=>['required',Rule::exists('products','id')->whereNull('deleted_at')],'type'=>'required|in:entrada,salida,traslado','from_id'=>'nullable|exists:locations,id','to_id'=>'nullable|exists:locations,id','quantity'=>'required|integer|min:1|max:1000000','occurred_at'=>'required|date|before_or_equal:now','responsible'=>'required|max:100','note'=>'nullable|max:2000']);$service->move($data);return back()->with('success','Movimiento registrado. Existencias actualizadas.');}
 public function location(Request $r,?Location $location=null){$data=$r->validate(['name'=>['required','max:100',Rule::unique('locations')->ignore($location?->id)],'description'=>'nullable|max:200']);DB::transaction(function()use($location,$data){$before=$location?->getOriginal();$location?->exists?$location->update($data):Location::create($data);$this->audit('Ubicación guardada',json_encode(['antes'=>$before,'después'=>$data],JSON_UNESCAPED_UNICODE));});return back()->with('success','Ubicación guardada.');}
 public function deleteLocation(Location $location){if($location->stocks()->exists()||Movement::where('from_id',$location->id)->orWhere('to_id',$location->id)->exists())return back()->withErrors(['location'=>'La ubicación tiene existencias o historial y debe conservarse.']);DB::transaction(function()use($location){$location->delete();$this->audit('Ubicación eliminada',$location->name);});return back()->with('success','Ubicación eliminada.');}
}
