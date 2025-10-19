<?php
$_SESSION['book']= $_SESSION['book'] ?? [];
$pid = (int)($_GET['pid'] ?? ($_SESSION['book']['pid'] ?? 0)); if($pid<=0){ redirect('/'); } $_SESSION['book']['pid']=$pid;
if(is_post()){ csrf_check(); $_SESSION['book']['full_name']=trim($_POST['full_name']??''); $_SESSION['book']['gender']=trim($_POST['gender']??''); $_SESSION['book']['birth']=trim($_POST['birth']??''); $_SESSION['book']['phone']=trim($_POST['phone']??''); $_SESSION['book']['email']=trim($_POST['email']??''); $_SESSION['book']['note']=trim($_POST['note']??''); redirect('/?p=date'); }
$prov=q($pdo,"SELECT name,image FROM providers WHERE id=?",[$pid])->fetch();
$img = ($prov && $prov['image'] && file_exists(__DIR__.'/uploads/providers/'.$prov['image'])) ? '/uploads/providers/'.$prov['image'] : 'https://placehold.co/96x96?text=+';
?>
<div class="p-6"><div class="flex items-center gap-4 mb-4"><img src="<?=$img?>" class="w-14 h-14 rounded-xl object-cover border"><div><div class="text-sm text-slate-500">Randevu veren</div><div class="text-lg font-medium"><?=h($prov['name']??'')?></div></div></div>
<form method="post" class="grid md:grid-cols-2 gap-4"><?=csrf_field()?>
<label>Ad Soyad *<input required name="full_name" value="<?=h($_SESSION['book']['full_name'] ?? '')?>" class="border rounded w-full p-2"></label>
<label>Cinsiyet *<select name="gender" required class="border rounded w-full p-2"><?php $g=$_SESSION['book']['gender'] ?? ''; ?><option value="">Seçiniz</option><option value="Kadın" <?=$g==='Kadın'?'selected':''?>>Kadın</option><option value="Erkek" <?=$g==='Erkek'?'selected':''?>>Erkek</option></select></label>
<label>Doğum tarihi *<input required name="birth" placeholder="GG.AA.YYYY" value="<?=h($_SESSION['book']['birth'] ?? '')?>" class="border rounded w-full p-2"></label>
<label>Telefon *<input required name="phone" value="<?=h($_SESSION['book']['phone'] ?? '')?>" class="border rounded w-full p-2"></label>
<label>E-posta<input name="email" value="<?=h($_SESSION['book']['email'] ?? '')?>" class="border rounded w-full p-2"></label>
<label class="md:col-span-2">Notunuz<textarea name="note" class="border rounded w-full p-2" rows="3"><?=h($_SESSION['book']['note'] ?? '')?></textarea></label>
<div class="md:col-span-2 flex justify-between mt-2"><a href="/" class="px-4 py-2 rounded-lg border"><?=h(t('btn.prev'))?></a><button class="px-4 py-2 rounded-lg bg-sky-600 text-white"><?=h(t('btn.next'))?></button></div>
</form></div>
