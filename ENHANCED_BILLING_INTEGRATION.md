# Enhanced Billing API Integration for WHMCS

## Overview

This document outlines the enhanced billing integration between the HostedAI WHMCS module and the updated HostedAI billing API. The integration now supports comprehensive cost tracking including GPU resources, network usage, PCI devices, and team-level metrics.

## Key Enhancements

### 1. Enhanced Instance Cost Breakdown

The billing now includes detailed per-resource costs:

- **Cost_Of_CPU**: CPU usage costs
- **Cost_Of_RAM**: Memory usage costs  
- **Cost_Of_Disk_Storage**: Storage costs
- **Cost_Of_GPU**: GPU usage costs
- **Cost_Of_NetworkIn**: Inbound network costs
- **Cost_Of_NetworkOut**: Outbound network costs
- **Cost_of_Public_IP**: Public IP address costs

### 2. GPUaaS Pool Billing

Enhanced GPU pool billing with detailed metrics:

- **Cost_Of_GPUConsumed**: GPU subscription costs
- **Cost_Of_vRAMConsumed**: Video memory consumption costs
- **Cost_Of_TotalTFlopsConsumed**: Computational power costs
- **GPU_Pool_Hours**: Pool usage hours
- Pool metadata (name, model type, vendor type)

### 3. PCI Device (GPU Card) Billing

New support for individual GPU card billing:

- Per-card usage tracking
- VM-specific usage breakdown
- Hourly usage and cost details
- Card reassignment tracking

### 4. Team Metrics Integration

Team-level resource usage billing:

- **RAM**: Team RAM consumption
- **CPU**: Team CPU usage
- **GPU**: Team GPU utilization
- **GRAM**: Team GPU memory usage
- **TFlops**: Team computational usage

### 5. Policy and Currency Information

Enhanced transparency with:

- **pricing_policy**: Applied pricing policy ID
- **resource_policy**: Applied resource policy ID
- **currency_code**: Billing currency (e.g., USD)
- **currency_symbol**: Currency symbol (e.g., $)
- **current_month_total_cost**: Current month spending
- **monthly_cost**: Estimated monthly cost

## API Endpoints Used

### Primary Billing Endpoint
```
GET /team-billing/group-by-workspace/{team_id}/{start_date}/{end_date}/monthly
```

### Shared Storage Billing Endpoint
```
GET /team-billing/shared-storage/{team_id}/{start_date}/{end_date}/{interval}/{region_id}
```

**Response Structure:**
```json
{
  "team_id": "team-uuid",
  "start_date": "2024-01-01",
  "end_date": "2024-01-31", 
  "interval": "monthly",
  "region_id": "region-id",
  "total_billing": 123.45,
  "currency_code": "USD",
  "currency_symbol": "$",
  "details": {
    "shared-volume-name": {
      "2024-01": {
        "cost": 45.67,
        "hours": 744.0,
        "from": "2024-01-01T00:00:00Z",
        "to": "2024-01-31T23:59:59Z"
      }
    }
  }
}
```

### Enhanced GPUaaS Pool Billing Endpoint
```
GET /team-billing/gpuaas-pool/{team_id}/{start_date}/{end_date}/{interval}/{region_id}
```

**Response Structure:**
```json
{
  "team_id": "team-uuid",
  "start_date": "2024-01-01",
  "end_date": "2024-01-31",
  "interval": "monthly", 
  "total_billing": 456.78,
  "current_month_total_cost": 123.45,
  "currency_code": "USD",
  "currency_symbol": "$",
  "details": {
    "pool-name": {
      "intervals": {
        "2024-01": {
          "vRAM": {"cost": 12.34, "price": 0.02, "inclusive_price": 0.005},
          "RAM": {"cost": 23.45, "price": 0.01, "inclusive_price": 0.002},
          "CPU": {"cost": 34.56, "price": 0.05, "inclusive_price": 0.01},
          "GPU": {"cost": 45.67, "price": 0.50, "inclusive_price": 0.10},
          "SubscriptionRate": {"cost": 78.90, "price": 1.00, "inclusive_price": 0.20},
          "EphimeralStorage": {"cost": 15.67, "price": 0.03, "inclusive_price": 0.005},
          "from": "2024-01-01T00:00:00Z",
          "to": "2024-01-31T23:59:59Z",
          "interval_cost": 210.59,
          "interval_hours": 744.0
        }
      },
      "model_type": "A100",
      "pool_id": 123,
      "pool_name": "GPU Pool A",
      "current_month_total_cost": 123.45,
      "total_cost": 456.78,
      "vendor_type": "NVIDIA"
    }
  }
}
```

