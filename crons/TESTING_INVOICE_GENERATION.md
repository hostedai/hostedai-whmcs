# HostedAI Invoice Generation Test Suite

## Overview

This test suite provides comprehensive testing for the HostedAI invoice generation logic with various mock data scenarios including edge cases and error conditions.

**Files in this test suite:**
- `invoice_generation_test_suite.php` - Main test utility
- `TESTING_INVOICE_GENERATION.md` - This documentation
- `test_example_output.txt` - Sample test output

## Features

- ✅ **8 Pre-built Test Cases** covering common and edge case scenarios
- ✅ **Automated Validation** of invoice items, totals, and descriptions
- ✅ **Detailed Reporting** with pass/fail status and error details
- ✅ **Extensible Design** for adding custom test cases
- ✅ **CLI Interface** for easy automation and integration

## Usage

### Run All Tests
```bash
php invoice_generation_test_suite.php
```

### Run Specific Test
```bash
php invoice_generation_test_suite.php test_case_name
```

### List Available Tests
```bash
php invoice_generation_test_suite.php list
```

## Test Cases Included

### 1. `missing_monthly_cost`
**Description:** Missing monthly_cost field  
**Scenario:** API response without monthly_cost field  
**Expected:** Only workspace billing items, no base fee  

### 2. `zero_monthly_cost`
**Description:** Zero monthly_cost value  
**Scenario:** monthly_cost is explicitly set to 0  
**Expected:** Only workspace billing items, no base fee  

### 3. `multiple_gpu_pools`
**Description:** Multiple GPU pools with different configurations  
**Scenario:** Customer using 2+ different GPU pools simultaneously  
**Expected:** Base fee + multiple GPU pool line items  

### 4. `mixed_usage`
**Description:** Customer with both workspace instances and GPU pools  
**Scenario:** Full-featured customer using all service types  
**Expected:** Base fee + workspace items + GPU pool items  

### 5. `empty_response`
**Description:** Completely empty billing response  
**Scenario:** Customer with no usage except base service  
**Expected:** Only base fee line item  

### 6. `empty_workspace_instances`
**Description:** Workspace with empty instances object  
**Scenario:** Workspace exists but has no active instances  
**Expected:** Only base fee, no workspace items  

### 7. `micro_usage`
**Description:** Very small usage amounts (cents)  
**Scenario:** Development/testing environments with minimal usage  
**Expected:** Proper handling of small decimal amounts  

### 8. `large_numbers`
**Description:** Very large usage amounts (enterprise scale)  
**Scenario:** Enterprise customers with high-volume usage  
**Expected:** Proper handling of large numbers and formatting  

## Adding Custom Test Cases

You can add new test cases programmatically:

```php
$tester = new InvoiceTestUtility();

$customData = (object)[
    'billing_by_workspace' => (object)[
        // Your workspace data
    ],
    'gpuaas_billing_by_pool' => (object)[
        // Your GPU pool data
    ],
    'monthly_cost' => 12.00
];

$tester->addTestCase(
    'my_custom_test',           // Test name
    'My custom scenario',       // Description
    $customData,               // Mock data
    2,                         // Expected number of items
    50.00,                     // Expected total
    ['Monthly Base Service Fee'] // Expected descriptions
);

$tester->runTest('my_custom_test');
```

## Test Validation

Each test validates:

1. **Item Count:** Correct number of invoice line items
2. **Total Amount:** Accurate sum of all charges
3. **Descriptions:** Presence of expected description patterns
4. **Error Handling:** Graceful handling of malformed data

## Sample Output

```
🚀 Starting HostedAI Invoice Generation Tests
============================================================

🧪 Running Test: Missing monthly_cost field
------------------------------------------------------------
✅ PASSED

Generated Invoice:
Item 1: 18.50 - Workspace: Test Workspace
Total: $18.50

🧪 Running Test: Multiple GPU pools with different configurations
------------------------------------------------------------
✅ PASSED

Generated Invoice:
Item 1: 15.00 - Monthly Base Service Fee
Item 2: 175.00 - GPU Pool: GPU Pool Alpha
Item 3: 275.00 - GPU Pool: GPU Pool Beta
Total: $465.00

============================================================
📊 Test Summary:
✅ Passed: 8
❌ Failed: 0
📈 Success Rate: 100.0%
```

## Integration with CI/CD

The utility returns appropriate exit codes for automation:

```bash
# Run tests and check exit code
php invoice_generation_test_suite.php
if [ $? -eq 0 ]; then
    echo "All tests passed!"
else
    echo "Some tests failed!"
    exit 1
fi
```

## Mock Data Structure

Test data follows the same structure as the HostedAI API response:

```php
$mockData = (object)[
    'billing_by_workspace' => (object)[
        'workspace-id' => (object)[
            'workspace_name' => 'Workspace Name',
            'instances' => (object)[
                'instance-id' => (object)[
                    'YYYY-MM' => (object)[
                        'CPU' => 10.00,
                        'RAM' => 5.00,
                        'Disk Storage' => 2.00,
                        'total_cost' => 17.00
                    ]
                ]
            ]
        ]
    ],
    'gpuaas_billing_by_pool' => (object)[
        'pool-id' => (object)[
            'pool_name' => 'Pool Name',
            'intervals' => (object)[
                'YYYY-MM' => (object)[
                    'Cost_Of_GPUConsumed' => 100.00,
                    'Cost_Of_vRAMConsumed' => 50.00,
                    'Cost_Of_TotalTFlopsConsumed' => 25.00,
                    'total_cost' => 175.00
                ]
            ]
        ]
    ],
    'monthly_cost' => 12.00
];
```

## Troubleshooting

### Common Issues

1. **Test Fails with "Expected X items, got Y"**
   - Check if your mock data structure matches expected format
   - Verify empty instances are properly handled

2. **Total Amount Mismatch**
   - Ensure all cost fields are numeric
   - Check for floating point precision issues

3. **Description Not Found**
   - Verify workspace names and pool names in mock data
   - Check for special characters in descriptions

### Debug Mode

For detailed debugging, you can modify the test utility to output more information:

```php
// Add this before running tests for verbose output
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Best Practices

1. **Test Early and Often:** Run tests after any changes to billing logic
2. **Add Edge Cases:** Create tests for any new scenarios you encounter
3. **Validate Real Data:** Use actual API responses as test cases when possible
4. **Monitor Performance:** Ensure tests run quickly for CI/CD integration
5. **Document Changes:** Update test descriptions when modifying scenarios

## Development Workflow

### Before Pushing Changes

Always run the test suite before committing changes to the invoice generation logic:

```bash
# Run all tests to ensure no regressions
php invoice_generation_test_suite.php

# If all tests pass, your changes are safe to commit
git add .
git commit -m "Updated invoice generation logic"
git push
```

### Adding New Test Cases

When adding new features or fixing bugs, add corresponding test cases to ensure future stability.