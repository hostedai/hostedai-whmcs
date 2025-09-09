<?php
// Test API with UI date range
$api_url = "https://user20.hostedai.dev/api";
$team_id = "b1d6f577-33dd-4b50-9925-46cfbc8bf779";
$start_date = "2024-10-01";
$end_date = "2025-09-30";

echo "Testing UI date range...\n";
echo "Team ID: {$team_id}\n";
echo "Date Range: {$start_date} to {$end_date}\n\n";

// Test main billing endpoint
$main_url = "{$api_url}/team-billing/group-by-workspace/{$team_id}/{$start_date}/{$end_date}/monthly";
echo "Main Billing URL: {$main_url}\n";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $main_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_TOKEN', // Replace with actual token
    'Content-Type: application/json'
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: {$http_code}\n";
$data = json_decode($response, true);
echo "Total Billing: " . ($data['total_billing'] ?? 'Not found') . "\n";
echo "GPUaaS Total: " . ($data['gpuaas_total_cost'] ?? 'Not found') . "\n";

if ($http_code === 200 && isset($data['total_billing']) && $data['total_billing'] > 0) {
    echo "\n✅ SUCCESS: API returns billing data with UI date range!\n";
    echo "Expected invoice amount: $" . $data['total_billing'] . "\n";
} else {
    echo "\n❌ ISSUE: No billing data returned\n";
    echo "Response: " . substr($response, 0, 200) . "\n";
}
?>