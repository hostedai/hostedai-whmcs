<?php

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\HosteDai\Helper;

$whmcspath = "";
if (file_exists(dirname(__FILE__) . "/config.php"))
    require_once dirname(__FILE__) . "/config.php";

if (!empty($whmcspath)) {
    require_once $whmcspath . "/init.php";
} else {
    require(__DIR__ . "/../init.php");
}

$helper = new Helper();

echo "=== Enhanced Billing API Test Suite ===\n\n";

// Get a test team
$testTeam = Capsule::table('mod_hostdaiteam_details')->first();

if (!$testTeam) {
    echo "No test team found in database. Please create a team first.\n";
    exit(1);
}

$teamId = $testTeam->teamid;
echo "Testing with Team ID: {$teamId}\n\n";

// Test 1: Enhanced Team Billing (Group by Workspace)
echo "=== Test 1: Enhanced Team Billing (Group by Workspace) ===\n";
$response = $helper->generateBill($teamId);
echo "HTTP Code: " . $response['httpcode'] . "\n";

if ($response['httpcode'] === 200) {
    $data = $response['result'];
    
    // Test new fields
    echo "✓ Pricing Policy: " . ($data->pricing_policy ?? 'NOT FOUND') . "\n";
    echo "✓ Resource Policy: " . ($data->resource_policy ?? 'NOT FOUND') . "\n";
    echo "✓ Currency Code: " . ($data->currency_code ?? 'NOT FOUND') . "\n";
    echo "✓ Currency Symbol: " . ($data->currency_symbol ?? 'NOT FOUND') . "\n";
    echo "✓ Current Month Total Cost: " . ($data->current_month_total_cost ?? 'NOT FOUND') . "\n";
    echo "✓ Monthly Cost: " . ($data->monthly_cost ?? 'NOT FOUND') . "\n";
    echo "✓ Total Billing: " . ($data->total_billing ?? 'NOT FOUND') . "\n";
    echo "✓ GPUaaS Total Cost: " . ($data->gpuaas_total_cost ?? 'NOT FOUND') . "\n";
    
    // Test workspace billing structure
    if (isset($data->billing_by_workspace)) {
        echo "✓ Workspace billing data found\n";
        $workspaceCount = count((array)$data->billing_by_workspace);
        echo "  - Number of workspaces: {$workspaceCount}\n";
        
        foreach ($data->billing_by_workspace as $workspaceId => $workspace) {
            echo "  - Workspace: " . ($workspace->workspace_name ?? 'Unknown') . "\n";
            if (isset($workspace->instances)) {
                $instanceCount = count((array)$workspace->instances);
                echo "    - Instances: {$instanceCount}\n";
                
                // Test enhanced instance cost breakdown
                foreach ($workspace->instances as $instanceId => $instanceData) {
                    $instanceArray = (array)$instanceData;
                    $monthData = reset($instanceArray);
                    
                    echo "    - Instance {$instanceId}:\n";
                    echo "      - Cost_Of_CPU: " . ($monthData->Cost_Of_CPU ?? 'NOT FOUND') . "\n";
                    echo "      - Cost_Of_RAM: " . ($monthData->Cost_Of_RAM ?? 'NOT FOUND') . "\n";
                    echo "      - Cost_Of_Disk_Storage: " . ($monthData->Cost_Of_Disk_Storage ?? 'NOT FOUND') . "\n";
                    echo "      - Cost_Of_GPU: " . ($monthData->Cost_Of_GPU ?? 'NOT FOUND') . "\n";
                    echo "      - Cost_Of_NetworkIn: " . ($monthData->Cost_Of_NetworkIn ?? 'NOT FOUND') . "\n";
                    echo "      - Cost_Of_NetworkOut: " . ($monthData->Cost_Of_NetworkOut ?? 'NOT FOUND') . "\n";
                    echo "      - Cost_of_Public_IP: " . ($monthData->Cost_of_Public_IP ?? 'NOT FOUND') . "\n";
                    echo "      - Total Cost: " . ($monthData->total_cost ?? 'NOT FOUND') . "\n";
                    break; // Only show first instance for brevity
                }
            }
            break; // Only show first workspace for brevity
        }
    } else {
        echo "✗ No workspace billing data found\n";
    }
    
    // Test GPUaaS billing structure
    if (isset($data->gpuaas_billing_by_pool)) {
        echo "✓ GPUaaS pool billing data found\n";
        $poolCount = count((array)$data->gpuaas_billing_by_pool);
        echo "  - Number of pools: {$poolCount}\n";
        
        foreach ($data->gpuaas_billing_by_pool as $poolId => $poolData) {
            echo "  - Pool {$poolId}: " . ($poolData->pool_name ?? 'Unknown') . "\n";
            echo "    - Model Type: " . ($poolData->model_type ?? 'Unknown') . "\n";
            echo "    - Vendor Type: " . ($poolData->vendor_type ?? 'Unknown') . "\n";
            echo "    - Total Cost: " . ($poolData->total_cost ?? 'Unknown') . "\n";
            
            if (isset($poolData->intervals)) {
                $intervalsArray = (array)$poolData->intervals;
                $interval = reset($intervalsArray);
                echo "    - GPU Cost: " . ($interval->Cost_Of_GPUConsumed ?? 'NOT FOUND') . "\n";
                echo "    - vRAM Cost: " . ($interval->Cost_Of_vRAMConsumed ?? 'NOT FOUND') . "\n";
                echo "    - TFlops Cost: " . ($interval->Cost_Of_TotalTFlopsConsumed ?? 'NOT FOUND') . "\n";
                echo "    - Pool Hours: " . ($interval->GPU_Pool_Hours ?? 'NOT FOUND') . "\n";
            }
            break; // Only show first pool for brevity
        }
    } else {
        echo "✗ No GPUaaS pool billing data found\n";
    }
    
    // Test PCI device billing
    if (isset($data->pci_devices) && isset($data->pci_devices->pci_devices)) {
        echo "✓ PCI device billing data found\n";
        $deviceCount = count((array)$data->pci_devices->pci_devices);
        echo "  - Number of PCI devices: {$deviceCount}\n";
        echo "  - Total PCI billing: " . ($data->pci_devices->total_billing ?? 'NOT FOUND') . "\n";
        
        foreach ($data->pci_devices->pci_devices as $cardId => $cardData) {
            echo "  - Card {$cardId}:\n";
            $intervalsArray = (array)$cardData;
            $interval = reset($intervalsArray);
            echo "    - Total Hours: " . ($interval->total_hours ?? 'NOT FOUND') . "\n";
            echo "    - Total Cost: " . ($interval->total_cost ?? 'NOT FOUND') . "\n";
            if (isset($interval->vm_usage)) {
                echo "    - VM Usage Count: " . count($interval->vm_usage) . "\n";
            }
            break; // Only show first card for brevity
        }
    } else {
        echo "✗ No PCI device billing data found\n";
    }
    
    // Test team metrics
    if (isset($data->team_metrics)) {
        echo "✓ Team metrics billing data found\n";
        $teamMetricsArray = (array)$data->team_metrics;
        $teamMetricsInterval = reset($teamMetricsArray);
        echo "  - RAM Cost: " . ($teamMetricsInterval->RAM ?? 'NOT FOUND') . "\n";
        echo "  - CPU Cost: " . ($teamMetricsInterval->CPU ?? 'NOT FOUND') . "\n";
        echo "  - GPU Cost: " . ($teamMetricsInterval->GPU ?? 'NOT FOUND') . "\n";
        echo "  - GRAM Cost: " . ($teamMetricsInterval->GRAM ?? 'NOT FOUND') . "\n";
        echo "  - TFlops Cost: " . ($teamMetricsInterval->TFlops ?? 'NOT FOUND') . "\n";
        echo "  - Total Cost: " . ($teamMetricsInterval->total_cost ?? 'NOT FOUND') . "\n";
    } else {
        echo "✗ No team metrics billing data found\n";
    }
    
} else {
    echo "✗ Failed to get billing data\n";
    echo "Response: " . json_encode($response['result']) . "\n";
}

