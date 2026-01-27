<?php
/**
 * DetailCostingService - Calculate printing/production cost for details
 */

namespace App\Services\Catalog;

use App\Core\Application;
use App\Core\Database;

class DetailCostingService
{
    private Database $db;

    public function __construct(Application $app)
    {
        $this->db = $app->getDatabase();
    }

    /**
     * Calculate full production cost for a detail
     *
     * @param array $detail Detail data with printer_id, material_item_id, material_qty_grams, print_time_minutes
     * @param float $laborCost Optional labor cost from operations
     * @return array Cost breakdown with total and individual components
     */
    public function calculateCost(array $detail, float $laborCost = 0): array
    {
        $result = [
            'material_cost' => 0,
            'material_cost_per_gram' => 0,
            'electricity_cost' => 0,
            'amortization_cost' => 0,
            'maintenance_cost' => 0,
            'labor_cost' => $laborCost,
            'total_cost' => 0,
            'has_data' => false,
            'missing_data' => [],
        ];

        // Check if this is a printed detail
        if (($detail['detail_type'] ?? '') !== 'printed') {
            $result['missing_data'][] = 'not_printed_detail';
            return $result;
        }

        $printTimeHours = ((float)($detail['print_time_minutes'] ?? 0)) / 60;
        $materialQtyGrams = (float)($detail['material_qty_grams'] ?? 0);

        if ($materialQtyGrams <= 0) {
            $result['missing_data'][] = 'material_qty';
        }

        if ($printTimeHours <= 0) {
            $result['missing_data'][] = 'print_time';
        }

        // Get material cost (weighted average from stock)
        $materialCostPerGram = 0;
        if (!empty($detail['material_item_id'])) {
            $materialCostPerGram = $this->getMaterialCostPerGram((int)$detail['material_item_id']);
            if ($materialCostPerGram <= 0) {
                $result['missing_data'][] = 'material_price';
            }
        } else {
            $result['missing_data'][] = 'material_not_selected';
        }

        $result['material_cost_per_gram'] = $materialCostPerGram;
        $result['material_cost'] = $materialQtyGrams * $materialCostPerGram;

        // Get printer costs
        $printerData = null;
        if (!empty($detail['printer_id'])) {
            $printerData = $this->getPrinterData((int)$detail['printer_id']);
            if (!$printerData) {
                $result['missing_data'][] = 'printer_not_found';
            }
        } else {
            $result['missing_data'][] = 'printer_not_selected';
        }

        if ($printerData && $printTimeHours > 0) {
            // Electricity cost: (watts / 1000) * hours * cost_per_kwh
            $powerKw = ((float)($printerData['power_watts'] ?? 0)) / 1000;
            $electricityCostPerKwh = (float)($printerData['electricity_cost_per_kwh'] ?? 0);
            $result['electricity_cost'] = $powerKw * $printTimeHours * $electricityCostPerKwh;

            // Amortization cost: hours * amortization_per_hour
            $result['amortization_cost'] = $printTimeHours * (float)($printerData['amortization_per_hour'] ?? 0);

            // Maintenance cost: hours * maintenance_per_hour
            $result['maintenance_cost'] = $printTimeHours * (float)($printerData['maintenance_per_hour'] ?? 0);

            // Store printer data for display
            $result['printer'] = [
                'name' => $printerData['name'] ?? '',
                'model' => $printerData['model'] ?? '',
                'power_watts' => (float)($printerData['power_watts'] ?? 0),
                'electricity_cost_per_kwh' => $electricityCostPerKwh,
                'amortization_per_hour' => (float)($printerData['amortization_per_hour'] ?? 0),
                'maintenance_per_hour' => (float)($printerData['maintenance_per_hour'] ?? 0),
            ];
        }

        // Calculate total
        $result['total_cost'] = $result['material_cost']
            + $result['electricity_cost']
            + $result['amortization_cost']
            + $result['maintenance_cost']
            + $result['labor_cost'];

        // Mark as having data if we have a meaningful cost
        $result['has_data'] = $result['total_cost'] > 0 || empty($result['missing_data']);

        // Add calculation details for display
        $result['calculation_details'] = [
            'material_qty_grams' => $materialQtyGrams,
            'print_time_minutes' => (int)($detail['print_time_minutes'] ?? 0),
            'print_time_hours' => round($printTimeHours, 2),
        ];

        return $result;
    }

