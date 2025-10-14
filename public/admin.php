<?php
require __DIR__ . '/../bot/bootstrap.php';

use App\Models\PaymentRepository;
use App\Models\ServiceRepository;
use App\Models\SettingRepository;
use App\Models\UserRepository;
use App\Services\CatalogSyncService;
use App\Support\Config;

$token = Config::get('admin.panel_token');
if (!$token || ($_GET['token'] ?? '') !== $token) {
    http_response_code(401);
    echo 'Yetkisiz erişim.';
    exit;
}

$settings = new SettingRepository();
$users = new UserRepository();
$payments = new PaymentRepository();
$services = new ServiceRepository();
$catalog = new CatalogSyncService();
$noticeParam = $_GET['notice'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $redirectNotice = null;
    if ($section === 'telegram') {
        $settings->set('telegram.bot_token', $_POST['bot_token'] ?? '');
        $settings->set('telegram.admin_chat_id', $_POST['admin_chat_id'] ?? '');
        $settings->set('telegram.webhook_secret', $_POST['webhook_secret'] ?? '');
    } elseif ($section === 'providers') {
        $settings->set('providers.preferred', $_POST['preferred'] ?? '5sim');
        $settings->set('providers.5sim.api_key', $_POST['fivesim_api_key'] ?? '');
        $settings->set('providers.sms_activate.api_key', $_POST['sms_activate_api_key'] ?? '');
    } elseif ($section === 'payments') {
        $paymentsConfig = Config::get('payments');
        foreach ($paymentsConfig as $method => $options) {
            $enabled = isset($_POST['enabled'][$method]);
            $settings->set("payments.$method.enabled", $enabled);
        }
        $settings->set('payments.iban.iban', $_POST['iban_iban'] ?? '');
        $settings->set('payments.iban.holder', $_POST['iban_holder'] ?? '');
        $settings->set('payments.crypto.address', $_POST['crypto_address'] ?? '');
        $settings->set('payments.online_crypto.provider', $_POST['online_crypto_provider'] ?? '');
        $settings->set('payments.telegram_stars.provider_token', $_POST['telegram_provider_token'] ?? '');
        $settings->set('payments.telegram_stars.title', $_POST['telegram_stars_title'] ?? '');
        $settings->set('payments.telegram_stars.description', $_POST['telegram_stars_description'] ?? '');
        $package = [
            'label' => $_POST['telegram_stars_package_label'] ?? '100 Yıldız',
            'stars' => (int) ($_POST['telegram_stars_package_stars'] ?? 100),
            'price' => (float) ($_POST['telegram_stars_package_price'] ?? 100),
        ];
        $settings->set('payments.telegram_stars.packages', json_encode([$package]));
        $settings->set('payments.nowpayments.api_key', $_POST['nowpayments_api_key'] ?? '');
        $settings->set('payments.nowpayments.price_currency', $_POST['nowpayments_price_currency'] ?? 'USD');
        $settings->set('payments.nowpayments.pay_currency', $_POST['nowpayments_pay_currency'] ?? 'USDT');
        $settings->set('payments.nowpayments.default_amount', (float) ($_POST['nowpayments_default_amount'] ?? 100));
        $settings->set('payments.nowpayments.success_url', $_POST['nowpayments_success_url'] ?? '');
        $settings->set('payments.nowpayments.cancel_url', $_POST['nowpayments_cancel_url'] ?? '');
        $settings->set('payments.nowpayments.ipn_secret', $_POST['nowpayments_ipn_secret'] ?? '');
    } elseif ($section === 'pricing') {
        $settings->set('catalog.markup_percent', (float) ($_POST['markup_percent'] ?? 0));
        $settings->set('catalog.markup_fixed', (float) ($_POST['markup_fixed'] ?? 0));
    } elseif ($section === 'catalog') {
        try {
            $catalog->syncAll();
            $redirectNotice = 'catalog_synced';
        } catch (\Throwable $exception) {
            $redirectNotice = 'catalog_failed';
        }

        header('Location: admin.php?token=' . urlencode($token) . ($redirectNotice ? '&notice=' . urlencode($redirectNotice) : ''));
        exit;
    }

    header('Location: admin.php?token=' . urlencode($token) . ($redirectNotice ? '&notice=' . urlencode($redirectNotice) : ''));
    exit;
}

