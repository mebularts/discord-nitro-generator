<?php
if(empty($_SESSION['book']['pid'])) redirect('/');
require_once __DIR__.'/../app/notify.php';
$general = settings_get($pdo,'general',['weekend_enabled'=>0,'lunch_enabled'=>1,'day_start'=>'09:00','day_end'=>'18:00','lunch_start'=>'12:30','lunch_end'=>'13:30','slot_minutes'=>30]);
$pid=(int)$_SESSION['book']['pid']; $today=new DateTime('today'); $month=$_GET['m'] ?? date('Y-m');
try{$first=new DateTime($month.'-01');}catch(Throwable $e){$first=new DateTime('first day of this month');}
$days=(int)$first->format('t'); $prev=(clone $first)->modify('-1 month')->format('Y-m'); $next=(clone $first)->modify('+1 month')->format('Y-m');
function time_range_local($s,$e,$step){ $o=[]; $t=strtotime($s); $E=strtotime($e); while($t<$E){$o[]=date('H:i',$t);$t+=$step*60;}return $o;}
function slots_for(PDO $pdo,$pid,$date,$g){
  $w=(int)date('N',strtotime($date)); if(!$g['weekend_enabled'] && ($w==6||$w==7)) return [];
  $slots=time_range_local($g['day_start'],$g['day_end'],$g['slot_minutes']);
  if(!$g['lunch_enabled']){ $ls=strtotime($g['lunch_start']); $le=strtotime($g['lunch_end']); $slots=array_values(array_filter($slots,function($s)use($ls,$le){$ts=strtotime($s);return !($ts>=$ls && $ts<$le);})); }
  $rules=q($pdo,"SELECT start_time,end_time FROM provider_rules WHERE provider_id=? AND ((repeat_weekly=1 AND weekday=?) OR date=?)",[$pid,(int)date('N',strtotime($date)),$date])->fetchAll();
  $slots=array_values(array_filter($slots,function($s)use($rules){$ts=strtotime($s);foreach($rules as $r){if(!$r['start_time']||!$r['end_time'])continue; $st=strtotime($r['start_time']);$en=strtotime($r['end_time']); if($ts>=$st && $ts<$en) return false;} return true;}));
  $busy=q($pdo,"SELECT app_time FROM appointments WHERE provider_id=? AND app_date=?",[$pid,$date])->fetchAll(PDO::FETCH_COLUMN);
  return array_values(array_diff($slots,$busy));
}
if(isset($_GET['d'])){ $_SESSION['book']['date']=$_GET['d']; redirect('/?p=time'); }
?>
<div class="p-6"><div class="flex items-center justify-between mb-4"><div class="text-slate-600"><?=h(t('book.date'))?></div>
<div class="flex items-center gap-2"><a class="px-3 py-2 rounded-xl border" href="/?p=date&m=<?=$prev?>">←</a><div class="font-medium"><?=h(format_month_year($first))?></div><a class="px-3 py-2 rounded-xl border" href="/?p=date&m=<?=$next?>">→</a></div></div>
<div class="grid grid-cols-7 gap-2 text-center text-xs text-slate-600 mb-2"><div>Pzt</div><div>Sal</div><div>Çar</div><div>Per</div><div>Cum</div><div>Cmt</div><div>Paz</div></div>
<div class="grid grid-cols-7 gap-2">
<?php $startDow=(int)$first->format('N'); for($i=1;$i<$startDow;$i++): ?><div></div><?php endfor; ?>
<?php for($d=1;$d<=$days;$d++): $dateStr=$first->format('Y-m').'-'.str_pad((string)$d,2,'0',STR_PAD_LEFT); $isPast=(new DateTime($dateStr))<$today; $available=!$isPast && count(slots_for($pdo,$pid,$dateStr,$general))>0; $cls='px-3 py-3 rounded-xl border text-center flex items-center justify-center gap-1 '.($available?'hover:border-sky-600 bg-white':'opacity-40 pointer-events-none bg-slate-50'); $label=date('d.m.Y',strtotime($dateStr)); $badge=$available?'✅':'⛔'; ?>
  <a class="<?=$cls?>" href="/?p=date&d=<?=$dateStr?>"><?=$badge?> <?=$label?></a>
<?php endfor; ?></div>
<div class="flex justify-between mt-6"><a href="/?p=book&pid=<?=$pid?>" class="px-4 py-2 rounded-lg border"><?=h(t('btn.prev'))?></a></div></div>
