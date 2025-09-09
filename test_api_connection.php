<?php
// Quick API connection test
$api_url = "YOUR_HOSTEDAI_API_URL"; // Replace with actual URL
$api_token = "YOUR_API_TOKEN"; // Replace with actual token
$team_id = "ae579208-35e3-406d-b970-b0ce85470cc5"; // From your logs

$test_endpoint = $api_url . "/team-billing/group-by-workspace/" . $team_id . "/2024-08-01/2024-08-31/monthly";

echo "Testing API connection...\n";
echo "URL: " . $test_endpoint . "\n";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $test_endpoint);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_token,
    'Content-Type: application/json'
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curl_error = curl_error($curl);

echo "HTTP Code: " . $http_code . "\n";
echo "CURL Error: " . ($curl_error ?: "None") . "\n";
echo "Response: " . substr($response, 0, 200) . "\n";

curl_close($curl);
?>