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

    if (date('d') == '01') {

        // Generate bills and create invoices
        $teams = Capsule::table('mod_hostdaiteam_details')->get();

        foreach ($teams as $team) {
            $response = $helper->generateBill($team->teamid);
            logActivity("Billing response for TeamID {$team->teamid}: " . json_encode($response));

            if ($response['httpcode'] === 200) {

                $responseData = $response['result'];
                $invoiceItems = [];
                $itemCount = 1;
                $totalWithoutTax = 0;

                // Add monthly base cost if available
                if (isset($responseData->monthly_cost) && $responseData->monthly_cost > 0) {
                    $monthlyCost = number_format($responseData->monthly_cost, 2);
                    $invoiceItems["itemdescription{$itemCount}"] = "Monthly Base Service Fee";
                    $invoiceItems["itemamount{$itemCount}"] = $monthlyCost;
                    $invoiceItems["itemtaxed{$itemCount}"] = true;
                    $totalWithoutTax += $responseData->monthly_cost;
                    $itemCount++;
                }

                foreach ($responseData->billing_by_workspace as $workspace) {
                    $workspaceName = $workspace->workspace_name;
                    if (empty($workspace->instances)) {
                        continue;
                    }
                
                    foreach ($workspace->instances as $instanceId => $instanceData) {
                        $instanceArray = (array)$instanceData;
                        $monthData = reset($instanceArray);
                
                        $cpu = number_format($monthData->CPU, 2);
                        $ram = number_format($monthData->RAM, 2);
                        $disk = number_format($monthData->{'Disk Storage'}, 2);
                        $instanceTotal = number_format($monthData->total_cost, 2);
                
                        $description = <<<DESC
                                        Workspace: {$workspaceName}
                                        Instance ID: {$instanceId}
                                        CPU ………………………………………………………… \$ {$cpu}
                                        RAM ………………………………………………………… \$ {$ram}
                                        Disk Storage ………………………………………… \$ {$disk}
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
                        $poolName = $poolData->pool_name;
                        $intervalsArray = (array)$poolData->intervals;
                        $interval = reset($intervalsArray);

                        $gpuCost = number_format($interval->Cost_Of_GPUConsumed, 2);
                        $vramCost = number_format($interval->Cost_Of_vRAMConsumed, 2);
                        $tflopsCost = number_format($interval->Cost_Of_TotalTFlopsConsumed, 2);
                        $totalCost = number_format($interval->total_cost, 2);

                        $description = <<<DESC
                        GPU Pool: {$poolName}
                        GPU ..................................... \$ {$gpuCost}
                        vRAM .................................... \$ {$vramCost}
                        TFlops .................................. \$ {$tflopsCost}
                        DESC;

                        $invoiceItems["itemdescription{$itemCount}"] = $description;
                        $invoiceItems["itemamount{$itemCount}"] = $totalCost;
                        $invoiceItems["itemtaxed{$itemCount}"] = true;

                        $totalWithoutTax += $interval->total_cost;
                        $itemCount++;
                    }
                }
            }

                // Generate Invoice
                $invoiceResult = $helper->createInvoice($team->uid, $invoiceItems);
                logActivity("Invoice creation response for UID {$team->uid}: " . json_encode($invoiceResult));
                if (isset($invoiceResult['result']) && $invoiceResult['result'] === 'success') {
                    $helper->insert_teamDetail($team->uid, $team->sid, $team->pid, $invoiceResult['invoiceid'], "update");
                    logActivity("Invoice created for UID {$team->uid} - Invoice ID: {$invoiceResult['invoiceid']} - Amount: {$totalWithoutTax}");
                } else {
                    logActivity("Failed to create invoice for UID {$team->uid}: " . json_encode($invoiceResult));
                }

            } else {
                logActivity("Failed to generate bill for TeamID {$team->teamid}: " . json_encode($response));
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