    /**
     * Get material cost per gram from weighted average stock price
     */
    private function getMaterialCostPerGram(int $materialItemId): float
    {
        // Get item with its unit and stock balance
        $item = $this->db->fetch(
            "SELECT i.unit, COALESCE(sb.avg_cost, 0) as avg_cost
             FROM items i
             LEFT JOIN stock_balances sb ON sb.item_id = i.id
             WHERE i.id = ?",
            [$materialItemId]
        );

        if (!$item || (float)$item['avg_cost'] <= 0) {
            return 0;
        }

        $avgCost = (float)$item['avg_cost'];
        $unit = $item['unit'] ?? 'g';

        // Convert cost per unit to cost per gram
        switch ($unit) {
            case 'kg':
                // avg_cost is per kg, divide by 1000 to get per gram
                return $avgCost / 1000;
            case 'g':
                // avg_cost is already per gram
                return $avgCost;
            default:
                // For other units, assume it's per gram
                return $avgCost;
        }
    }

    /**
     * Get printer data with cost parameters
     */
    private function getPrinterData(int $printerId): ?array
    {
        // Base columns that always exist
        $columns = ['id', 'name'];

        // Check for optional cost-related columns
        $optionalColumns = [
            'model',
            'power_watts',
            'electricity_cost_per_kwh',
            'amortization_per_hour',
            'maintenance_per_hour'
        ];

        foreach ($optionalColumns as $col) {
            if ($this->db->columnExists('printers', $col)) {
                $columns[] = $col;
            }
        }

        $printer = $this->db->fetch(
            "SELECT " . implode(', ', $columns) . " FROM printers WHERE id = ?",
            [$printerId]
        );

        if (!$printer) {
            return null;
        }

        // Ensure all cost columns have default values
        $defaults = [
            'model' => '',
            'power_watts' => 0,
            'electricity_cost_per_kwh' => 0,
            'amortization_per_hour' => 0,
            'maintenance_per_hour' => 0,
        ];

        foreach ($defaults as $key => $default) {
            if (!isset($printer[$key])) {
                $printer[$key] = $default;
            }
        }

        return $printer;
    }

    /**
     * Get cost breakdown for display
     */
    public function getCostBreakdown(array $costData): array
    {
        $breakdown = [];

        if ($costData['material_cost'] > 0) {
            $breakdown[] = [
                'type' => 'material',
                'label' => 'material_cost',
                'value' => $costData['material_cost'],
                'details' => sprintf(
                    '%.2f g × %.4f',
                    $costData['calculation_details']['material_qty_grams'] ?? 0,
                    $costData['material_cost_per_gram'] ?? 0
                ),
            ];
        }

        if ($costData['electricity_cost'] > 0) {
            $printer = $costData['printer'] ?? [];
            $breakdown[] = [
                'type' => 'electricity',
                'label' => 'electricity_cost',
                'value' => $costData['electricity_cost'],
                'details' => sprintf(
                    '%.0f W × %.2f h × %.4f',
                    $printer['power_watts'] ?? 0,
                    $costData['calculation_details']['print_time_hours'] ?? 0,
                    $printer['electricity_cost_per_kwh'] ?? 0
                ),
            ];
        }

        if ($costData['amortization_cost'] > 0) {
            $printer = $costData['printer'] ?? [];
            $breakdown[] = [
                'type' => 'amortization',
                'label' => 'amortization_cost',
                'value' => $costData['amortization_cost'],
                'details' => sprintf(
                    '%.2f h × %.4f',
                    $costData['calculation_details']['print_time_hours'] ?? 0,
                    $printer['amortization_per_hour'] ?? 0
                ),
            ];
        }

        if ($costData['maintenance_cost'] > 0) {
            $printer = $costData['printer'] ?? [];
            $breakdown[] = [
                'type' => 'maintenance',
                'label' => 'maintenance_cost',
                'value' => $costData['maintenance_cost'],
                'details' => sprintf(
                    '%.2f h × %.4f',
                    $costData['calculation_details']['print_time_hours'] ?? 0,
                    $printer['maintenance_per_hour'] ?? 0
                ),
            ];
        }

        if (($costData['labor_cost'] ?? 0) > 0) {
            $breakdown[] = [
                'type' => 'labor',
                'label' => 'labor_cost',
                'value' => $costData['labor_cost'],
                'details' => $costData['calculation_details']['labor_details'] ?? '',
            ];
        }

        return $breakdown;
    }
}
