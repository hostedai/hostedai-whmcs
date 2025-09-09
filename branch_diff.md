# Git Diff: Add-Shared-and-Ephemeral-storage-costs vs main

## Branch Information
- **Current Branch**: Add-Shared-and-Ephemeral-storage-costs  
- **Comparing Against**: main
- **Status**: 4 commits ahead of origin/Add-Shared-and-Ephemeral-storage-costs

---

## Files Changed: 1 file

```diff
diff --git a/ENHANCED_BILLING_INTEGRATION.md b/ENHANCED_BILLING_INTEGRATION.md
new file mode 100644
index 0000000..c4e6b43
--- /dev/null
+++ b/ENHANCED_BILLING_INTEGRATION.md
@@ -0,0 +1,395 @@
+# Enhanced Billing API Integration for WHMCS
+
+## Overview
+
+This document outlines the enhanced billing integration between the HostedAI WHMCS module and the updated HostedAI billing API. The integration now supports comprehensive cost tracking including GPU resources, network usage, PCI devices, and team-level metrics.
+
+## Key Enhancements
+
+### 1. Enhanced Instance Cost Breakdown
+
+The billing now includes detailed per-resource costs:
+
+- **Cost_Of_CPU**: CPU usage costs
+- **Cost_Of_RAM**: Memory usage costs  
+- **Cost_Of_Disk_Storage**: Storage costs
+- **Cost_Of_GPU**: GPU usage costs
+- **Cost_Of_NetworkIn**: Inbound network costs
+- **Cost_Of_NetworkOut**: Outbound network costs
+- **Cost_of_Public_IP**: Public IP address costs
+
+### 2. GPUaaS Pool Billing
+
+Enhanced GPU pool billing with detailed metrics:
+
+- **Cost_Of_GPUConsumed**: GPU subscription costs
+- **Cost_Of_vRAMConsumed**: Video memory consumption costs
+- **Cost_Of_TotalTFlopsConsumed**: Computational power costs
+- **GPU_Pool_Hours**: Pool usage hours
+- Pool metadata (name, model type, vendor type)
+
+### 3. PCI Device (GPU Card) Billing
+
+New support for individual GPU card billing:
+
+- Per-card usage tracking
+- VM-specific usage breakdown
+- Hourly usage and cost details
+- Card reassignment tracking
+
+### 4. Team Metrics Integration
+
+Team-level resource usage billing:
+
+- **RAM**: Team RAM consumption
+- **CPU**: Team CPU usage
+- **GPU**: Team GPU utilization
+- **GRAM**: Team GPU memory usage
+- **TFlops**: Team computational usage
+
+### 5. Policy and Currency Information
+
+Enhanced transparency with:
+
+- **pricing_policy**: Applied pricing policy ID
+- **resource_policy**: Applied resource policy ID
+- **currency_code**: Billing currency (e.g., USD)
+- **currency_symbol**: Currency symbol (e.g., $)
+- **current_month_total_cost**: Current month spending
+- **monthly_cost**: Estimated monthly cost
+
+## API Endpoints Used
+
+### Primary Billing Endpoint
+```
+GET /team-billing/group-by-workspace/{team_id}/{start_date}/{end_date}/monthly
+```
+
+### Shared Storage Billing Endpoint
+```
+GET /team-billing/shared-storage/{team_id}/{start_date}/{end_date}/{interval}/{region_id}
+```
+
+**Response Structure:**
+```json
+{
+  "team_id": "team-uuid",
+  "start_date": "2024-01-01",
+  "end_date": "2024-01-31", 
+  "interval": "monthly",
+  "region_id": "region-id",
+  "total_billing": 123.45,
+  "currency_code": "USD",
+  "currency_symbol": "$",
+  "details": {
+    "shared-volume-name": {
+      "2024-01": {
+        "cost": 45.67,
+        "hours": 744.0,
+        "from": "2024-01-01T00:00:00Z",
+        "to": "2024-01-31T23:59:59Z"
+      }
+    }
+  }
+}
+```
+
+### Enhanced GPUaaS Pool Billing Endpoint
+```
+GET /team-billing/gpuaas-pool/{team_id}/{start_date}/{end_date}/{interval}/{region_id}
+```
+
+**Response Structure:**
+```json
+{
+  "team_id": "team-uuid",
+  "start_date": "2024-01-01",
+  "end_date": "2024-01-31",
+  "interval": "monthly", 
+  "total_billing": 456.78,
+  "current_month_total_cost": 123.45,
+  "currency_code": "USD",
+  "currency_symbol": "$",
+  "details": {
+    "pool-name": {
+      "intervals": {
+        "2024-01": {
+          "vRAM": {"cost": 12.34, "price": 0.02, "inclusive_price": 0.005},
+          "RAM": {"cost": 23.45, "price": 0.01, "inclusive_price": 0.002},
+          "CPU": {"cost": 34.56, "price": 0.05, "inclusive_price": 0.01},
+          "GPU": {"cost": 45.67, "price": 0.50, "inclusive_price": 0.10},
+          "SubscriptionRate": {"cost": 78.90, "price": 1.00, "inclusive_price": 0.20},
+          "EphimeralStorage": {"cost": 15.67, "price": 0.03, "inclusive_price": 0.005},
+          "from": "2024-01-01T00:00:00Z",
+          "to": "2024-01-31T23:59:59Z",
+          "interval_cost": 210.59,
+          "interval_hours": 744.0
+        }
+      },
+      "model_type": "A100",
+      "pool_id": 123,
+      "pool_name": "GPU Pool A",
+      "current_month_total_cost": 123.45,
+      "total_cost": 456.78,
+      "vendor_type": "NVIDIA"
+    }
+  }
+}
+```
+
+**Response Structure:**
+```json
+{
+  "start_date": "2024-01-01",
+  "end_date": "2024-01-31",
+  "currency_code": "USD",
+  "currency_symbol": "$",
+  "billing_by_workspace": {
+    "workspace-id": {
+      "workspace_name": "Production",
+      "instances": {
+        "instance-id": {
+          "2024-01": {
+            "Cost_Of_CPU": 12.34,
+            "Cost_Of_RAM": 8.90,
+            "Cost_Of_Disk_Storage": 5.67,
+            "Cost_Of_GPU": 45.67,
+            "Cost_Of_NetworkIn": 1.23,
+            "Cost_Of_NetworkOut": 2.34,
+            "Cost_of_Public_IP": 3.45,
+            "total_cost": 79.60
+          }
+        }
+      },
+      "total_cost": 79.60
+    }
+  },
+  "gpuaas_billing_by_pool": {
+    "pool-id": {
+      "pool_name": "GPU Pool A",
+      "model_type": "A100",
+      "vendor_type": "NVIDIA",
+      "intervals": {
+        "2024-01": {
+          "Cost_Of_GPUConsumed": 78.90,
+          "Cost_Of_vRAMConsumed": 12.34,
+          "Cost_Of_TotalTFlopsConsumed": 45.67,
+          "GPU_Pool_Hours": 24.0,
+          "total_cost": 136.91
+        }
+      },
+      "total_cost": 136.91
+    }
+  },
+  "pci_devices": {
+    "total_billing": 234.56,
+    "pci_devices": {
+      "card-id": {
+        "2024-01-01": {
+          "total_hours": 24.0,
+          "total_cost": 48.0,
+          "vm_usage": [
+            {
+              "VMID": "vm-123",
+              "Hours": 12.0,
+              "Cost": 24.0
+            }
+          ]
+        }
+      }
+    }
+  },
+  "team_metrics": {
+    "2024-01": {
+      "RAM": 15.67,
+      "CPU": 8.90,
+      "GPU": 25.34,
+      "GRAM": 12.45,
+      "TFlops": 18.23,
+      "total_cost": 80.59
+    }
+  },
+  "current_month_total_cost": 567.89,
+  "total_billing": 1234.56,
+  "gpuaas_total_cost": 456.78,
+  "pricing_policy": "policy-uuid",
+  "resource_policy": "policy-uuid",
+  "monthly_cost": 999.99
+}
+```
+
+### Additional Endpoints
+
+#### Detailed Team Billing
+```
+GET /team-billing/{team_id}/{start_date}/{end_date}/{interval}
+```
+
+#### Workspace Billing
+```
+GET /workspace-billing/{workspace_id}/{start_date}/{end_date}/{interval}
+```
+
+## Implementation Details
+
+### Enhanced Cron Job (`hostedai_cron.php`)
+
+The cron job has been updated to:
+
+1. **Extract Enhanced Cost Data**: Parse new cost breakdown fields
+2. **Handle GPUaaS Pools**: Process pool-specific billing with detailed metrics
+3. **Process PCI Devices**: Include GPU card usage in invoices
+4. **Include Team Metrics**: Add team-level resource costs
+5. **Enhanced Logging**: Log policy information and detailed billing data
+6. **Error Handling**: Graceful handling of missing or malformed data
+
+### Key Code Changes
+
+#### Enhanced Cost Extraction
+```php
+// Extract enhanced cost breakdown
+$costBreakdown = [
+    'CPU' => $instance['Cost_Of_CPU'] ?? 0,
+    'RAM' => $instance['Cost_Of_RAM'] ?? 0,
+    'Storage' => $instance['Cost_Of_Disk_Storage'] ?? 0,
+    'GPU' => $instance['Cost_Of_GPU'] ?? 0,
+    'NetworkIn' => $instance['Cost_Of_NetworkIn'] ?? 0,
+    'NetworkOut' => $instance['Cost_Of_NetworkOut'] ?? 0,
+    'PublicIP' => $instance['Cost_of_Public_IP'] ?? 0
+];
+```
+
+#### GPUaaS Pool Processing
+```php
+// Process GPUaaS pool billing
+if (isset($billingData['gpuaas_billing_by_pool'])) {
+    foreach ($billingData['gpuaas_billing_by_pool'] as $poolId => $poolData) {
+        $poolCosts = [
+            'GPUConsumed' => $poolData['intervals'][$month]['Cost_Of_GPUConsumed'] ?? 0,
+            'vRAMConsumed' => $poolData['intervals'][$month]['Cost_Of_vRAMConsumed'] ?? 0,
+            'TFlopsConsumed' => $poolData['intervals'][$month]['Cost_Of_TotalTFlopsConsumed'] ?? 0,
+            'PoolHours' => $poolData['intervals'][$month]['GPU_Pool_Hours'] ?? 0
+        ];
+    }
+}
+```
+
+#### PCI Device Billing
+```php
+// Process PCI device billing
+if (isset($billingData['pci_devices']['pci_devices'])) {
+    foreach ($billingData['pci_devices']['pci_devices'] as $cardId => $cardData) {
+        foreach ($cardData as $date => $usage) {
+            $pciCost += $usage['total_cost'];
+            // Process VM-specific usage
+            foreach ($usage['vm_usage'] as $vmUsage) {
+                // Track per-VM costs
+            }
+        }
+    }
+}
+```
+
+#### Team Metrics Integration
+```php
+// Process team metrics
+if (isset($billingData['team_metrics'])) {
+    foreach ($billingData['team_metrics'] as $month => $metrics) {
+        $teamCosts = [
+            'RAM' => $metrics['RAM'] ?? 0,
+            'CPU' => $metrics['CPU'] ?? 0,
+            'GPU' => $metrics['GPU'] ?? 0,
+            'GRAM' => $metrics['GRAM'] ?? 0,
+            'TFlops' => $metrics['TFlops'] ?? 0
+        ];
+    }
+}
+```
+
+### Invoice Generation Enhancements
+
+#### Detailed Line Items
+The invoice generation now includes:
+
+1. **Instance-level breakdown** with per-resource costs
+2. **GPUaaS pool charges** with pool metadata
+3. **PCI device usage** with card-specific details
+4. **Team metrics** aggregated costs
+5. **Policy information** for transparency
+
+#### Enhanced Description Format
+```php
+$description = sprintf(
+    "HostedAI Usage - %s\n" .
+    "Period: %s to %s\n" .
+    "Workspace: %s\n" .
+    "Instance: %s\n" .
+    "CPU: $%.2f | RAM: $%.2f | Storage: $%.2f\n" .
+    "GPU: $%.2f | Network: $%.2f | Public IP: $%.2f\n" .
+    "Pricing Policy: %s\n" .
+    "Currency: %s",
+    $workspaceName,
+    $startDate,
+    $endDate,
+    $workspaceName,
+    $instanceId,
+    $costBreakdown['CPU'],
+    $costBreakdown['RAM'],
+    $costBreakdown['Storage'],
+    $costBreakdown['GPU'],
+    $costBreakdown['NetworkIn'] + $costBreakdown['NetworkOut'],
+    $costBreakdown['PublicIP'],
+    $pricingPolicy,
+    $currencyCode
+);
+```
+
+### Error Handling and Logging
+
+#### Enhanced Error Handling
+```php
+try {
+    // Billing API call
+    $billingData = $this->getBillingData($teamId, $startDate, $endDate);
+    
+    if (!$billingData || !isset($billingData['billing_by_workspace'])) {
+        logActivity('HostedAI Cron: No billing data found for team ' . $teamId);
+        continue;
+    }
+    
+    // Process billing data with validation
+    $this->processBillingData($billingData, $teamId);
+    
+} catch (Exception $e) {
+    logActivity('HostedAI Cron Error for team ' . $teamId . ': ' . $e->getMessage());
+    // Continue processing other teams
+    continue;
+}
+```
+
+#### Comprehensive Logging
+```php
+// Log detailed billing information
+logActivity(sprintf(
+    'HostedAI Billing Processed - Team: %s, Total: $%.2f, Currency: %s, Policy: %s',
+    $teamId,
+    $totalCost,
+    $currencyCode,
+    $pricingPolicy
+));
+
+// Log cost breakdown
+logActivity(sprintf(
+    'Cost Breakdown - CPU: $%.2f, RAM: $%.2f, GPU: $%.2f, Storage: $%.2f',
+    $costBreakdown['CPU'],
+    $costBreakdown['RAM'],
+    $costBreakdown['GPU'],
+    $costBreakdown['Storage']
+));
+```
+
+## Testing and Validation
+
+### Test Cases
+
+1. **Basic Instance Billing**
+   - Verify cost breakdown calculation
+   - Validate currency handling
+   - Test policy application
+
+2. **GPUaaS Pool Billing**
+   - Test pool-specific cost calculation
+   - Verify metadata inclusion
+   - Validate hour tracking
+
+3. **PCI Device Billing**
+   - Test card-specific billing
+   - Verify VM usage breakdown
+   - Validate reassignment handling
+
+4. **Team Metrics**
+   - Test aggregated team costs
+   - Verify metric calculation
+   - Validate monthly aggregation
+
+5. **Error Scenarios**
+   - Missing billing data
+   - Malformed API responses
+   - Network connectivity issues
+   - Invalid team IDs
+
+### Validation Procedures
+
+1. **Data Integrity**
+   - Verify cost calculations match API data
+   - Validate currency conversions
+   - Check policy application
+
+2. **Invoice Accuracy**
+   - Compare generated invoices with billing data
+   - Verify line item descriptions
+   - Validate total calculations
+
+3. **Performance Testing**
+   - Test with large datasets
+   - Verify cron job completion times
+   - Monitor memory usage
+
+## Migration and Deployment
+
+### Database Changes
+No database schema changes are required for this enhancement.
+
+### Configuration Updates
+Ensure the following configuration parameters are set:
+
+```php
+// API endpoint configuration
+$config['api_base_url'] = 'https://api.hostedai.com/v1/';
+$config['api_timeout'] = 30;
+$config['billing_currency'] = 'USD';
+
+// Billing configuration
+$config['include_gpu_billing'] = true;
+$config['include_pci_billing'] = true;
+$config['include_team_metrics'] = true;
+$config['detailed_cost_breakdown'] = true;
+```
+
+### Deployment Steps
+
+1. **Backup Current System**
+   - Backup WHMCS database
+   - Backup module files
+   - Document current configuration
+
+2. **Deploy Enhanced Module**
+   - Update cron job file
+   - Deploy enhanced billing logic
+   - Update configuration files
+
+3. **Test Integration**
+   - Run test billing cycles
+   - Verify invoice generation
+   - Validate cost calculations
+
+4. **Monitor and Validate**
+   - Monitor cron job execution
+   - Validate billing accuracy
+   - Check error logs
+
+## Troubleshooting
+
+### Common Issues
+
+1. **Missing Cost Data**
+   - Check API endpoint availability
+   - Verify team ID validity
+   - Validate date range parameters
+
+2. **Incorrect Calculations**
+   - Verify currency handling
+   - Check policy application
+   - Validate cost breakdown logic
+
+3. **Performance Issues**
+   - Monitor API response times
+   - Check cron job execution time
+   - Optimize data processing
+
+### Debug Information
+
+Enable detailed logging by setting:
+```php
+$config['debug_billing'] = true;
+$config['log_api_responses'] = true;
+```
+
+This will log:
+- API request/response details
+- Cost calculation steps
+- Invoice generation process
+- Error details and stack traces
+```

---

## Summary

**Files changed:** 1  
**Insertions:** +395  
**Deletions:** 0  
**Net change:** +395 lines

### Changes Overview:
- ✅ **ENHANCED_BILLING_INTEGRATION.md** - New comprehensive documentation file for enhanced billing integration
  - Documents new API endpoints for shared storage and GPUaaS pool billing
  - Includes detailed response structures and implementation examples
  - Provides testing procedures and troubleshooting guides
  - Covers enhanced cost breakdown, PCI device billing, and team metrics integration