**Response Structure:**
```json
{
  "start_date": "2024-01-01",
  "end_date": "2024-01-31",
  "currency_code": "USD",
  "currency_symbol": "$",
  "billing_by_workspace": {
    "workspace-id": {
      "workspace_name": "Production",
      "instances": {
        "instance-id": {
          "2024-01": {
            "Cost_Of_CPU": 12.34,
            "Cost_Of_RAM": 8.90,
            "Cost_Of_Disk_Storage": 5.67,
            "Cost_Of_GPU": 45.67,
            "Cost_Of_NetworkIn": 1.23,
            "Cost_Of_NetworkOut": 2.34,
            "Cost_of_Public_IP": 3.45,
            "total_cost": 79.60
          }
        }
      },
      "total_cost": 79.60
    }
  },
  "gpuaas_billing_by_pool": {
    "pool-id": {
      "pool_name": "GPU Pool A",
      "model_type": "A100",
      "vendor_type": "NVIDIA",
      "intervals": {
        "2024-01": {
          "Cost_Of_GPUConsumed": 78.90,
          "Cost_Of_vRAMConsumed": 12.34,
          "Cost_Of_TotalTFlopsConsumed": 45.67,
          "GPU_Pool_Hours": 24.0,
          "total_cost": 136.91
        }
      },
      "total_cost": 136.91
    }
  },
  "pci_devices": {
    "total_billing": 234.56,
    "pci_devices": {
      "card-id": {
        "2024-01-01": {
          "total_hours": 24.0,
          "total_cost": 48.0,
          "vm_usage": [
            {
              "VMID": "vm-123",
              "Hours": 12.0,
              "Cost": 24.0
            }
          ]
        }
      }
    }
  },
  "team_metrics": {
    "2024-01": {
      "RAM": 15.67,
      "CPU": 8.90,
      "GPU": 25.34,
      "GRAM": 12.45,
      "TFlops": 18.23,
      "total_cost": 80.59
    }
  },
  "current_month_total_cost": 567.89,
  "total_billing": 1234.56,
  "gpuaas_total_cost": 456.78,
  "pricing_policy": "policy-uuid",
  "resource_policy": "policy-uuid",
  "monthly_cost": 999.99
}
```

### Additional Endpoints

#### Detailed Team Billing
```
GET /team-billing/{team_id}/{start_date}/{end_date}/{interval}
```

#### Workspace Billing
```
GET /workspace-billing/{workspace_id}/{start_date}/{end_date}/{interval}
```

## Implementation Details

### Enhanced Cron Job (`hostedai_cron.php`)

The cron job has been updated to:

1. **Extract Enhanced Cost Data**: Parse new cost breakdown fields
2. **Handle GPUaaS Pools**: Process pool-specific billing with detailed metrics
3. **Process PCI Devices**: Include GPU card usage in invoices
4. **Include Team Metrics**: Add team-level resource costs
5. **Enhanced Logging**: Log policy information and detailed billing data
6. **Error Handling**: Graceful handling of missing or malformed data

### Helper Class Enhancements (`Helper.php`)

New methods added:

- `generateDetailedTeamBill()`: Fetch detailed team billing data
- `getWorkspaceBilling()`: Get workspace-specific billing
- Enhanced error handling and logging

### Invoice Generation

Enhanced invoice items now include:

1. **Monthly Base Cost**: Fixed monthly service fees
2. **Instance Costs**: Detailed per-resource breakdown
3. **GPUaaS Pool Costs**: Pool-specific GPU billing
4. **PCI Device Costs**: Individual GPU card billing
5. **Team Metrics**: Team-level resource usage

## Testing

### Test Suite (`enhanced_billing_test.php`)

Comprehensive test script that validates:

- API response structure
- New billing fields
- Data integrity
- Invoice generation simulation
- Error handling

### Running Tests

```bash
php crons/enhanced_billing_test.php
```

## Migration from Previous Version

### Removed Features

- **Shared Storage Billing**: No longer supported
- **Ephemeral Storage Distinction**: Merged into standard storage billing
- **Complex Decimal Precision**: Simplified to standard floats

### Backward Compatibility

The integration maintains backward compatibility by:

- Using null coalescing operators (`??`) for new fields
- Graceful fallbacks for missing data
- Maintaining existing invoice structure

### Migration Steps

1. **Update WHMCS Module**: Deploy enhanced billing code
2. **Test Integration**: Run test suite to validate functionality
3. **Monitor Invoices**: Check first month's invoices for accuracy
4. **Update Documentation**: Inform customers of enhanced billing details

## Error Handling

### Common Issues and Solutions

1. **Missing Billing Data**
   - Fallback to empty arrays/zero values
   - Log warnings for investigation
   - Continue processing other items

2. **API Timeouts**
   - Retry logic in Helper class
   - Graceful degradation
   - Error logging for monitoring

3. **Invalid Response Structure**
   - Validate response before processing
   - Use default values for missing fields
   - Log structural issues

### Logging

Enhanced logging includes:

- Policy information (pricing and resource policies)
- Currency details
- Cost breakdowns by category
- Error details with context
- Processing statistics

## Configuration

### WHMCS Product Configuration

No changes required to existing product configuration. The enhanced billing automatically detects and processes new data fields.

### Server Configuration

Ensure the server configuration points to the updated HostedAI API endpoints that support the enhanced billing structure.

## Monitoring and Maintenance

### Key Metrics to Monitor

1. **Invoice Generation Success Rate**
2. **Billing Data Completeness**
3. **API Response Times**
4. **Error Rates by Category**

### Regular Maintenance

1. **Monthly**: Review invoice accuracy
2. **Quarterly**: Validate policy assignments
3. **As Needed**: Update for new billing features

## Support and Troubleshooting

### Common Troubleshooting Steps

1. **Check API Connectivity**: Verify server configuration
2. **Validate Team Data**: Ensure teams exist in both systems
3. **Review Logs**: Check WHMCS activity logs for errors
4. **Test Billing**: Use test suite to validate functionality

### Support Resources

- **Test Suite**: `crons/enhanced_billing_test.php`
- **Activity Logs**: WHMCS System Logs
- **API Documentation**: HostedAI API docs
- **Module Logs**: WHMCS Module Debug Logs

## Future Enhancements

### Planned Features

1. **Real-time Billing**: Live cost tracking
2. **Custom Billing Periods**: Flexible billing cycles
3. **Cost Alerts**: Automated cost threshold notifications
4. **Detailed Reporting**: Enhanced billing reports

### API Evolution

The integration is designed to be forward-compatible with future API enhancements through:

- Flexible data parsing
- Optional field handling
- Extensible invoice generation
- Modular billing components

---

**Note**: This integration provides comprehensive billing transparency while maintaining the simplicity and reliability expected from WHMCS billing automation.