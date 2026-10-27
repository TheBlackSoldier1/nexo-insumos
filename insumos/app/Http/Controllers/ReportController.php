<?php
namespace App\Http\Controllers;
use App\Models\{Product,Movement};
use App\Services\XlsxExporter;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
class ReportController extends Controller {
 public function export(Request $r,string $format){
  $r->validate(['dataset'=>'nullable|in:inventory,movements','product_id'=>'nullable|exists:products,id','start'=>'nullable|date','end'=>$r->filled('start')?'nullable|date|after_or_equal:start':'nullable|date']);
  $dataset=$r->input('dataset','inventory');
  if($dataset==='movements'){
   $q=Movement::with(['product','from','to'])->orderBy('occurred_at');
   if($r->filled('product_id'))$q->where('product_id',$r->product_id);
   if($r->filled('start'))$q->whereDate('occurred_at','>=',$r->start);
   if($r->filled('end'))$q->whereDate('occurred_at','<=',$r->end);
   $headers=['Fecha','SKU','Producto','Tipo','Origen','Destino','Cantidad','Responsable','Nota'];
   $rows=$q->get()->map(fn($m)=>[$m->occurred_at->format('Y-m-d H:i'),$m->product->sku,$m->product->name,$m->type,$m->from?->name??'Externo',$m->to?->name??'Externo',$m->quantity,$m->responsible,$m->note??''])->all();
  }else{
   $q=Product::with('stocks.location')->withCount('movements');if($r->filled('product_id'))$q->where('id',$r->product_id);
   $headers=['SKU','Producto','Categoría','Ubicación','Cantidad','Mínimo global','Costo CLP','Movimientos'];$rows=[];
   foreach($q->get() as $p){if($p->stocks->isEmpty())$rows[]=[$p->sku,$p->name,$p->category,'Sin ubicación',0,$p->minimum,(float)$p->cost,$p->movements_count];foreach($p->stocks as $s)$rows[]=[$p->sku,$p->name,$p->category,$s->location->name,$s->quantity,$p->minimum,(float)$p->cost,$p->movements_count];}
  }
  $name='nexo-'.$dataset.'-'.now()->format('Ymd-His');
  if($format==='json')return response()->json(['generated_at'=>now()->toIso8601String(),'dataset'=>$dataset,'records'=>array_map(fn($row)=>array_combine($headers,$row),$rows)],200,['Content-Disposition'=>'attachment; filename="'.$name.'.json"'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
  if($format==='csv')return response()->streamDownload(function()use($headers,$rows){$f=fopen('php://output','w');fwrite($f,"\xEF\xBB\xBF");foreach([$headers,...$rows] as $row){$row=array_map(fn($v)=>is_string($v)&&preg_match('/^[=+@\-\t\r]/u',$v)?"'".$v:$v,$row);fputcsv($f,$row,',','"','');}fclose($f);},$name.'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
  if($format==='xlsx')return response()->streamDownload(fn()=>(new XlsxExporter)->write([$headers,...$rows]),$name.'.xlsx',['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
  $pdf=new Dompdf(['isRemoteEnabled'=>false]);$pdf->loadHtml(view('report',compact('headers','rows','dataset'))->render());$pdf->setPaper('A4','landscape');$pdf->render();return response($pdf->output(),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="'.$name.'.pdf"']);
 }
}
