<?php

/**
 * HostedAI Invoice Generation Test Utility
 * 
 * This utility provides comprehensive testing for the invoice generation logic
 * with various mock data scenarios including edge cases and error conditions.
 * 
 * Usage:
 * - Run all tests: php tmp_rovodev_invoice_test_utility.php
 * - Run specific test: php tmp_rovodev_invoice_test_utility.php test_case_name
 * - Add new test cases by adding to the $testCases array
 * 
 * @author Generated for HostedAI Cron Testing
 * @version 1.0
 */

// Standalone test utility - no external dependencies needed

class InvoiceTestUtility {
    
    private $testCases = [];
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function __construct() {
        $this->initializeTestCases();
    }
    
    /**
     * Initialize all test cases with mock data
     */
    private function initializeTestCases() {
        
        // Test Case 1: Missing monthly_cost
        $this->testCases['missing_monthly_cost'] = [
            'description' => 'Missing monthly_cost field',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Test Workspace',
                        'instances' => (object)[
                            'instance-1' => (object)[
                                '2025-01' => (object)[
                                    'Cost_Of_CPU' => 10.50,
                                    'Cost_Of_RAM' => 5.25,
                                    'Cost_Of_Disk_Storage' => 2.75,
                                    'Cost_Of_GPU' => 0.00,
                                    'Cost_Of_NetworkIn' => 1.25,
                                    'Cost_Of_NetworkOut' => 0.75,
                                    'Cost_of_Public_IP' => 2.50,
                                    'total_cost' => 22.75
                                ]
                            ]
                        ]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[]
                // monthly_cost is intentionally missing
            ],
            'expected_items' => 1,
            'expected_total' => 18.50,
            'expected_descriptions' => ['Workspace: Test Workspace']
        ];
        
