<?php
if(empty($_SESSION['book']['date'])) redirect('/?p=date');
require_once __DIR__.'/../app/notify.php';
$g=settings_get($pdo,'general',['weekend_enabled'=>0,'lunch_enabled'=>1,'day_start'=>'09:00','day_end'=>'18:00','lunch_start'=>'12:30','lunch_end'=>'13:30','slot_minutes'=>30]);
$date=$_SESSION['book']['date']; $pid=(int)$_SESSION['book']['pid']; $wd=(int)date('N',strtotime($date));
function time_range($s,$e,$step){$o=[];$t=strtotime($s);$E=strtotime($e);while($t<$E){$o[]=date('H:i',$t);$t+=$step*60;}return $o;}
$slots=time_range($g['day_start'],$g['day_end'],$g['slot_minutes']);
if(!$g['lunch_enabled']){ $ls=strtotime($g['lunch_start']);$le=strtotime($g['lunch_end']); $slots=array_values(array_filter($slots,function($s)use($ls,$le){$ts=strtotime($s);return !($ts>=$ls&&$ts<$le);})); }
$rules=q($pdo,"SELECT start_time,end_time FROM provider_rules WHERE provider_id=? AND ((repeat_weekly=1 AND weekday=?) OR date=?)",[$pid,$wd,$date])->fetchAll();
$apps=q($pdo,"SELECT app_time FROM appointments WHERE provider_id=? AND app_date=?",[$pid,$date])->fetchAll(PDO::FETCH_COLUMN);
function blocked($t,$rules){$ts=strtotime($t); foreach($rules as $r){ if(!$r['start_time']||!$r['end_time']) continue; $s=strtotime($r['start_time']);$e=strtotime($r['end_time']); if($ts>=$s && $ts<$e) return true;} return false;}
if(isset($_GET['t'])){ if(in_array($_GET['t'],$apps,true)) redirect('/?p=time'); $_SESSION['book']['time']=$_GET['t']; redirect('/?p=confirm'); }
$am=array_filter($slots,fn($s)=>strtotime($s)<strtotime('12:00')); $pm=array_filter($slots,fn($s)=>strtotime($s)>=strtotime('12:00'));
?>
<div class="p-6"><div class="flex items-center justify-between mb-4"><div class="text-slate-600"><?=h(t('book.time'))?></div><div class="text-sm text-slate-500"><?=date('d.m.Y',strtotime($date))?></div></div>
<div class="mb-2 font-medium">Öğleden önce</div><div class="grid md:grid-cols-4 gap-3 mb-6">
<?php foreach($am as $s): $isBusy=in_array($s,$apps,true)||blocked($s,$rules); $cls='px-4 py-3 text-center border rounded-xl '.($isBusy?'opacity-40 pointer-events-none bg-slate-50':'hover:border-sky-600 bg-white'); ?><a class="<?=$cls?>" href="/?p=time&t=<?=$s?>"><?=$s?></a><?php endforeach; ?>
</div><div class="mb-2 font-medium">Öğleden sonra</div><div class="grid md:grid-cols-4 gap-3">
<?php foreach($pm as $s): $isBusy=in_array($s,$apps,true)||blocked($s,$rules); $cls='px-4 py-3 text-center border rounded-xl '.($isBusy?'opacity-40 pointer-events-none bg-slate-50':'hover:border-sky-600 bg-white'); ?><a class="<?=$cls?>" href="/?p=time&t=<?=$s?>"><?=$s?></a><?php endforeach; ?>
</div><div class="flex justify-between mt-6"><a href="/?p=date" class="px-4 py-2 rounded-lg border"><?=h(t('btn.prev'))?></a></div></div>
