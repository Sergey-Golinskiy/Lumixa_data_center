<?php
/**
 * LotsController - Lot/batch management
 */

namespace App\Controllers\Warehouse;

use App\Core\Controller;

class LotsController extends Controller
{
    /**
     * List all lots
     */
    public function index(): void
    {
        $this->requirePermission('warehouse.lots.view');

        $search = $_GET['search'] ?? '';
        $itemId = $_GET['item_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(l.lot_number LIKE ? OR i.name LIKE ? OR i.sku LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($itemId) {
            $where[] = "l.item_id = ?";
            $params[] = $itemId;
        }

        if ($status) {
            $where[] = "l.status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM lots l
             JOIN items i ON l.item_id = i.id
             WHERE {$whereClause}",
            $params
        );

        // Get lots
        $offset = ($page - 1) * $perPage;
        $lots = $this->db()->fetchAll(
            "SELECT l.*, i.sku, i.name as item_name, i.unit
             FROM lots l
             JOIN items i ON l.item_id = i.id
             WHERE {$whereClause}
             ORDER BY l.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get items for filter
        $items = $this->db()->fetchAll("SELECT id, sku, name FROM items WHERE is_active = 1 ORDER BY sku");
        $translator = $this->app->getTranslator();

        $this->render('warehouse/lots/index', [
            'title' => $translator->get('lots_management'),
            'lots' => $lots,
            'items' => $items,
            'search' => $search,
            'itemId' => $itemId,
            'status' => $status,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show lot details
     */
    public function show(string $id): void
    {
        $this->requirePermission('warehouse.lots.view');

        $lot = $this->db()->fetch(
            "SELECT l.*, i.sku, i.name as item_name, i.unit, i.category
             FROM lots l
             JOIN items i ON l.item_id = i.id
             WHERE l.id = ?",
            [$id]
        );

        if (!$lot) {
            $this->notFound();
        }

        // Get stock movements for this lot
        $movements = $this->db()->fetchAll(
            "SELECT sm.*, d.document_number, d.type as document_type
             FROM stock_movements sm
             JOIN documents d ON sm.document_id = d.id
             WHERE sm.lot_id = ?
             ORDER BY sm.created_at DESC
             LIMIT 50",
            [$id]
        );

        // Get current stock balance
        $stockBalance = $this->db()->fetch(
            "SELECT * FROM stock_balances WHERE lot_id = ?",
            [$id]
        );
        $translator = $this->app->getTranslator();

        $this->render('warehouse/lots/show', [
            'title' => $translator->get('lot_title', ['number' => $lot['lot_number']]),
            'lot' => $lot,
            'movements' => $movements,
            'stockBalance' => $stockBalance
        ]);
    }

    /**
     * Create lot form
     */
    public function create(): void
    {
        $this->requirePermission('warehouse.lots.create');

        $trackLotsEnabled = $this->db()->columnExists('items', 'track_lots');
        $itemsQuery = "SELECT id, sku, name, unit FROM items WHERE is_active = 1";
        if ($trackLotsEnabled) {
            $itemsQuery .= " AND track_lots = 1";
        }
        $itemsQuery .= " ORDER BY sku";
        $items = $this->db()->fetchAll($itemsQuery);
        $translator = $this->app->getTranslator();

        $this->render('warehouse/lots/form', [
            'title' => $translator->get('create_lot'),
            'lot' => null,
            'items' => $items
        ]);
    }

    /**
     * Store new lot
     */
    public function store(): void
    {
        $this->requirePermission('warehouse.lots.create');
        $this->validateCSRF();

        $data = [
            'item_id' => $_POST['item_id'] ?? '',
            'lot_number' => trim($_POST['lot_number'] ?? ''),
            'manufacture_date' => $_POST['manufacture_date'] ?: null,
            'expiry_date' => $_POST['expiry_date'] ?: null,
            'supplier_lot' => trim($_POST['supplier_lot'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];

        // Validation
        $errors = [];

        if (empty($data['item_id'])) {
            $errors['item_id'] = $this->app->getTranslator()->get('item_required');
        }

        if (empty($data['lot_number'])) {
            $errors['lot_number'] = $this->app->getTranslator()->get('lot_number_required');
        } else {
            // Check uniqueness per item
            $exists = $this->db()->fetch(
                "SELECT id FROM lots WHERE item_id = ? AND lot_number = ?",
                [$data['item_id'], $data['lot_number']]
            );
            if ($exists) {
                $errors['lot_number'] = $this->app->getTranslator()->get('lot_number_exists');
            }
        }

        if ($data['expiry_date'] && $data['manufacture_date']) {
            if ($data['expiry_date'] < $data['manufacture_date']) {
                $errors['expiry_date'] = $this->app->getTranslator()->get('expiry_before_manufacture');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/warehouse/lots/create');
            return;
        }

        // Check if item tracks lots
        $trackLotsEnabled = $this->db()->columnExists('items', 'track_lots');
        if ($trackLotsEnabled) {
            $item = $this->db()->fetch("SELECT track_lots FROM items WHERE id = ?", [$data['item_id']]);
            if (!$item || !$item['track_lots']) {
                $this->session->setFlash('error', $this->app->getTranslator()->get('item_no_lot_tracking'));
                $this->redirect('/warehouse/lots/create');
                return;
            }
        }

        // Create lot
        $id = $this->db()->insert('lots', [
            'item_id' => $data['item_id'],
            'lot_number' => $data['lot_number'],
            'manufacture_date' => $data['manufacture_date'],
            'expiry_date' => $data['expiry_date'],
            'supplier_lot' => $data['supplier_lot'] ?: null,
            'notes' => $data['notes'] ?: null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->audit('lot.created', 'lots', $id, null, $data);
        $this->session->setFlash('success', $this->app->getTranslator()->get('lot_created_success'));
        $this->redirect("/warehouse/lots/{$id}");
    }

    /**
     * Edit lot form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('warehouse.lots.edit');

        $lot = $this->db()->fetch(
            "SELECT l.*, i.sku, i.name as item_name
             FROM lots l
             JOIN items i ON l.item_id = i.id
             WHERE l.id = ?",
            [$id]
        );

        if (!$lot) {
            $this->notFound();
        }

        $trackLotsEnabled = $this->db()->columnExists('items', 'track_lots');
        $itemsQuery = "SELECT id, sku, name, unit FROM items WHERE is_active = 1";
        if ($trackLotsEnabled) {
            $itemsQuery .= " AND track_lots = 1";
        }
        $itemsQuery .= " ORDER BY sku";
        $items = $this->db()->fetchAll($itemsQuery);
        $translator = $this->app->getTranslator();

        $this->render('warehouse/lots/form', [
            'title' => $translator->get('edit_lot_title', ['number' => $lot['lot_number']]),
            'lot' => $lot,
            'items' => $items
        ]);
    }

    /**
     * Update lot
     */
    public function update(string $id): void
    {
        $this->requirePermission('warehouse.lots.edit');
        $this->validateCSRF();

        $lot = $this->db()->fetch("SELECT * FROM lots WHERE id = ?", [$id]);
        if (!$lot) {
            $this->notFound();
        }

        $data = [
            'lot_number' => trim($_POST['lot_number'] ?? ''),
            'manufacture_date' => $_POST['manufacture_date'] ?: null,
            'expiry_date' => $_POST['expiry_date'] ?: null,
            'supplier_lot' => trim($_POST['supplier_lot'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'status' => $_POST['status'] ?? 'active'
        ];

        // Validation
        $errors = [];

        if (empty($data['lot_number'])) {
            $errors['lot_number'] = $this->app->getTranslator()->get('lot_number_required');
        } else {
            // Check uniqueness per item
            $exists = $this->db()->fetch(
                "SELECT id FROM lots WHERE item_id = ? AND lot_number = ? AND id != ?",
                [$lot['item_id'], $data['lot_number'], $id]
            );
            if ($exists) {
                $errors['lot_number'] = $this->app->getTranslator()->get('lot_number_exists');
            }
        }

        if ($data['expiry_date'] && $data['manufacture_date']) {
            if ($data['expiry_date'] < $data['manufacture_date']) {
                $errors['expiry_date'] = $this->app->getTranslator()->get('expiry_before_manufacture');
            }
        }

        if (!in_array($data['status'], ['active', 'quarantine', 'blocked', 'expired'])) {
            $errors['status'] = $this->app->getTranslator()->get('invalid_status');
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/warehouse/lots/{$id}/edit");
            return;
        }

        // Update
        $this->db()->update('lots', [
            'lot_number' => $data['lot_number'],
            'manufacture_date' => $data['manufacture_date'],
            'expiry_date' => $data['expiry_date'],
            'supplier_lot' => $data['supplier_lot'] ?: null,
            'notes' => $data['notes'] ?: null,
            'status' => $data['status'],
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('lot.updated', 'lots', $id, $lot, $data);
        $this->session->setFlash('success', $this->app->getTranslator()->get('lot_updated_success'));
        $this->redirect("/warehouse/lots/{$id}");
    }

    /**
     * Change lot status
     */
    public function changeStatus(string $id): void
    {
        $this->requirePermission('warehouse.lots.edit');
        $this->validateCSRF();

        $lot = $this->db()->fetch("SELECT * FROM lots WHERE id = ?", [$id]);
        if (!$lot) {
            $this->notFound();
        }

        $status = $_POST['status'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if (!in_array($status, ['active', 'quarantine', 'blocked', 'expired'])) {
            $this->session->setFlash('error', $this->app->getTranslator()->get('invalid_status'));
            $this->redirect("/warehouse/lots/{$id}");
            return;
        }

        $oldStatus = $lot['status'];
        $notes = $lot['notes'] ?? '';
        if ($reason) {
            $notes .= "\n[" . date('Y-m-d H:i') . "] Status changed from {$oldStatus} to {$status}: {$reason}";
        }

        $this->db()->update('lots', [
            'status' => $status,
            'notes' => trim($notes),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('lot.status_changed', 'lots', $id, ['status' => $oldStatus], ['status' => $status, 'reason' => $reason]);
        $statusLabel = $this->app->getTranslator()->get($status);
        $this->session->setFlash('success', $this->app->getTranslator()->get('lot_status_changed', ['status' => $statusLabel]));
        $this->redirect("/warehouse/lots/{$id}");
    }

    /**
     * List expiring lots
     */
    public function expiring(): void
    {
        $this->requirePermission('warehouse.lots.view');

        $days = (int)($_GET['days'] ?? 30);

        $stockColumn = $this->db()->columnExists('stock_balances', 'on_hand') ? 'on_hand' : 'quantity';
        $lots = $this->db()->fetchAll(
            "SELECT l.*, i.sku, i.name as item_name, i.unit,
                    DATEDIFF(l.expiry_date, CURDATE()) as days_until_expiry,
                    COALESCE(sb.{$stockColumn}, 0) as stock_quantity
             FROM lots l
             JOIN items i ON l.item_id = i.id
             LEFT JOIN stock_balances sb ON l.id = sb.lot_id
             WHERE l.expiry_date IS NOT NULL
               AND l.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
               AND l.status != 'expired'
             ORDER BY l.expiry_date ASC",
            [$days]
        );
        $translator = $this->app->getTranslator();

        $this->render('warehouse/lots/expiring', [
            'title' => $translator->get('expiring_lots'),
            'lots' => $lots,
            'days' => $days
        ]);
    }
}