        // Test Case 2: Zero monthly_cost
        $this->testCases['zero_monthly_cost'] = [
            'description' => 'Zero monthly_cost value',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Test Workspace',
                        'instances' => (object)[
                            'instance-1' => (object)[
                                '2025-01' => (object)[
                                    'CPU' => 5.00,
                                    'RAM' => 3.00,
                                    'Disk Storage' => 2.00,
                                    'total_cost' => 10.00
                                ]
                            ]
                        ]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[],
                'monthly_cost' => 0
            ],
            'expected_items' => 1,
            'expected_total' => 10.00,
            'expected_descriptions' => ['Workspace: Test Workspace']
        ];
        
        // Test Case 3: Multiple GPU pools
        $this->testCases['multiple_gpu_pools'] = [
            'description' => 'Multiple GPU pools with different configurations',
            'data' => (object)[
                'billing_by_workspace' => (object)[],
                'gpuaas_billing_by_pool' => (object)[
                    '1' => (object)[
                        'pool_name' => 'GPU Pool Alpha',
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'Cost_Of_GPUConsumed' => 100.00,
                                'Cost_Of_vRAMConsumed' => 50.00,
                                'Cost_Of_TotalTFlopsConsumed' => 25.00,
                                'total_cost' => 175.00
                            ]
                        ]
                    ],
                    '2' => (object)[
                        'pool_name' => 'GPU Pool Beta',
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'Cost_Of_GPUConsumed' => 200.00,
                                'Cost_Of_vRAMConsumed' => 75.00,
                                'Cost_Of_TotalTFlopsConsumed' => 0.00,
                                'total_cost' => 275.00
                            ]
                        ]
                    ]
                ],
                'monthly_cost' => 15.00
            ],
            'expected_items' => 3,
            'expected_total' => 465.00,
            'expected_descriptions' => ['Monthly Base Service Fee', 'GPU Pool: GPU Pool Alpha', 'GPU Pool: GPU Pool Beta']
        ];
        
        // Test Case 4: Mixed usage (workspace + GPU)
        $this->testCases['mixed_usage'] = [
            'description' => 'Customer with both workspace instances and GPU pools',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Production Workspace',
                        'instances' => (object)[
                            'instance-1' => (object)[
                                '2025-01' => (object)[
                                    'CPU' => 15.00,
                                    'RAM' => 8.00,
                                    'Disk Storage' => 5.00,
                                    'total_cost' => 28.00
                                ]
                            ],
                            'instance-2' => (object)[
                                '2025-01' => (object)[
                                    'CPU' => 12.00,
                                    'RAM' => 6.00,
                                    'Disk Storage' => 4.00,
                                    'total_cost' => 22.00
                                ]
                            ]
                        ]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[
                    '3' => (object)[
                        'pool_name' => 'ML Training Pool',
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'Cost_Of_GPUConsumed' => 300.00,
                                'Cost_Of_vRAMConsumed' => 150.00,
                                'Cost_Of_TotalTFlopsConsumed' => 50.00,
                                'total_cost' => 500.00
                            ]
                        ]
                    ]
                ],
                'monthly_cost' => 20.00
            ],
            'expected_items' => 4,
            'expected_total' => 570.00,
            'expected_descriptions' => ['Monthly Base Service Fee', 'Workspace: Production Workspace', 'Workspace: Production Workspace', 'GPU Pool: ML Training Pool']
        ];
        
        // Test Case 5: Empty response
        $this->testCases['empty_response'] = [
            'description' => 'Completely empty billing response',
            'data' => (object)[
                'billing_by_workspace' => (object)[],
                'gpuaas_billing_by_pool' => (object)[],
                'monthly_cost' => 12.00
            ],
            'expected_items' => 1,
            'expected_total' => 12.00,
            'expected_descriptions' => ['Monthly Base Service Fee']
        ];
        
        // Test Case 6: Empty workspace instances
        $this->testCases['empty_workspace_instances'] = [
            'description' => 'Workspace with empty instances object',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Empty Workspace',
                        'instances' => (object)[]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[],
                'monthly_cost' => 12.00
            ],
            'expected_items' => 1,
            'expected_total' => 12.00,
            'expected_descriptions' => ['Monthly Base Service Fee']
        ];
        
        // Test Case 7: Micro-usage scenarios
        $this->testCases['micro_usage'] = [
            'description' => 'Very small usage amounts (cents)',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Dev Workspace',
                        'instances' => (object)[
                            'instance-1' => (object)[
                                '2025-01' => (object)[
                                    'CPU' => 0.01,
                                    'RAM' => 0.02,
                                    'Disk Storage' => 0.03,
                                    'total_cost' => 0.06
                                ]
                            ]
                        ]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[
                    '1' => (object)[
                        'pool_name' => 'Test GPU Pool',
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'Cost_Of_GPUConsumed' => 0.05,
                                'Cost_Of_vRAMConsumed' => 0.03,
                                'Cost_Of_TotalTFlopsConsumed' => 0.00,
                                'total_cost' => 0.08
                            ]
                        ]
                    ]
                ],
                'monthly_cost' => 12.00
            ],
            'expected_items' => 3,
            'expected_total' => 12.14,
            'expected_descriptions' => ['Monthly Base Service Fee', 'Workspace: Dev Workspace', 'GPU Pool: Test GPU Pool']
        ];
        
        // Test Case 8: Large numbers
        $this->testCases['large_numbers'] = [
            'description' => 'Very large usage amounts (enterprise scale)',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Enterprise Workspace',
                        'instances' => (object)[
                            'instance-1' => (object)[
                                '2025-01' => (object)[
                                    'CPU' => 5000.00,
                                    'RAM' => 3000.00,
                                    'Disk Storage' => 2000.00,
                                    'total_cost' => 10000.00
                                ]
                            ]
                        ]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[
                    '1' => (object)[
                        'pool_name' => 'Enterprise GPU Cluster',
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'Cost_Of_GPUConsumed' => 50000.00,
                                'Cost_Of_vRAMConsumed' => 25000.00,
                                'Cost_Of_TotalTFlopsConsumed' => 15000.00,
                                'total_cost' => 90000.00
                            ]
                        ]
                    ]
                ],
                'monthly_cost' => 100.00
            ],
            'expected_items' => 3,
            'expected_total' => 100100.00,
            'expected_descriptions' => ['Monthly Base Service Fee', 'Workspace: Enterprise Workspace', 'GPU Pool: Enterprise GPU Cluster']
        ];

        // Test Case 9: Shared Storage Billing
        $this->testCases['shared_storage_billing'] = [
            'description' => 'Shared storage volumes billing',
            'data' => (object)[
                'billing_by_workspace' => (object)[],
                'gpuaas_billing_by_pool' => (object)[],
                'monthly_cost' => 25.00
            ],
            'shared_storage_data' => (object)[
                'team_id' => 'team-123',
                'total_billing' => 150.75,
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'details' => (object)[
                    'shared-volume-1' => (object)[
                        '2025-01' => (object)[
                            'cost' => 75.50,
                            'hours' => 744.0
                        ]
                    ],
                    'shared-volume-2' => (object)[
                        '2025-01' => (object)[
                            'cost' => 75.25,
                            'hours' => 744.0
                        ]
                    ]
                ]
            ],
            'expected_items' => 3,
            'expected_total' => 175.75,
            'expected_descriptions' => ['Monthly Base Service Fee', 'Shared Storage: shared-volume-1', 'Shared Storage: shared-volume-2']
        ];

        // Test Case 10: Enhanced GPUaaS Pool with Ephemeral Storage
        $this->testCases['enhanced_gpuaas_ephemeral'] = [
            'description' => 'GPUaaS pools with ephemeral storage billing',
            'data' => (object)[
                'billing_by_workspace' => (object)[],
                'gpuaas_billing_by_pool' => (object)[],
                'monthly_cost' => 30.00
            ],
            'enhanced_gpuaas_data' => (object)[
                'team_id' => 'team-456',
                'total_billing' => 425.80,
                'current_month_total_cost' => 425.80,
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'details' => (object)[
                    'ml-training-pool' => (object)[
                        'model_type' => 'A100',
                        'vendor_type' => 'NVIDIA',
                        'pool_id' => 101,
                        'pool_name' => 'ML Training Pool',
                        'total_cost' => 425.80,
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'GPU' => (object)['cost' => 200.00],
                                'vRAM' => (object)['cost' => 75.00],
                                'SubscriptionRate' => (object)['cost' => 100.00],
                                'EphimeralStorage' => (object)['cost' => 35.80],
                                'CPU' => (object)['cost' => 10.00],
                                'RAM' => (object)['cost' => 5.00],
                                'interval_cost' => 425.80,
                                'interval_hours' => 744.0
                            ]
                        ]
                    ]
                ]
            ],
            'expected_items' => 2,
            'expected_total' => 455.80,
            'expected_descriptions' => ['Monthly Base Service Fee', 'GPU Pool: ML Training Pool']
        ];

        // Test Case 11: Complete Integration (All Billing Types)
        $this->testCases['complete_integration'] = [
            'description' => 'Complete billing with instances, GPU pools, shared storage, and ephemeral storage',
            'data' => (object)[
                'billing_by_workspace' => (object)[
                    'workspace-1' => (object)[
                        'workspace_name' => 'Full Stack Workspace',
                        'instances' => (object)[
                            'instance-1' => (object)[
                                '2025-01' => (object)[
                                    'Cost_Of_CPU' => 25.00,
                                    'Cost_Of_RAM' => 15.00,
                                    'Cost_Of_Disk_Storage' => 10.00,
                                    'Cost_Of_GPU' => 50.00,
                                    'Cost_Of_NetworkIn' => 5.00,
                                    'Cost_Of_NetworkOut' => 3.00,
                                    'Cost_of_Public_IP' => 7.00,
                                    'total_cost' => 115.00
                                ]
                            ]
                        ]
                    ]
                ],
                'gpuaas_billing_by_pool' => (object)[],
                'monthly_cost' => 50.00,
                'pci_devices' => (object)[
                    'total_billing' => 125.50,
                    'pci_devices' => (object)[
                        'gpu-card-1' => (object)[
                            '2025-01-01' => (object)[
                                'total_hours' => 24.0,
                                'total_cost' => 125.50,
                                'vm_usage' => [
                                    (object)['VMID' => 'vm-123', 'Hours' => 24.0, 'Cost' => 125.50]
                                ]
                            ]
                        ]
                    ]
                ],
                'team_metrics' => (object)[
                    '2025-01' => (object)[
                        'RAM' => 15.25,
                        'CPU' => 8.75,
                        'GPU' => 25.50,
                        'GRAM' => 12.00,
                        'TFlops' => 18.25,
                        'total_cost' => 79.75
                    ]
                ]
            ],
            'shared_storage_data' => (object)[
                'details' => (object)[
                    'shared-data-volume' => (object)[
                        '2025-01' => (object)[
                            'cost' => 45.25,
                            'hours' => 744.0
                        ]
                    ]
                ]
            ],
            'enhanced_gpuaas_data' => (object)[
                'details' => (object)[
                    'enterprise-gpu-pool' => (object)[
                        'model_type' => 'H100',
                        'vendor_type' => 'NVIDIA',
                        'intervals' => (object)[
                            '2025-01' => (object)[
                                'GPU' => (object)['cost' => 300.00],
                                'vRAM' => (object)['cost' => 150.00],
                                'SubscriptionRate' => (object)['cost' => 200.00],
                                'EphimeralStorage' => (object)['cost' => 85.75],
                                'CPU' => (object)['cost' => 25.00],
                                'RAM' => (object)['cost' => 15.00],
                                'interval_cost' => 775.75,
                                'interval_hours' => 744.0
                            ]
                        ]
                    ]
                ]
            ],
            'expected_items' => 7,
            'expected_total' => 1191.25,
            'expected_descriptions' => [
                'Monthly Base Service Fee', 
                'Workspace: Full Stack Workspace', 
                'GPU Card: gpu-card-1',
                'Team-Level Resource Usage',
                'Shared Storage: shared-data-volume',
                'GPU Pool: enterprise-gpu-pool'
            ]
        ];
    }
    
    /**
     * Simulate the invoice generation logic from the cron script
     */
    private function generateInvoiceItems($responseData) {
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

        // Process workspace billing
        if (isset($responseData->billing_by_workspace)) {
            foreach ($responseData->billing_by_workspace as $workspace) {
                $workspaceName = $workspace->workspace_name;
                if (empty($workspace->instances)) {
                    continue;
                }
            
                foreach ($workspace->instances as $instanceId => $instanceData) {
                    $instanceArray = (array)$instanceData;
                    $monthData = reset($instanceArray);
            
                    // Enhanced cost breakdown with new fields
                    $cpu = number_format($monthData->Cost_Of_CPU ?? $monthData->CPU ?? 0, 2);
                    $ram = number_format($monthData->Cost_Of_RAM ?? $monthData->RAM ?? 0, 2);
                    $disk = number_format($monthData->Cost_Of_Disk_Storage ?? $monthData->{'Disk Storage'} ?? 0, 2);
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
            }
        }

        // Add GPUaaS pool billing (if available)
        if (isset($responseData->gpuaas_billing_by_pool) && !empty($responseData->gpuaas_billing_by_pool)) {
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

        return [
            'items' => $invoiceItems,
            'total' => $totalWithoutTax,
            'item_count' => $itemCount - 1
        ];
    }

    /**
     * Simulate the enhanced invoice generation logic including shared storage and ephemeral storage
     */
    private function generateEnhancedInvoiceItems($testCase) {
        $responseData = $testCase['data'];
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

        // Process workspace billing
        if (isset($responseData->billing_by_workspace)) {
            foreach ($responseData->billing_by_workspace as $workspace) {
                $workspaceName = $workspace->workspace_name ?? 'Unknown Workspace';
                if (empty($workspace->instances)) {
                    continue;
                }
            
                foreach ($workspace->instances as $instanceId => $instanceData) {
                    $instanceArray = (array)$instanceData;
                    $monthData = reset($instanceArray);
            
                    // Enhanced cost breakdown with new fields
                    $cpu = number_format($monthData->Cost_Of_CPU ?? $monthData->CPU ?? 0, 2);
                    $ram = number_format($monthData->Cost_Of_RAM ?? $monthData->RAM ?? 0, 2);
                    $disk = number_format($monthData->Cost_Of_Disk_Storage ?? $monthData->{'Disk Storage'} ?? 0, 2);
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
            }
        }

        // Add PCI Device (GPU Card) billing (if available)
        if (!empty($responseData->pci_devices) && isset($responseData->pci_devices->pci_devices)) {
            foreach ($responseData->pci_devices->pci_devices as $cardId => $cardData) {
                $intervalsArray = (array)$cardData;
                $interval = reset($intervalsArray);
                
                $totalHours = number_format($interval->total_hours ?? 0, 2);
                $totalCost = number_format($interval->total_cost ?? 0, 2);
                
                // Get VM usage details
                $vmUsageDetails = '';
                if (!empty($interval->vm_usage)) {
                    foreach ($interval->vm_usage as $vmUsage) {
                        $vmId = $vmUsage->VMID ?? 'Unknown';
                        $vmHours = number_format($vmUsage->Hours ?? 0, 2);
                        $vmCost = number_format($vmUsage->Cost ?? 0, 2);
                        $vmUsageDetails .= "\n                        VM {$vmId}: {$vmHours} hrs (\${$vmCost})";
                    }
                }

                $description = <<<DESC
                GPU Card: {$cardId}
                Total Hours ............................. {$totalHours} hrs{$vmUsageDetails}
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

        // Add Shared Storage billing (if available)
        if (isset($testCase['shared_storage_data'])) {
            $sharedStorageData = $testCase['shared_storage_data'];
            
            if (isset($sharedStorageData->details) && !empty($sharedStorageData->details)) {
                foreach ($sharedStorageData->details as $volumeName => $volumeData) {
                    $volumeArray = (array)$volumeData;
                    $interval = reset($volumeArray);
                    
                    $cost = number_format($interval->cost ?? 0, 2);
                    $hours = number_format($interval->hours ?? 0, 2);
                    
                    if ($cost > 0) {
                        $description = <<<DESC
                        Shared Storage: {$volumeName}
                        Hours ................................... {$hours} hrs
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
        }

        // Add Enhanced GPUaaS Pool billing with Ephemeral Storage (if available)
        if (isset($testCase['enhanced_gpuaas_data'])) {
            $gpuaasPoolData = $testCase['enhanced_gpuaas_data'];
            
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
                    $intervalHours = number_format($interval->interval_hours ?? 0, 2);
                    $totalCost = number_format($interval->interval_cost ?? 0, 2);

                    if ($totalCost > 0) {
                        $modelType = $poolData->model_type ?? 'Unknown';
                        $vendorType = $poolData->vendor_type ?? 'Unknown';
                        
                        $description = <<<DESC
                        GPU Pool: {$poolName} ({$modelType} - {$vendorType})
                        GPU Subscription ........................ \$ {$subscriptionCost}
                        GPU Usage ............................... \$ {$gpuCost}
                        vRAM Usage .............................. \$ {$vramCost}
                        CPU Usage ............................... \$ {$cpuCost}
                        RAM Usage ............................... \$ {$ramCost}
                        Ephemeral Storage ....................... \$ {$ephemeralStorageCost}
                        Pool Hours .............................. {$intervalHours} hrs
                        DESC;

                        $invoiceItems["itemdescription{$itemCount}"] = $description;
                        $invoiceItems["itemamount{$itemCount}"] = $totalCost;
                        $invoiceItems["itemtaxed{$itemCount}"] = true;

                        $totalWithoutTax += $interval->interval_cost;
                        $itemCount++;
                    }
                }
            }
        }

        return [
            'items' => $invoiceItems,
            'total' => $totalWithoutTax,
            'item_count' => $itemCount - 1
        ];
    }
    
    /**
     * Run a specific test case
     */
    public function runTest($testName) {
        if (!isset($this->testCases[$testName])) {
            echo "❌ Test case '{$testName}' not found!\n";
            return false;
        }
        
        $testCase = $this->testCases[$testName];
        echo "\n🧪 Running Test: {$testCase['description']}\n";
        echo str_repeat("-", 60) . "\n";
        
        try {
            $result = $this->generateInvoiceItems($testCase['data']);
            
            // Validate results
            $passed = true;
            $errors = [];
            
            // Check item count
            if ($result['item_count'] !== $testCase['expected_items']) {
                $passed = false;
                $errors[] = "Expected {$testCase['expected_items']} items, got {$result['item_count']}";
            }
            
            // Check total (with small tolerance for floating point)
            if (abs($result['total'] - $testCase['expected_total']) > 0.01) {
                $passed = false;
                $errors[] = "Expected total {$testCase['expected_total']}, got {$result['total']}";
            }
            
            // Check descriptions
            $actualDescriptions = [];
            for ($i = 1; $i <= $result['item_count']; $i++) {
                if (isset($result['items']["itemdescription{$i}"])) {
                    $desc = $result['items']["itemdescription{$i}"];
                    // Extract first line for comparison
                    $firstLine = trim(explode("\n", $desc)[0]);
                    $actualDescriptions[] = $firstLine;
                }
            }
            
            foreach ($testCase['expected_descriptions'] as $expectedDesc) {
                $found = false;
                foreach ($actualDescriptions as $actualDesc) {
                    if (strpos($actualDesc, $expectedDesc) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $passed = false;
                    $errors[] = "Expected description containing '{$expectedDesc}' not found";
                }
            }
            
            // Display results
            if ($passed) {
                echo "✅ PASSED\n";
                $this->passedTests++;
            } else {
                echo "❌ FAILED\n";
                foreach ($errors as $error) {
                    echo "   • {$error}\n";
                }
                $this->failedTests++;
            }
            
            // Display detailed invoice
            $this->displayInvoice($result);
            
            return $passed;
            
        } catch (Exception $e) {
            echo "❌ FAILED - Exception: " . $e->getMessage() . "\n";
            $this->failedTests++;
            return false;
        }
    }
    
    /**
     * Run all test cases
     */
    public function runAllTests() {
        echo "🚀 Starting HostedAI Invoice Generation Tests\n";
        echo "=" . str_repeat("=", 59) . "\n";
        
        foreach (array_keys($this->testCases) as $testName) {
            $this->runTest($testName);
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 Test Summary:\n";
        echo "✅ Passed: {$this->passedTests}\n";
        echo "❌ Failed: {$this->failedTests}\n";
        echo "📈 Success Rate: " . round(($this->passedTests / ($this->passedTests + $this->failedTests)) * 100, 1) . "%\n";
        
        return $this->failedTests === 0;
    }
    
    /**
     * Display a formatted invoice
     */
    private function displayInvoice($result) {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "                           INVOICE PREVIEW\n";
        echo str_repeat("=", 70) . "\n";
        
        $itemNumber = 1;
        for ($i = 1; $i <= $result['item_count']; $i++) {
            if (isset($result['items']["itemdescription{$i}"])) {
                $description = $result['items']["itemdescription{$i}"];
                $amount = $result['items']["itemamount{$i}"];
                $taxed = $result['items']["itemtaxed{$i}"] ? " (Taxed)" : "";
                
                echo "\nItem #{$itemNumber}:\n";
                echo str_repeat("-", 50) . "\n";
                
                // Handle multi-line descriptions (workspace/GPU details)
                $lines = explode("\n", trim($description));
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        echo $line . "\n";
                    }
                }
                
                echo "\nAmount: $" . number_format((float)$amount, 2) . $taxed . "\n";
                $itemNumber++;
            }
        }
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "TOTAL AMOUNT: $" . number_format($result['total'], 2) . " (before taxes)\n";
        echo str_repeat("=", 70) . "\n";
    }
    
    /**
     * Add a new test case
     */
    public function addTestCase($name, $description, $data, $expectedItems, $expectedTotal, $expectedDescriptions) {
        $this->testCases[$name] = [
            'description' => $description,
            'data' => $data,
            'expected_items' => $expectedItems,
            'expected_total' => $expectedTotal,
            'expected_descriptions' => $expectedDescriptions
        ];
    }
    
    /**
     * List all available test cases
     */
    public function listTestCases() {
        echo "📋 Available Test Cases:\n";
        echo str_repeat("-", 40) . "\n";
        foreach ($this->testCases as $name => $testCase) {
            echo "• {$name}: {$testCase['description']}\n";
        }
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $tester = new InvoiceTestUtility();
    
    if (isset($argv[1])) {
        if ($argv[1] === 'list') {
            $tester->listTestCases();
        } else {
            $tester->runTest($argv[1]);
        }
    } else {
        $tester->runAllTests();
    }
}

?>