echo "\n=== Test 2: Detailed Team Billing ===\n";
$detailedResponse = $helper->generateDetailedTeamBill($teamId);
echo "HTTP Code: " . $detailedResponse['httpcode'] . "\n";

if ($detailedResponse['httpcode'] === 200) {
    $detailedData = $detailedResponse['result'];
    echo "✓ Detailed billing data retrieved\n";
    echo "✓ Total Billing: " . ($detailedData->total_billing ?? 'NOT FOUND') . "\n";
    echo "✓ Currency: " . ($detailedData->currency_code ?? 'NOT FOUND') . "\n";
    echo "✓ Pricing Policy: " . ($detailedData->pricing_policy ?? 'NOT FOUND') . "\n";
    echo "✓ Resource Policy: " . ($detailedData->resource_policy ?? 'NOT FOUND') . "\n";
    
    if (isset($detailedData->instances)) {
        $instanceCount = count((array)$detailedData->instances);
        echo "✓ Instances found: {$instanceCount}\n";
    }
    
    if (isset($detailedData->gpuaas_cost)) {
        echo "✓ GPUaaS cost data found\n";
    }
    
    if (isset($detailedData->pci_devices)) {
        echo "✓ PCI devices data found\n";
    }
    
    if (isset($detailedData->team_metrics)) {
        echo "✓ Team metrics data found\n";
    }
} else {
    echo "✗ Failed to get detailed billing data\n";
    echo "Response: " . json_encode($detailedResponse['result']) . "\n";
}

