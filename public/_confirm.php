<?php
if(empty($_SESSION['book']['time'])) redirect('/?p=time');
require_once __DIR__.'/../app/notify.php';
if(is_post()){ csrf_check(); $b=$_SESSION['book'];
  q($pdo,"INSERT INTO appointments(provider_id,full_name,gender,birth,phone,email,note,app_date,app_time,status,created_at,reminder_sent) VALUES(?,?,?,?,?,?,?,?,?,'new',NOW(),0)",[$b['pid'],$b['full_name'],$b['gender'],$b['birth'],$b['phone'],$b['email'],$b['note'],$b['date'],$b['time']]);
  $id=(int)$pdo->lastInsertId(); $_SESSION['last_id']=$id;
  if(!empty($b['email'])) send_email($pdo,$b['email'],"Randevu Onayı #$id","<p>Randevunuz ".date('d.m.Y',strtotime($b['date']))." {$b['time']} için oluşturuldu.</p>");
  if(!empty($b['phone'])) send_sms($pdo,$b['phone'],"Randevunuz onaylandi: ".date('d.m.Y',strtotime($b['date']))." {$b['time']}");
  redirect('/?p=done');
}
$prov=q($pdo,"SELECT name FROM providers WHERE id=?",[$_SESSION['book']['pid']])->fetchColumn(); $b=$_SESSION['book'];
?>
<div class="p-6"><div class="grid md:grid-cols-3 gap-4 text-center">
<div class="bg-slate-50 rounded-xl p-6"><div class="text-sm text-slate-500"><?=h(t('confirm.provider'))?></div><div class="text-xl font-semibold"><?=h($prov)?></div></div>
<div class="bg-slate-50 rounded-xl p-6"><div class="text-sm text-slate-500"><?=h(t('confirm.date'))?></div><div class="text-xl font-semibold"><?=date('d.m.Y',strtotime($b['date']))?></div></div>
<div class="bg-slate-50 rounded-xl p-6"><div class="text-sm text-slate-500"><?=h(t('confirm.time'))?></div><div class="text-xl font-semibold"><?=h($b['time'])?></div></div>
</div><form method="post" class="flex justify-between mt-6"><?=csrf_field()?><a href="/?p=time" class="px-4 py-2 rounded-lg border"><?=h(t('btn.prev'))?></a><button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tamamla</button></form></div>
