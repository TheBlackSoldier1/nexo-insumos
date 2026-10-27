<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Product,Location};
use App\Services\InventoryService;
class DemoSeeder extends Seeder {
 public function run():void {
  if(Product::withTrashed()->exists()||Location::exists())return;
  $a=Location::create(['name'=>'Bodega central','description'=>'Almacenamiento y recepción de insumos']);$b=Location::create(['name'=>'Oficina TI','description'=>'Equipamiento del equipo de soporte']);$c=Location::create(['name'=>'Sala de servidores','description'=>'Infraestructura y conectividad']);
  $items=[['TEC-001','Teclado Logitech K120','Periféricos',48,10,12990],['MOU-002','Mouse inalámbrico M185','Periféricos',36,10,14990],['CAB-003','Cable de red Cat 6 · 3 m','Conectividad',120,20,3990],['SSD-004','Disco SSD Kingston 480 GB','Almacenamiento',8,10,34990],['RAM-005','Memoria RAM DDR4 8 GB','Componentes',24,5,24990],['TON-006','Tóner HP 85A','Impresión',4,8,45990],['USB-007','Memoria USB 64 GB','Almacenamiento',42,10,7990],['MON-008','Monitor Dell 24 pulgadas','Periféricos',6,8,149990]];
  $s=new InventoryService;foreach($items as $i=>$row){[$sku,$name,$category,$quantity,$minimum,$cost]=$row;$p=Product::create(compact('sku','name','category','minimum','cost'));$s->move(['product_id'=>$p->id,'type'=>'entrada','to_id'=>$a->id,'quantity'=>$quantity,'occurred_at'=>now()->subDays(8)->addHours($i),'responsible'=>'Datos de demostración','note'=>'Carga inicial de ejemplo']);if($quantity>10)$s->move(['product_id'=>$p->id,'type'=>'traslado','from_id'=>$a->id,'to_id'=>$i%2?$b->id:$c->id,'quantity'=>3,'occurred_at'=>now()->subDays(2)->addHours($i),'responsible'=>'Datos de demostración','note'=>'Asignación de ejemplo']);}
 }
}