$currentConfig = Config::all();
$allUsers = $users->all();
$allPayments = $payments->all();
$allServices = $services->all();
$lastSync = Config::get('catalog.last_sync');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Telegram SMS Mağaza Admin Paneli</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f6f7fb; }
        h1 { color: #333; }
        section { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 16px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        .checkbox-group { display: flex; gap: 16px; flex-wrap: wrap; }
        .notice { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
        .notice-success { background: #d1fae5; color: #065f46; }
        .notice-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<h1>Admin Paneli</h1>
<?php if ($noticeParam): ?>
    <div class="notice <?= $noticeParam === 'catalog_synced' ? 'notice-success' : 'notice-error' ?>">
        <?= $noticeParam === 'catalog_synced' ? 'Katalog başarıyla senkronize edildi.' : 'Katalog senkronizasyonu başarısız oldu.' ?>
    </div>
<?php endif; ?>
<section>
    <h2>Telegram Ayarları</h2>
    <form method="post">
        <input type="hidden" name="section" value="telegram">
        <label>Bot Token</label>
        <input type="password" name="bot_token" value="<?= htmlspecialchars($currentConfig['telegram']['bot_token'] ?? '') ?>">
        <label>Admin Chat ID</label>
        <input type="text" name="admin_chat_id" value="<?= htmlspecialchars($currentConfig['telegram']['admin_chat_id'] ?? '') ?>">
        <label>Webhook Secret</label>
        <input type="text" name="webhook_secret" value="<?= htmlspecialchars($currentConfig['telegram']['webhook_secret'] ?? '') ?>">
        <button type="submit">Kaydet</button>
    </form>
</section>

<section>
    <h2>SMS Sağlayıcıları</h2>
    <form method="post">
        <input type="hidden" name="section" value="providers">
        <label>Varsayılan Sağlayıcı</label>
        <input type="text" name="preferred" value="<?= htmlspecialchars($currentConfig['providers']['preferred'] ?? '') ?>">
        <label>5Sim API Anahtarı</label>
        <input type="password" name="fivesim_api_key" value="<?= htmlspecialchars($currentConfig['providers']['5sim']['api_key'] ?? '') ?>">
        <label>Sms-Activate API Anahtarı</label>
        <input type="password" name="sms_activate_api_key" value="<?= htmlspecialchars($currentConfig['providers']['sms_activate']['api_key'] ?? '') ?>">
        <button type="submit">Kaydet</button>
    </form>
</section>

<section>
    <h2>Fiyatlandırma</h2>
    <form method="post">
        <input type="hidden" name="section" value="pricing">
        <label>Komisyon Yüzdesi (%)</label>
        <input type="number" step="0.01" name="markup_percent" value="<?= htmlspecialchars($currentConfig['catalog']['markup_percent'] ?? 0) ?>">
        <label>Sabit Ücret (₺)</label>
        <input type="number" step="0.01" name="markup_fixed" value="<?= htmlspecialchars($currentConfig['catalog']['markup_fixed'] ?? 0) ?>">
        <p>Satış fiyatı = Sağlayıcı fiyatı + (Sağlayıcı fiyatı × % komisyon) + sabit ücret.</p>
        <button type="submit">Kaydet</button>
    </form>
</section>

<section>
    <h2>Ödeme Yöntemleri</h2>
    <form method="post">
        <input type="hidden" name="section" value="payments">
        <div class="checkbox-group">
            <?php foreach (($currentConfig['payments'] ?? []) as $method => $options): ?>
                <label><input type="checkbox" name="enabled[<?= $method ?>]" <?= !empty($options['enabled']) ? 'checked' : '' ?>> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $method))) ?></label>
            <?php endforeach; ?>
        </div>
        <h3>Telegram Stars</h3>
        <label>Sağlayıcı Token</label>
        <input type="text" name="telegram_provider_token" value="<?= htmlspecialchars($currentConfig['payments']['telegram_stars']['provider_token'] ?? '') ?>">
        <label>Başlık</label>
        <input type="text" name="telegram_stars_title" value="<?= htmlspecialchars($currentConfig['payments']['telegram_stars']['title'] ?? '') ?>">
        <label>Açıklama</label>
        <input type="text" name="telegram_stars_description" value="<?= htmlspecialchars($currentConfig['payments']['telegram_stars']['description'] ?? '') ?>">
        <?php $package = $currentConfig['payments']['telegram_stars']['packages'][0] ?? ['label' => '100 Yıldız', 'stars' => 100, 'price' => 100]; ?>
        <label>Paket Etiketi</label>
        <input type="text" name="telegram_stars_package_label" value="<?= htmlspecialchars($package['label'] ?? '') ?>">
        <label>Yıldız Adedi</label>
        <input type="number" name="telegram_stars_package_stars" value="<?= htmlspecialchars($package['stars'] ?? 100) ?>">
        <label>Varsayılan Fiyat</label>
        <input type="number" step="0.01" name="telegram_stars_package_price" value="<?= htmlspecialchars($package['price'] ?? 100) ?>">
        <label>IBAN</label>
        <input type="text" name="iban_iban" value="<?= htmlspecialchars($currentConfig['payments']['iban']['iban'] ?? '') ?>">
        <label>IBAN Hesap Sahibi</label>
        <input type="text" name="iban_holder" value="<?= htmlspecialchars($currentConfig['payments']['iban']['holder'] ?? '') ?>">
        <label>Kripto Cüzdan Adresi</label>
        <input type="text" name="crypto_address" value="<?= htmlspecialchars($currentConfig['payments']['crypto']['address'] ?? '') ?>">
        <label>Online Kripto Sağlayıcısı</label>
        <input type="text" name="online_crypto_provider" value="<?= htmlspecialchars($currentConfig['payments']['online_crypto']['provider'] ?? '') ?>">
        <h3>NowPayments</h3>
        <label>API Anahtarı</label>
        <input type="text" name="nowpayments_api_key" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['api_key'] ?? '') ?>">
        <label>Fiyat Para Birimi</label>
        <input type="text" name="nowpayments_price_currency" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['price_currency'] ?? 'USD') ?>">
        <label>Ödeme Para Birimi</label>
        <input type="text" name="nowpayments_pay_currency" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['pay_currency'] ?? 'USDT') ?>">
        <label>Varsayılan Tutar</label>
        <input type="number" step="0.01" name="nowpayments_default_amount" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['default_amount'] ?? 100) ?>">
        <label>Başarılı URL</label>
        <input type="text" name="nowpayments_success_url" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['success_url'] ?? '') ?>">
        <label>İptal URL</label>
        <input type="text" name="nowpayments_cancel_url" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['cancel_url'] ?? '') ?>">
        <label>IPN Gizli Anahtarı</label>
        <input type="text" name="nowpayments_ipn_secret" value="<?= htmlspecialchars($currentConfig['payments']['nowpayments']['ipn_secret'] ?? '') ?>">
        <button type="submit">Kaydet</button>
    </form>
