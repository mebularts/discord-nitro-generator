
<?php require __DIR__.'/_auth.php'; require_once __DIR__.'/../app/db.php'; require_once __DIR__.'/../app/helpers.php'; require_once __DIR__.'/../app/Models/Setting.php';
use App\Models\Setting;
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $fields=['site.name','site.logo_url','site.url','theme.primary_color','seo.meta_description','footer.html','ads.header','ads.sidebar','ads.inline','ads.footer','custom.head_css','custom.head_js','custom.body_css','custom.body_js','i18n.default_locale','i18n.enabled_locales','deepl.api_key','pwa.theme_color','pwa.background_color'];
  foreach($fields as $f){
    $postKey = str_replace('.','_',$f);
    Setting::set($f, $_POST[$postKey] ?? '');
  }
  $msg='Ayarlar kaydedildi.';
}
$vals=[
  'site.name'=>Setting::get('site.name','SolveClone'),
  'site.logo_url'=>Setting::get('site.logo_url',''),
  'site.url'=>Setting::get('site.url',''),
  'theme.primary_color'=>Setting::get('theme.primary_color','#4f46e5'),
  'seo.meta_description'=>Setting::get('seo.meta_description',''),
  'footer.html'=>Setting::get('footer.html',''),
  'ads.header'=>Setting::get('ads.header',''),
  'ads.sidebar'=>Setting::get('ads.sidebar',''),
  'ads.inline'=>Setting::get('ads.inline',''),
  'ads.footer'=>Setting::get('ads.footer',''),
  'custom.head_css'=>Setting::get('custom.head_css',''),
  'custom.head_js'=>Setting::get('custom.head_js',''),
  'custom.body_css'=>Setting::get('custom.body_css',''),
  'custom.body_js'=>Setting::get('custom.body_js',''),
  'i18n.default_locale'=>Setting::get('i18n.default_locale','tr'),
  'i18n.enabled_locales'=>Setting::get('i18n.enabled_locales','["tr","en"]'),
  'deepl.api_key'=>Setting::get('deepl.api_key',''),
  'pwa.theme_color'=>Setting::get('pwa.theme_color','#4f46e5'),
  'pwa.background_color'=>Setting::get('pwa.background_color','#ffffff'),
];
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Ayarlar</title></head><body>
<div class="container py-4">
  <h1 class="h4 mb-3">Ayarlar</h1>
  <?php if($msg) echo '<div class="alert alert-success">'.$msg.'</div>'; ?>
  <form method="post" class="row g-3">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="col-md-4"><label class="form-label">Site Adı</label><input class="form-control" name="site_name" value="<?= h($vals['site.name']) ?>"></div>
    <div class="col-md-4"><label class="form-label">Logo URL</label><input class="form-control" name="site_logo_url" value="<?= h($vals['site.logo_url']) ?>"></div>
    <div class="col-md-4"><label class="form-label">Site URL</label><input class="form-control" name="site_url" placeholder="https://example.com" value="<?= h($vals['site.url']) ?>"></div>
    <div class="col-md-4"><label class="form-label">Tema Rengi</label><input class="form-control" name="theme_primary_color" value="<?= h($vals['theme.primary_color']) ?>"></div>

    <div class="col-12"><label class="form-label">Meta Açıklaması</label><input class="form-control" name="seo_meta_description" value="<?= h($vals['seo.meta_description']) ?>"></div>
    <div class="col-12"><label class="form-label">Footer HTML</label><textarea class="form-control" rows="2" name="footer_html"><?= h($vals['footer.html']) ?></textarea></div>

    <div class="col-md-6"><label class="form-label">Header Reklam</label><textarea class="form-control" rows="3" name="ads_header"><?= h($vals['ads.header']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Sidebar Reklam</label><textarea class="form-control" rows="3" name="ads_sidebar"><?= h($vals['ads.sidebar']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">İçerik İçi Reklam</label><textarea class="form-control" rows="3" name="ads_inline"><?= h($vals['ads.inline']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Footer Reklam</label><textarea class="form-control" rows="3" name="ads_footer"><?= h($vals['ads.footer']) ?></textarea></div>

    <div class="col-md-6"><label class="form-label">Custom Head CSS</label><textarea class="form-control" rows="3" name="custom_head_css"><?= h($vals['custom.head_css']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Custom Head JS</label><textarea class="form-control" rows="3" name="custom_head_js"><?= h($vals['custom.head_js']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Custom Body CSS</label><textarea class="form-control" rows="3" name="custom_body_css"><?= h($vals['custom.body_css']) ?></textarea></div>
    <div class="col-md-6"><label class="form-label">Custom Body JS</label><textarea class="form-control" rows="3" name="custom_body_js"><?= h($vals['custom.body_js']) ?></textarea></div>

    <div class="col-md-4"><label class="form-label">Varsayılan Dil</label><input class="form-control" name="i18n_default_locale" value="<?= h($vals['i18n.default_locale']) ?>"></div>
    <div class="col-md-8"><label class="form-label">Etkin Diller (JSON)</label><input class="form-control" name="i18n_enabled_locales" value="<?= h($vals['i18n.enabled_locales']) ?>"></div>

    <div class="col-md-6"><label class="form-label">DeepL API Key</label><input class="form-control" name="deepl_api_key" value="<?= h($vals['deepl.api_key']) ?>"></div>
    <div class="col-md-3"><label class="form-label">PWA Theme Color</label><input class="form-control" name="pwa_theme_color" value="<?= h($vals['pwa.theme_color']) ?>"></div>
    <div class="col-md-3"><label class="form-label">PWA BG Color</label><input class="form-control" name="pwa_background_color" value="<?= h($vals['pwa.background_color']) ?>"></div>

    <div class="col-12"><button class="btn btn-primary">Kaydet</button></div>
  </form>
</div>
</body></html>
