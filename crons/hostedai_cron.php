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

try {
    logActivity("HostedAI Cron started on " . date('Y-m-d H:i:s'));

    // Debug mode - set to true to run on any day for testing
    $debug_mode = true;
    
    if (date('d') == '01' || $debug_mode) {
        if ($debug_mode) {
            logActivity("DEBUG MODE: Running invoice generation on day " . date('d') . " instead of 1st");
        }

        // Generate bills and create invoices
        $teams = Capsule::table('mod_hostdaiteam_details')->get();

        foreach ($teams as $team) {
            // Debug: Log API configuration
            logActivity("DEBUG: Processing TeamID {$team->teamid}");
            logActivity("DEBUG: API Base URL: " . $helper->baseUrl);
            logActivity("DEBUG: API Token configured: " . (!empty($helper->token) ? 'Yes' : 'No'));
            
            $response = $helper->generateBill($team->teamid);
            logActivity("Billing response for TeamID {$team->teamid}: " . json_encode($response));

            // Always initialize invoice items regardless of main billing response
            $invoiceItems = [];
            $itemCount = 1;
            $totalWithoutTax = 0;
            $currencyCode = 'USD';
            $currencySymbol = '$';

            // Process main billing data if available
            if ($response['httpcode'] === 200) {
                $responseData = $response['result'];

                // Log enhanced billing information
                $pricingPolicy = $responseData->pricing_policy ?? 'Unknown';
                $resourcePolicy = $responseData->resource_policy ?? 'Unknown';
                $currencyCode = $responseData->currency_code ?? 'USD';
                $currencySymbol = $responseData->currency_symbol ?? '$';
                $currentMonthCost = $responseData->current_month_total_cost ?? 0;
                
                logActivity("Enhanced billing info for TeamID {$team->teamid} - Pricing Policy: {$pricingPolicy}, Resource Policy: {$resourcePolicy}, Currency: {$currencyCode}, Current Month Cost: {$currentMonthCost}");

                // Add monthly base cost if available
                if (isset($responseData->monthly_cost) && $responseData->monthly_cost > 0) {
                    $monthlyCost = number_format($responseData->monthly_cost, 2);
                    $invoiceItems["itemdescription{$itemCount}"] = "Monthly Base Service Fee";
                    $invoiceItems["itemamount{$itemCount}"] = $monthlyCost;
                    $invoiceItems["itemtaxed{$itemCount}"] = true;
                    $totalWithoutTax += $responseData->monthly_cost;
                    $itemCount++;
                }

                // Process workspace billing data if available
                if (isset($responseData->billing_by_workspace) && !empty($responseData->billing_by_workspace)) {
                    logActivity("Processing workspace billing data for TeamID {$team->teamid}");

                foreach ($responseData->billing_by_workspace as $workspace) {
                    $workspaceName = $workspace->workspace_name ?? 'Unknown Workspace';
                    if (empty($workspace->instances)) {
                        logActivity("No instances found for workspace: {$workspaceName}");
                        continue;
                    }
                
                    foreach ($workspace->instances as $instanceId => $instanceData) {
                        $instanceArray = (array)$instanceData;
                        $monthData = reset($instanceArray);
                
                        // Enhanced cost breakdown with new fields
                        $cpu = number_format($monthData->Cost_Of_CPU ?? 0, 2);
                        $ram = number_format($monthData->Cost_Of_RAM ?? 0, 2);
                        $disk = number_format($monthData->Cost_Of_Disk_Storage ?? 0, 2);
                        $gpu = number_format($monthData->Cost_Of_GPU ?? 0, 2);
                        $networkIn = number_format($monthData->Cost_Of_NetworkIn ?? 0, 2);
                        $networkOut = number_format($monthData->Cost_Of_NetworkOut ?? 0, 2);
                        $publicIP = number_format($monthData->Cost_of_Public_IP ?? 0, 2);
                        $instanceTotal = number_format($monthData->total_cost, 2);
                
                        $description = <<<DESC
                                        Workspace: {$workspaceName}
                                        Instance ID: {$instanceId}
                                        CPU ………………………………………………………… \$ {$cpu}
                                        RAM ………………………………………………………… \$ {$ram}
                                        Disk Storage ………………………………………… \$ {$disk}
                                        GPU ………………………………………………………… \$ {$gpu}
                                        Network In ……………………………………………… \$ {$networkIn}
                                        Network Out …………………………………………… \$ {$networkOut}
                                        Public IP ……………………………………………… \$ {$publicIP}
                                        DESC;
                
                        $invoiceItems["itemdescription{$itemCount}"] = $description;
                        $invoiceItems["itemamount{$itemCount}"] = $instanceTotal;
                        $invoiceItems["itemtaxed{$itemCount}"] = true;
                
                        $totalWithoutTax += $monthData->total_cost;
                        $itemCount++;
                    }

                // Add GPUaaS pool billing (if available)
                if (!empty($responseData->gpuaas_billing_by_pool)) {
                    foreach ($responseData->gpuaas_billing_by_pool as $poolId => $poolData) {
                        $poolName = $poolData->pool_name ?? "Pool {$poolId}";
                        $modelType = $poolData->model_type ?? 'N/A';
                        $vendorType = $poolData->vendor_type ?? 'N/A';
                        $intervalsArray = (array)$poolData->intervals;
                        $interval = reset($intervalsArray);

                        $gpuCost = number_format($interval->Cost_Of_GPUConsumed ?? 0, 2);
                        $vramCost = number_format($interval->Cost_Of_vRAMConsumed ?? 0, 2);
                        $tflopsCost = number_format($interval->Cost_Of_TotalTFlopsConsumed ?? 0, 2);
                        $poolHours = number_format($interval->GPU_Pool_Hours ?? 0, 2);
                        $totalCost = number_format($interval->total_cost, 2);

                        $description = <<<DESC
                        GPU Pool: {$poolName} ({$modelType} - {$vendorType})
                        GPU Subscription ........................ \$ {$gpuCost}
                        vRAM Consumption ........................ \$ {$vramCost}
                        TFlops Consumption ...................... \$ {$tflopsCost}
                        Pool Hours .............................. {$poolHours} hrs
                        DESC;

                        $invoiceItems["itemdescription{$itemCount}"] = $description;
                        $invoiceItems["itemamount{$itemCount}"] = $totalCost;
                        $invoiceItems["itemtaxed{$itemCount}"] = true;

                        $totalWithoutTax += $interval->total_cost;
                        $itemCount++;
                    }
                }

                // Add PCI Device (GPU Card) billing (if available)
                if (!empty($responseData->pci_devices) && isset($responseData->pci_devices->pci_devices)) {
                    foreach ($responseData->pci_devices->pci_devices as $cardId => $cardData) {
                        $intervalsArray = (array)$cardData;
                        $interval = reset($intervalsArray);
                        
                        $totalHoursDecimal = $interval->total_hours ?? 0;
                        $totalHoursFormatted = $helper->formatHoursMinutes($totalHoursDecimal);
                        $totalCost = number_format($interval->total_cost ?? 0, 2);
                        
                        // Get VM usage details
                        $vmUsageDetails = '';
                        if (!empty($interval->vm_usage)) {
                            foreach ($interval->vm_usage as $vmUsage) {
                                $vmId = $vmUsage->VMID ?? 'Unknown';
                                $vmHoursDecimal = $vmUsage->Hours ?? 0;
                                $vmHoursFormatted = $helper->formatHoursMinutes($vmHoursDecimal);
                                $vmCost = number_format($vmUsage->Cost ?? 0, 2);
                                $vmUsageDetails .= "\n                        VM {$vmId}: {$vmHoursFormatted} (\${$vmCost})";
                            }
                        }

                        $description = <<<DESC
                        GPU Card: {$cardId}
                        Total Hours ............................. {$totalHoursFormatted}{$vmUsageDetails}
                        DESC;

                        $invoiceItems["itemdescription{$itemCount}"] = $description;
                        $invoiceItems["itemamount{$itemCount}"] = $totalCost;
                        $invoiceItems["itemtaxed{$itemCount}"] = true;

                        $totalWithoutTax += $interval->total_cost;
                        $itemCount++;
                    }
                }

                // Add Team Metrics billing (if available)
                if (!empty($responseData->team_metrics)) {
                    $teamMetricsArray = (array)$responseData->team_metrics;
                    $teamMetricsInterval = reset($teamMetricsArray);
                    
                    $teamRAM = number_format($teamMetricsInterval->RAM ?? 0, 2);
                    $teamCPU = number_format($teamMetricsInterval->CPU ?? 0, 2);
                    $teamGPU = number_format($teamMetricsInterval->GPU ?? 0, 2);
                    $teamGRAM = number_format($teamMetricsInterval->GRAM ?? 0, 2);
                    $teamTFlops = number_format($teamMetricsInterval->TFlops ?? 0, 2);
                    $teamTotal = number_format($teamMetricsInterval->total_cost ?? 0, 2);

                    if ($teamTotal > 0) {
                        $description = <<<DESC
                        Team-Level Resource Usage
                        RAM ..................................... \$ {$teamRAM}
                        CPU ..................................... \$ {$teamCPU}
                        GPU ..................................... \$ {$teamGPU}
                        GRAM .................................... \$ {$teamGRAM}
                        TFlops .................................. \$ {$teamTFlops}
                        DESC;

                        $invoiceItems["itemdescription{$itemCount}"] = $description;
                        $invoiceItems["itemamount{$itemCount}"] = $teamTotal;
                        $invoiceItems["itemtaxed{$itemCount}"] = true;

                        $totalWithoutTax += $teamMetricsInterval->total_cost;
                        $itemCount++;
                    }
                }
                }
                } else {
                    logActivity("No workspace billing data found for TeamID {$team->teamid}");
                }
            } else {
                logActivity("Main billing API failed for TeamID {$team->teamid} - HTTP Code: " . $response['httpcode']);
            }

            // ALWAYS process Shared Storage billing (regardless of main billing status)
            // TODO: Deploy updated Helper.php with getTeamSharedStorageBilling method
            // $sharedStorageResponse = $helper->getTeamSharedStorageBilling($team->teamid);
            $sharedStorageResponse = ['httpcode' => 404, 'result' => null]; // Temporary fallback
            if ($sharedStorageResponse['httpcode'] === 200 && !empty($sharedStorageResponse['result'])) {
                $sharedStorageData = $sharedStorageResponse['result'];
                logActivity("Shared storage billing for TeamID {$team->teamid}: " . json_encode($sharedStorageData));
                
                if (isset($sharedStorageData->details) && !empty($sharedStorageData->details)) {
                    foreach ($sharedStorageData->details as $volumeName => $volumeData) {
                        $volumeArray = (array)$volumeData;
                        $interval = reset($volumeArray);
                        
                        $cost = number_format($interval->cost ?? 0, 2);
                        $hoursDecimal = $interval->hours ?? 0;
                        $hoursFormatted = $helper->formatHoursMinutes($hoursDecimal);
                        
                        if ($cost > 0) {
                            $description = <<<DESC
                            Shared Storage: {$volumeName}
                            Hours ................................... {$hoursFormatted}
                            Cost .................................... \$ {$cost}
                            DESC;

                            $invoiceItems["itemdescription{$itemCount}"] = $description;
                            $invoiceItems["itemamount{$itemCount}"] = $cost;
                            $invoiceItems["itemtaxed{$itemCount}"] = true;

                            $totalWithoutTax += $interval->cost;
                            $itemCount++;
                        }
                    }
                }
            } else {
                logActivity("Shared storage billing failed or empty for TeamID {$team->teamid} - HTTP Code: " . ($sharedStorageResponse['httpcode'] ?? 'unknown'));
            }

            // ALWAYS process Enhanced GPUaaS Pool billing with Ephemeral Storage (regardless of main billing status)
            // TODO: Deploy updated Helper.php with getTeamGpuaasPoolBilling method
            // $gpuaasPoolResponse = $helper->getTeamGpuaasPoolBilling($team->teamid);
            $gpuaasPoolResponse = ['httpcode' => 404, 'result' => null]; // Temporary fallback
            if ($gpuaasPoolResponse['httpcode'] === 200 && !empty($gpuaasPoolResponse['result'])) {
                $gpuaasPoolData = $gpuaasPoolResponse['result'];
                logActivity("GPUaaS pool billing for TeamID {$team->teamid}: " . json_encode($gpuaasPoolData));
                
                if (isset($gpuaasPoolData->details) && !empty($gpuaasPoolData->details)) {
                    foreach ($gpuaasPoolData->details as $poolName => $poolData) {
                        $intervalsArray = (array)$poolData->intervals;
                        $interval = reset($intervalsArray);
                        
                        $gpuCost = number_format($interval->GPU->cost ?? 0, 2);
                        $vramCost = number_format($interval->vRAM->cost ?? 0, 2);
                        $subscriptionCost = number_format($interval->SubscriptionRate->cost ?? 0, 2);
                        $ephemeralStorageCost = number_format($interval->EphimeralStorage->cost ?? 0, 2);
                        $cpuCost = number_format($interval->CPU->cost ?? 0, 2);
                        $ramCost = number_format($interval->RAM->cost ?? 0, 2);
                        $intervalHoursDecimal = $interval->interval_hours ?? 0;
                        $intervalHoursFormatted = $helper->formatHoursMinutes($intervalHoursDecimal);
                        $totalCost = number_format($interval->interval_cost ?? 0, 2);

                        if ($totalCost > 0) {
                            $description = <<<DESC
                            GPU Pool: {$poolName} ({$poolData->model_type} - {$poolData->vendor_type})
                            GPU Subscription ........................ \$ {$subscriptionCost}
                            GPU Usage ............................... \$ {$gpuCost}
                            vRAM Usage .............................. \$ {$vramCost}
                            CPU Usage ............................... \$ {$cpuCost}
                            RAM Usage ............................... \$ {$ramCost}
                            Ephemeral Storage ....................... \$ {$ephemeralStorageCost}
                            Pool Hours .............................. {$intervalHoursFormatted}
                            DESC;

                            $invoiceItems["itemdescription{$itemCount}"] = $description;
                            $invoiceItems["itemamount{$itemCount}"] = $totalCost;
                            $invoiceItems["itemtaxed{$itemCount}"] = true;

                            $totalWithoutTax += $interval->interval_cost;
                            $itemCount++;
                        }
                    }
                }
            } else {
                logActivity("GPUaaS pool billing failed or empty for TeamID {$team->teamid} - HTTP Code: " . ($gpuaasPoolResponse['httpcode'] ?? 'unknown'));
            }

            // Generate Invoice only if there are any costs
            if ($totalWithoutTax > 0) {
                logActivity("Creating invoice for TeamID {$team->teamid} with total amount: \${$totalWithoutTax}");
                $invoiceResult = $helper->createInvoice($team->uid, $invoiceItems);
                logActivity("Invoice creation response for UID {$team->uid}: " . json_encode($invoiceResult));
                if (isset($invoiceResult['result']) && $invoiceResult['result'] === 'success') {
                    $helper->insert_teamDetail($team->uid, $team->sid, $team->pid, $invoiceResult['invoiceid'], "update");
                    logActivity("Invoice created for UID {$team->uid} - Invoice ID: {$invoiceResult['invoiceid']} - Amount: {$totalWithoutTax}");
                } else {
                    logActivity("Failed to create invoice for UID {$team->uid}: " . json_encode($invoiceResult));
                }

            } else {
                logActivity("No billable costs found for TeamID {$team->teamid} - skipping invoice generation");
            }
        }
    }
    // Suspension & Termination on overdue
    $invoices = Capsule::table('mod_hostdaiteam_details')->get();

    foreach ($invoices as $invoice) {
        $invoice_date = Capsule::table('tblinvoices')->where('id', $invoice->invoiceid)->where('status', 'Unpaid')->value('date');
        $product = Capsule::table('tblproducts')->where('id', $invoice->pid)->first();
    
        if ($invoice_date && $product) {
            $suspend_days = $product->configoption9;
            $terminate_days = $product->configoption10;
    
            if ($suspend_days !== null && $terminate_days !== null) {
                $invoiceDate = new DateTime($invoice_date);
                $today = new DateTime();
                $daysDiff = $invoiceDate->diff($today)->days;
    
                logActivity("Checking service ID {$invoice->sid} - Days since invoice: {$daysDiff}");
    
                if ($daysDiff > $terminate_days) {
                    $helper->suspendTerminate_service($invoice->sid , $invoice->pid , 'ModuleTerminate');
                    logActivity("Service ID {$invoice->sid} TERMINATED - Days since invoice: {$daysDiff} (Limit: {$terminate_days})");
                } elseif ($daysDiff > $suspend_days) {
                    $helper->suspendTerminate_service($invoice->sid, $invoice->pid, 'ModuleSuspend');
                    logActivity("Service ID {$invoice->sid} SUSPENDED - Days since invoice: {$daysDiff} (Limit: {$suspend_days})");
                }
            } else {
                logActivity("Service ID {$invoice->sid} - Product found but config options missing.");
            }
        } else {
            logActivity("Skipping service ID {$invoice->sid} - Invoice unpaid: " . ($invoice_date ? 'Yes' : 'No') . ", Product found: " . ($product ? 'Yes' : 'No'));
        }
    }

    logActivity("HostedAI Cron completed.");

} catch (\Exception $e) {
    logActivity("Exception in HostedAI Cron: " . $e->getMessage());
}