</section>

<section>
    <h2>Katalog Senkronizasyonu</h2>
    <p>Son senkronizasyon: <?= $lastSync ? htmlspecialchars($lastSync) : 'Henüz senkronize edilmedi' ?></p>
    <form method="post">
        <input type="hidden" name="section" value="catalog">
        <button type="submit">Sağlayıcıdan Yenile</button>
    </form>
</section>

<section>
    <h2>Kullanıcılar</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Telegram ID</th>
            <th>Ad</th>
            <th>Bakiye</th>
            <th>Katılma Tarihi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allUsers as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['telegram_id']) ?></td>
                <td><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></td>
                <td><?= number_format((float) $user['balance'], 2) ?>₺</td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section>
    <h2>Ödemeler</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Kullanıcı</th>
            <th>Yöntem</th>
            <th>Tutar</th>
            <th>Durum</th>
            <th>Oluşturulma</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allPayments as $payment): ?>
            <tr>
                <td><?= $payment['id'] ?></td>
                <td><?= htmlspecialchars($payment['telegram_id']) ?></td>
                <td><?= htmlspecialchars($payment['method']) ?></td>
                <td><?= number_format((float) $payment['amount'], 2) ?>₺</td>
                <td><?= htmlspecialchars($payment['status']) ?></td>
                <td><?= htmlspecialchars($payment['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section>
    <h2>Servisler</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Ad</th>
            <th>Sağlayıcı</th>
            <th>Sağlayıcı Servis ID</th>
            <th>Taban Fiyat</th>
            <th>Popüler</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allServices as $service): ?>
            <tr>
                <td><?= $service['id'] ?></td>
                <td><?= htmlspecialchars($service['name']) ?></td>
                <td><?= htmlspecialchars($service['provider']) ?></td>
                <td><?= htmlspecialchars($service['provider_service_id']) ?></td>
                <td><?= number_format((float) $service['base_price'], 2) ?>₺</td>
                <td><?= $service['popular'] ? 'Evet' : 'Hayır' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
</body>
</html>
