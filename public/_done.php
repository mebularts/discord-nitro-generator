<?php
// public/_done.php — modern “tamamlandı” sayfası (takvim görünümü + aksiyonlar)
$id  = (int)($_SESSION['last_id'] ?? 0);
$ap  = null;
if ($id) {
  $ap = q($pdo, "SELECT a.*, p.name provider FROM appointments a JOIN providers p ON p.id=a.provider_id WHERE a.id=?", [$id])->fetch();
}
?>

<?php if ($ap):
  $ts       = strtotime($ap['app_date'].' '.$ap['app_time']);
  $day      = date('d', $ts);
  $timeStr  = $ap['app_time'];
  $dateStr  = date('d.m.Y', strtotime($ap['app_date']));
  $weekTR   = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
  $weekday  = $weekTR[(int)date('N', $ts)-1];

  // Kısa ay adları (TR)
  $moTR = ['01'=>'Oca','02'=>'Şub','03'=>'Mar','04'=>'Nis','05'=>'May','06'=>'Haz','07'=>'Tem','08'=>'Ağu','09'=>'Eyl','10'=>'Eki','11'=>'Kas','12'=>'Ara'];
  $mo   = $moTR[date('m',$ts)] ?? date('M',$ts);
  $yr   = date('Y',$ts);

  // Basit .ics içeriği (30 dk varsayılan, gerekirse DB genel ayardan süreyı çekebilirsin)
  $dtStart = date('Ymd\THis', $ts);
  $dtEnd   = date('Ymd\THis', strtotime('+30 minutes', $ts));
  $summary = 'Randevu - '.$ap['provider'];
  $desc    = "Randevu: {$ap['provider']}\\nTarih: {$dateStr} {$timeStr}\\nNot: ".trim((string)$ap['note']);
  $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//RandevuPro//v2.0//TR\r\nBEGIN:VEVENT\r\nUID:appt-{$ap['id']}@randevupro\r\nDTSTAMP:".gmdate('Ymd\THis\Z')."\r\nDTSTART:{$dtStart}\r\nDTEND:{$dtEnd}\r\nSUMMARY:{$summary}\r\nDESCRIPTION:{$desc}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
  $icsHref = 'data:text/calendar;charset=utf-8,'.rawurlencode($ics);
?>

<div class="p-6">
  <!-- Üst başlık -->
  <div class="mb-6 flex items-center justify-center gap-3">
    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center ring-8 ring-emerald-50">✔</div>
    <h2 class="text-2xl font-semibold"><?= h(t('success.title')) ?></h2>
  </div>

  <div class="grid lg:grid-cols-3 gap-6">
    <!-- Sol: Takvim kartı -->
    <div class="bg-white rounded-2xl border shadow-sm p-5 relative overflow-hidden">
      <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-rose-50"></div>
      <div class="relative">
        <div class="text-sm text-slate-500 mb-3"><?= h($mo.' '.$yr) ?> • <?= h($weekday) ?></div>

        <div class="flex items-center gap-4">
          <!-- “Kırmızı yuvarlak” tarih -->
          <div class="w-24 h-24 rounded-full bg-rose-600 text-white flex flex-col items-center justify-center shadow-lg">
            <div class="text-lg leading-none"><?= h($mo) ?></div>
            <div class="text-3xl font-bold leading-tight"><?= h($day) ?></div>
          </div>

          <div class="flex-1">
            <div class="text-slate-500 text-sm">Randevu</div>
            <div class="text-xl font-semibold"><?= h($dateStr.' • '.$timeStr) ?></div>
            <div class="mt-1 text-slate-500 text-sm">Süre: 30 dk</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Orta: Detaylar -->
    <div class="bg-white rounded-2xl border shadow-sm p-5 lg:col-span-2">
      <div class="grid md:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-slate-50 border">
          <div class="text-xs text-slate-500">Randevu No</div>
          <div class="text-lg font-medium">#<?= (int)$ap['id'] ?></div>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border">
          <div class="text-xs text-slate-500">Randevu Veren</div>
          <div class="text-lg font-medium"><?= h($ap['provider']) ?></div>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border">
          <div class="text-xs text-slate-500">Tarih • Saat</div>
          <div class="text-lg font-medium"><?= h($dateStr.' • '.$timeStr) ?></div>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border md:col-span-3">
          <div class="text-xs text-slate-500 mb-1">Ad Soyad • İletişim</div>
          <div class="text-slate-700">
            <?= h($ap['full_name']) ?>
            <?php if(!empty($ap['phone'])): ?>
              <span class="text-slate-400"> • </span><a class="underline" href="tel:<?= h($ap['phone']) ?>"><?= h($ap['phone']) ?></a>
            <?php endif; ?>
            <?php if(!empty($ap['email'])): ?>
              <span class="text-slate-400"> • </span><a class="underline" href="mailto:<?= h($ap['email']) ?>"><?= h($ap['email']) ?></a>
            <?php endif; ?>
          </div>
          <?php if(!empty($ap['note'])): ?>
            <div class="text-xs text-slate-500 mt-2">Not: <span class="text-slate-700"><?= nl2br(h($ap['note'])) ?></span></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Aksiyonlar -->
      <div class="mt-5 flex flex-wrap gap-3">
        <a href="<?= $icsHref ?>" download="randevu-<?= (int)$ap['id'] ?>.ics"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border hover:border-sky-600">
          <span>Takvime ekle (.ics)</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border hover:border-sky-600">
          Yazdır / PDF
        </button>
        <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700" href="/">
          Yeni randevu oluştur
        </a>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
  <div class="p-8 text-center">
    <div class="text-slate-500 mb-4">Gösterilecek randevu bulunamadı.</div>
    <a class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700" href="/">Yeni randevu oluştur</a>
  </div>
<?php endif; ?>
