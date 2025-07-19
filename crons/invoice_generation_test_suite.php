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
                                    'CPU' => 10.50,
                                    'RAM' => 5.25,
                                    'Disk Storage' => 2.75,
                                    'total_cost' => 18.50
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