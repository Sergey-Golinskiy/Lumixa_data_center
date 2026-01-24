# Batch Inventory Management

## Overview

Lumixa LMS supports full batch (lot) inventory tracking with multiple costing methods. This allows for accurate cost accounting and traceability of inventory movements.

## Key Concepts

### Inventory Batches

Each receipt of inventory creates a new batch with:
- **Batch Code**: Unique identifier (auto-generated or manual)
- **Received Date**: When the batch was received
- **Quantity**: Amount received and currently available
- **Unit Cost**: Purchase cost per unit
- **Supplier**: Where the batch came from
- **Expiry Date**: Optional expiration tracking
- **Status**: active, depleted, expired, or quarantine

### Costing Methods

The system supports four costing methods for inventory valuation:

#### 1. FIFO (First In, First Out)
- **Default method**
- Issues inventory from the oldest batches first
- Most common for physical goods
- Matches typical warehouse flow

**Example:**
```
Batch A: 100 units @ $5.00 (received 2024-01-01)
Batch B: 100 units @ $6.00 (received 2024-01-10)

Issue 150 units:
- 100 units from Batch A @ $5.00 = $500
- 50 units from Batch B @ $6.00 = $300
- Total cost: $800
- Average cost: $5.33/unit
```

#### 2. LIFO (Last In, First Out)
- Issues inventory from the newest batches first
- Less common but useful for certain scenarios
- May better match current market prices

**Example:**
```
Batch A: 100 units @ $5.00 (received 2024-01-01)
Batch B: 100 units @ $6.00 (received 2024-01-10)

Issue 150 units:
- 100 units from Batch B @ $6.00 = $600
- 50 units from Batch A @ $5.00 = $250
- Total cost: $850
- Average cost: $5.67/unit
```

#### 3. Weighted Average
- Calculates average cost across all available batches
- Issues using the weighted average cost
- Physical batches still tracked, but cost is averaged

**Example:**
```
Batch A: 100 units @ $5.00 = $500
Batch B: 100 units @ $6.00 = $600
Total: 200 units, $1100

Weighted average: $1100 / 200 = $5.50/unit

Issue 150 units @ $5.50 = $825
```

#### 4. Manual Allocation
- User manually selects which batches to issue from
- Provides complete control over costing
- Useful for:
  - Specific quality requirements
  - Customer-specific batching
  - Expiry date management
  - Testing different scenarios

**Example:**
```
User selects:
- 75 units from Batch B @ $6.00 = $450
- 75 units from Batch A @ $5.00 = $375
- Total cost: $825
- Average cost: $5.50/unit
```

## Configuration

### Setting Default Costing Method

**For the entire system:**
1. Go to Admin → Settings
2. Set `inventory_issue_method` to: FIFO, LIFO, WEIGHTED_AVG, or MANUAL
3. Set `inventory_allow_issue_method_override` to allow per-document overrides

**For individual items:**
1. Edit an item in Warehouse → Items
2. Set "Costing Method" (FIFO, LIFO, Weighted Average, or Manual)
3. Check "Allow Method Override" to permit document-level changes

### Costing Method Priority

When issuing inventory, the system uses this priority:
1. **Document-level override** (if allowed and specified)
2. **Item-level setting** (costing_method field)
3. **System default** (inventory_issue_method setting)

## Workflow

### Receiving Inventory (Creating Batches)

**Automatic batch creation:**
1. Create a Receipt document
2. Add items with quantities and unit prices
3. Post the document
4. System automatically creates batches for each line

**Manual batch creation:**
1. Go to Warehouse → Items → [Select Item] → Batches
2. Click "Create Batch"
3. Enter batch details:
   - Batch code (or leave empty for auto-generation)
   - Quantity received
   - Unit cost
   - Received date
   - Supplier (optional)
   - Expiry date (optional)
   - Notes
4. Save

### Issuing Inventory (Consuming Batches)

**Automatic allocation (FIFO/LIFO/Weighted Average):**
1. Create an Issue document
2. Add items and quantities
3. Select costing method (optional, if override allowed)
4. Post the document
5. System automatically allocates from batches using the selected method
6. Document lines are updated with actual costs

**Manual allocation:**
1. Create an Issue document
2. Set costing method to "Manual"
3. For each line item, click "Select Batches"
4. Choose batches and quantities to allocate
5. System validates selection matches line quantity
6. Post the document

### Viewing Batch Information

**Item-level:**
- Warehouse → Items → [Select Item] → Batches
- Shows all batches for the item
- Displays available quantities, costs, and status

**Batch details:**
- Click on a batch code to view:
  - Current status and quantities
  - Usage history
  - Movements (in/out)
  - Issue allocations
  - Value calculations

