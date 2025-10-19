<?php
declare(strict_types=1);
require_once __DIR__.'/helpers.php';

function send_email(PDO $pdo, string $to, string $subject, string $html): bool{
  if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    return false;
  }
  $smtp = smtp_settings($pdo);
  $from = $smtp['from'] ?: 'noreply@example.com';
  $fromName = $smtp['from_name'] ?: 'Randevu Sistemi';

  if (!empty($smtp['enabled']) && $smtp['host']) {
    return smtp_transport_send($smtp, $from, $fromName, $to, $subject, $html);
  }

  $headers = [];
  $headers[] = 'From: '.mime_encode_header($fromName).' <'.$from.'>';
  $headers[] = 'Reply-To: '.$from;
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-Type: text/html; charset=utf-8';
  $headers[] = 'Content-Transfer-Encoding: 8bit';
  return @mail($to, $subject, $html, implode("\r\n", $headers));
}

function mime_encode_header(string $text): string {
  if (preg_match('/[\x80-\xFF]/', $text)) {
    return '=?UTF-8?B?'.base64_encode($text).'?=';
  }
  return $text;
}

function smtp_transport_send(array $smtp, string $from, string $fromName, string $to, string $subject, string $html): bool {
  $host = $smtp['host'];
  $port = (int)($smtp['port'] ?? 587);
  $encryption = $smtp['encryption'] ?? 'tls';
  $username = $smtp['username'] ?? '';
  $password = $smtp['password'] ?? '';

  $transport = $host;
  $context = stream_context_create([
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
    ],
  ]);

  $remote = ($encryption === 'ssl' ? 'ssl://' : '').$transport.':'.$port;
  $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
  if (!$socket) {
    return false;
  }
  stream_set_timeout($socket, 15);

  $read = function () use ($socket) {
    $data = '';
    while (($line = fgets($socket, 515)) !== false) {
      $data .= $line;
      if (strlen($line) < 4 || $line[3] !== '-') break;
    }
    return $data;
  };

  $write = function (string $command) use ($socket) {
    fwrite($socket, $command."\r\n");
  };

  $expect = function ($codes) use ($read) {
    $response = $read();
    if ($response === '' || $response === false) return false;
    $code = substr($response, 0, 3);
    $codes = (array)$codes;
    return in_array($code, $codes, true);
  };

  if (!$expect(['220'])) {
    fclose($socket);
    return false;
  }

  $write('EHLO '.$host);
  if (!$expect(['250'])) {
    fclose($socket);
    return false;
  }

  if ($encryption === 'tls') {
    $write('STARTTLS');
    if (!$expect(['220'])) {
      fclose($socket);
      return false;
    }
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
      fclose($socket);
      return false;
    }
    $write('EHLO '.$host);
    if (!$expect(['250'])) {
      fclose($socket);
      return false;
    }
  }

  if ($username) {
    $write('AUTH LOGIN');
    if (!$expect(['334'])) { fclose($socket); return false; }
    $write(base64_encode($username));
    if (!$expect(['334'])) { fclose($socket); return false; }
    $write(base64_encode($password));
    if (!$expect(['235'])) { fclose($socket); return false; }
  }

  $write('MAIL FROM:<'.$from.'>');
  if (!$expect(['250'])) { fclose($socket); return false; }

  $write('RCPT TO:<'.$to.'>');
  if (!$expect(['250','251'])) { fclose($socket); return false; }

  $write('DATA');
  if (!$expect(['354'])) { fclose($socket); return false; }

  $headers = [];
  $headers[] = 'From: '.mime_encode_header($fromName).' <'.$from.'>';
  $headers[] = 'To: <'.$to.'>';
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-Type: text/html; charset=utf-8';
  $headers[] = 'Content-Transfer-Encoding: 8bit';

  $body = str_replace(["\r\n", "\r"], "\n", $html);
  $body = implode("\r\n", array_map(function ($line) {
    if (isset($line[0]) && $line[0] === '.') {
      return '.'.$line;
    }
    return $line;
  }, explode("\n", $body)));

  $message = 'Subject: '.mime_encode_header($subject)."\r\n".implode("\r\n", $headers)."\r\n\r\n".$body."\r\n.";
  $write($message);
  if (!$expect(['250'])) { fclose($socket); return false; }

  $write('QUIT');
  fclose($socket);
  return true;
}

function send_sms(PDO $pdo, string $to, string $message): array{
  $sms = sms_settings($pdo);
  $provider = $sms['provider'] ?? 'netgsm';
  $username = $sms['username'] ?? '';
  $password = $sms['password'] ?? '';
  $header   = $sms['header'] ?? '';
  $result = ['ok' => false, 'provider' => $provider, 'response' => null];
  $message = trim($message);
  if ($message === '') {
    return $result;
  }
  if ($provider === 'netgsm') {
    $url = 'https://api.netgsm.com.tr/sms/send/get/';
    $params = [
      'usercode' => $username,
      'password' => $password,
      'gsmno'    => $to,
      'message'  => $message,
      'msgheader'=> $header,
      'dil'      => 'TR',
    ];
    $resp = @file_get_contents($url.'?'.http_build_query($params));
    $result['response'] = $resp;
    if ($resp !== false) {
      $trim = trim((string)$resp);
      $result['ok'] = preg_match('/^(00|20|30)/', $trim) === 1;
    }
  } elseif ($provider === 'iletimerkezi') {
    $url = 'https://api.iletimerkezi.com/v1/send-sms';
    $payload = [
      'request' => [
        'sender' => $header,
        'messages' => ['mb' => [['msg' => $message, 'phone' => $to]]],
      ],
      'auth' => [
        'username' => $username,
        'password' => $password,
      ],
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
      CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $result['response'] = $resp;
    if ($resp !== false && $resp !== null) {
      $result['ok'] = strpos((string)$resp, 'success') !== false;
    }
  }
  return $result;
}
