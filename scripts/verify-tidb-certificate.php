<?php

// CLI-only, read-only build check. This does not connect to the database.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$fail = static function (string $message): never {
    fwrite(STDERR, 'TiDB certificate check failed: '.$message.PHP_EOL);
    exit(1);
};

$path = dirname(__DIR__).'/certs/isrgrootx1.pem';

if (! is_file($path) || ! is_readable($path)) {
    $fail('certs/isrgrootx1.pem is absent or unreadable. Include it in the deployed files.');
}

if (! extension_loaded('openssl')) {
    $fail('The PHP OpenSSL extension is required.');
}

$pem = file_get_contents($path);

if (! is_string($pem) || ! str_contains($pem, '-----BEGIN CERTIFICATE-----')) {
    $fail('The certificate file must contain PEM certificate text, not an HTML page.');
}

$certificate = openssl_x509_read($pem);

if ($certificate === false) {
    $fail('OpenSSL cannot parse the certificate.');
}

$details = openssl_x509_parse($certificate);

if ($details === false || ($details['subject']['CN'] ?? '') !== 'ISRG Root X1') {
    $fail('The bundled certificate is not ISRG Root X1.');
}

$expectedFingerprint = '96bcec06264976f37460779acf28c5a7cfe8a3c0aae11a8ffcee05c0bddf08c6';
$actualFingerprint = openssl_x509_fingerprint($certificate, 'sha256');

if (! is_string($actualFingerprint)
    || ! hash_equals($expectedFingerprint, strtolower($actualFingerprint))) {
    $fail('The bundled certificate fingerprint does not match ISRG Root X1.');
}

if (($details['validFrom_time_t'] ?? PHP_INT_MAX) > time()
    || ($details['validTo_time_t'] ?? 0) <= time()) {
    $fail('The certificate is outside its validity period.');
}

fwrite(STDOUT, 'TiDB certificate check passed: ISRG Root X1 PEM is present and valid.'.PHP_EOL);