**Document-level:**
- When viewing a posted Issue document
- Shows which batches were allocated
- Displays cost breakdown by batch

## Batch Status Management

### Active
- Normal operational status
- Can be issued from

### Quarantine
- Temporarily blocked from use
- Cannot be issued until returned to Active
- Use for quality holds, investigation, etc.

**Setting to Quarantine:**
1. View batch details
2. Click "Set to Quarantine"
3. Batch cannot be selected for new issues

### Expired
- Permanently blocked from use
- Cannot be returned to Active
- Use for expired materials

**Setting to Expired:**
1. View batch details
2. Click "Mark as Expired"
3. Batch cannot be issued

### Depleted
- Automatically set when quantity reaches zero
- Indicates batch is fully consumed
- Cannot be issued from

## Expiry Date Tracking

Enable expiry tracking in Admin → Settings:
- `inventory_track_expiry`: Enable/disable expiry tracking
- `inventory_block_expired`: Prevent issuing from expired batches
- `inventory_warn_expiry_days`: Days before expiry to show warning (default: 30)

**Features:**
- Visual indicators for expiring/expired batches
- Automatic expiry warnings
- Optional blocking of expired batch allocation
- Expiry date display in batch lists and details

## Reports and Analytics

### Batch Valuation
- Current value by batch
- Total inventory value
- Cost distribution

### Batch Movement History
- All movements for an item
- Filtered by date, batch, or document
- Audit trail for tracking

### Issue Cost Analysis
- Compare costing methods
- Actual costs vs. planned costs
- Cost trends over time

## Best Practices

### Batch Codes
- Use meaningful codes (e.g., SKU-DATE-SUPPLIER)
- Enable auto-generation for consistency
- Include date information for sorting

### Costing Methods
- **FIFO**: Best for most scenarios, matches physical flow
- **LIFO**: Use when prices are rapidly changing upward
- **Weighted Average**: Good for commodities, simplifies accounting
- **Manual**: Use for special cases, customer requirements, quality control

### Expiry Management
- Set expiry dates on perishable items
- Enable blocking of expired batches
- Review expiry warnings regularly
- Use quarantine for items needing inspection

### Documentation
- Add notes to batches for traceability
- Reference supplier invoices in batch notes
- Document quality issues in batch status changes

## API Integration

For programmatic access to batch functionality:

```php
use App\Services\Warehouse\BatchService;

$batchService = new BatchService($app);

// Create batch
$batch = $batchService->createBatch([
    'item_id' => 123,
    'quantity' => 100,
    'unit_cost' => 5.99,
    'received_date' => '2024-01-15',
    'supplier_id' => 45
]);

// Get available batches
$batches = $batchService->getAvailableBatches($itemId);

// Allocate batches (FIFO)
$allocations = $batchService->allocateBatches(
    $itemId,
    $quantity,
    BatchService::METHOD_FIFO
);

// Manual allocation
$allocations = $batchService->allocateBatches(
    $itemId,
    $quantity,
    BatchService::METHOD_MANUAL,
    null,
    null,
    [
        ['batch_id' => 101, 'quantity' => 50],
        ['batch_id' => 102, 'quantity' => 50]
    ]
);
```

## Database Schema

### inventory_batches
- Stores batch information
- Links to items and suppliers
- Tracks quantities and costs

### inventory_batch_movements
- Immutable log of all batch movements
- Links to documents for traceability
- Records quantity changes and balances

### inventory_issue_allocations
- Records which batches were used for each issue
- Stores actual costs and allocation method
- Links to documents and document lines

### inventory_batch_reservations
- Tracks reserved quantities per batch
- Prevents over-allocation
- Links to production orders, print jobs, etc.

## Troubleshooting

### "Insufficient stock" error when posting Issue
- Check available quantity vs. required quantity
- Verify batches exist for the item
- Check batch status (expired/quarantine batches can't be issued)

### Manual allocation validation errors
- Ensure selected batch quantities sum to line quantity
- Verify batches have sufficient available quantity
- Check batch status is Active

### Incorrect costing
- Verify costing method is set correctly
- Check if method override is allowed/used
- Review batch costs and received dates
- Ensure batches were created with correct costs

### Missing batches in selection
- Check batch status (only Active batches shown)
- Verify available quantity > 0
- Check expiry settings if expiry blocking is enabled

## Future Enhancements

Planned features for future releases:
- Batch splitting and merging
- Inter-batch transfers
- Batch genealogy/traceability chains
- Cost adjustment workflows
- Batch reservation for production
- Multi-location batch tracking
- Quality hold workflows
- Batch reclassification

---

For questions or support, contact your system administrator or refer to the main documentation.
