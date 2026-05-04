<?php
require __DIR__.'/vendor/autoload.php';
putenv('APP_KEY=base64:SEO6abhlN0T8afteBjk8kuo/C9pFo3DXELVaMPc5LYw=');
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$baseUrl = 'https://www.tubagusalwasii.my.id';

// Get login page for CSRF/cookies
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
$headers = substr($response, 0, $headerSize);
curl_close($ch);

preg_match('/\/(livewire-[a-f0-9]{8})\//', $body, $lwMatches);
$lwPrefix = $lwMatches[1] ?? 'unknown';
echo "Livewire prefix: $lwPrefix\n";

// Test upload endpoint
$uploadUrl = $baseUrl . '/' . $lwPrefix . '/upload-file';
echo "Upload URL: $uploadUrl\n\n";

$tmpFile = tempnam(sys_get_temp_dir(), 'test');
file_put_contents($tmpFile, str_repeat("X", 1024));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uploadUrl);
curl_setopt($ch, CURLOPT_POST, true);
$cfile = new CURLFile($tmpFile, 'image/jpeg', 'test.jpg');
curl_setopt($ch, CURLOPT_POSTFIELDS, ['files[0]' => $cfile]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$respHeaders = substr($response, 0, $headerSize);
$respBody = substr($response, $headerSize);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response Headers:\n$respHeaders\n";
echo "Response Body: " . substr($respBody, 0, 1000) . "\n";

unlink($tmpFile);
