<div class="p-6"><h2 class="text-xl font-semibold mb-6">Randevu almak için bir personel seçin</h2>
<div class="grid md:grid-cols-3 gap-5">
<?php $rows=q($pdo,"SELECT id,name,image FROM providers WHERE active=1 ORDER BY sort,name")->fetchAll();
foreach($rows as $r): $img = ($r['image'] && file_exists(__DIR__.'/uploads/providers/'.$r['image'])) ? '/uploads/providers/'.$r['image'] : 'https://placehold.co/240x160?text=Personel'; ?>
  <div class="rounded-2xl border shadow-sm overflow-hidden group bg-white"><img src="<?=$img?>" alt="<?=h($r['name'])?>" class="h-40 w-full object-cover group-hover:scale-105 transition-transform"><div class="p-4"><div class="font-medium text-lg"><?=h($r['name'])?></div><div class="mt-3"><a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700" href="/?p=book&pid=<?=$r['id']?>">Randevu al</a></div></div></div>
<?php endforeach; if(empty($rows)) echo '<div class="p-6">Henüz personel tanımlı değil.</div>'; ?>
</div></div>