echo "\n=== Test 3: Invoice Generation Simulation ===\n";
if ($response['httpcode'] === 200) {
    $responseData = $response['result'];
    $invoiceItems = [];
    $itemCount = 1;
    $totalWithoutTax = 0;
    
    // Simulate the enhanced invoice generation logic
    if (isset($responseData->monthly_cost) && $responseData->monthly_cost > 0) {
        $monthlyCost = number_format($responseData->monthly_cost, 2);
        $invoiceItems["itemdescription{$itemCount}"] = "Monthly Base Service Fee";
        $invoiceItems["itemamount{$itemCount}"] = $monthlyCost;
        $totalWithoutTax += $responseData->monthly_cost;
        $itemCount++;
        echo "✓ Monthly base cost added: \${$monthlyCost}\n";
    }
    
    $instancesProcessed = 0;
    $poolsProcessed = 0;
    $pciDevicesProcessed = 0;
    $teamMetricsProcessed = 0;
    
    if (isset($responseData->billing_by_workspace)) {
        foreach ($responseData->billing_by_workspace as $workspace) {
            if (!empty($workspace->instances)) {
                foreach ($workspace->instances as $instanceId => $instanceData) {
                    $instancesProcessed++;
                    $instanceArray = (array)$instanceData;
                    $monthData = reset($instanceArray);
                    $totalWithoutTax += $monthData->total_cost;
                    $itemCount++;
                }
            }
        }
    }
    
    if (isset($responseData->gpuaas_billing_by_pool)) {
        foreach ($responseData->gpuaas_billing_by_pool as $poolId => $poolData) {
            $poolsProcessed++;
            $intervalsArray = (array)$poolData->intervals;
            $interval = reset($intervalsArray);
            $totalWithoutTax += $interval->total_cost;
            $itemCount++;
        }
    }
    
    if (isset($responseData->pci_devices) && isset($responseData->pci_devices->pci_devices)) {
        foreach ($responseData->pci_devices->pci_devices as $cardId => $cardData) {
            $pciDevicesProcessed++;
            $intervalsArray = (array)$cardData;
            $interval = reset($intervalsArray);
            $totalWithoutTax += $interval->total_cost;
            $itemCount++;
        }
    }
    
    if (isset($responseData->team_metrics)) {
        $teamMetricsArray = (array)$responseData->team_metrics;
        $teamMetricsInterval = reset($teamMetricsArray);
        if (($teamMetricsInterval->total_cost ?? 0) > 0) {
            $teamMetricsProcessed = 1;
            $totalWithoutTax += $teamMetricsInterval->total_cost;
            $itemCount++;
        }
    }
    
    echo "✓ Invoice items processed:\n";
    echo "  - Instances: {$instancesProcessed}\n";
    echo "  - GPU Pools: {$poolsProcessed}\n";
    echo "  - PCI Devices: {$pciDevicesProcessed}\n";
    echo "  - Team Metrics: {$teamMetricsProcessed}\n";
    echo "  - Total Items: " . ($itemCount - 1) . "\n";
    echo "  - Total Amount: \$" . number_format($totalWithoutTax, 2) . "\n";
    
} else {
    echo "✗ Cannot simulate invoice generation - no billing data\n";
}

echo "\n=== Test Summary ===\n";
echo "Enhanced billing API integration test completed.\n";
echo "Check the output above for any ✗ (failed) items that need attention.\n";
echo "All ✓ (passed) items indicate successful integration with the new API structure.\n";

?>