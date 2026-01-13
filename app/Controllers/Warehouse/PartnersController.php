<?php
/**
 * PartnersController - Suppliers and customers management
 */

namespace App\Controllers\Warehouse;

use App\Core\Controller;

class PartnersController extends Controller
{
    /**
     * List all partners
     */
    public function index(): void
    {
        $this->requirePermission('warehouse.partners.view');

        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR code LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($type) {
            $where[] = "type = ?";
            $params[] = $type;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM partners WHERE {$whereClause}",
            $params
        );

        // Get partners
        $offset = ($page - 1) * $perPage;
        $partners = $this->db->fetchAll(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM documents WHERE partner_id = p.id) as document_count
             FROM partners p
             WHERE {$whereClause}
             ORDER BY p.name
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('warehouse/partners/index', [
            'title' => 'Partners',
            'partners' => $partners,
            'search' => $search,
            'type' => $type,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show partner details
     */
    public function show(string $id): void
    {
        $this->requirePermission('warehouse.partners.view');

        $partner = $this->db->fetch(
            "SELECT * FROM partners WHERE id = ?",
            [$id]
        );

        if (!$partner) {
            $this->notFound();
        }

        // Get recent documents
        $documents = $this->db->fetchAll(
            "SELECT d.*, u.username as created_by_name
             FROM documents d
             LEFT JOIN users u ON d.created_by = u.id
             WHERE d.partner_id = ?
             ORDER BY d.created_at DESC
             LIMIT 20",
            [$id]
        );

        // Get statistics
        $stats = $this->db->fetch(
            "SELECT
                COUNT(*) as total_documents,
                SUM(CASE WHEN type = 'receipt' THEN total_amount ELSE 0 END) as total_receipts,
                SUM(CASE WHEN type = 'shipment' THEN total_amount ELSE 0 END) as total_shipments
             FROM documents
             WHERE partner_id = ? AND status = 'posted'",
            [$id]
        );

        $this->render('warehouse/partners/show', [
            'title' => $partner['name'],
            'partner' => $partner,
            'documents' => $documents,
            'stats' => $stats
        ]);
    }

    /**
     * Create partner form
     */
    public function create(): void
    {
        $this->requirePermission('warehouse.partners.create');

        $this->render('warehouse/partners/form', [
            'title' => 'Create Partner',
            'partner' => null
        ]);
    }

    /**
     * Store new partner
     */
    public function store(): void
    {
        $this->requirePermission('warehouse.partners.create');
        $this->validateCSRF();

        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'supplier',
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'tax_id' => trim($_POST['tax_id'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validation
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (!in_array($data['type'], ['supplier', 'customer', 'both'])) {
            $errors['type'] = 'Invalid partner type';
        }

        if ($data['code']) {
            $exists = $this->db->fetch(
                "SELECT id FROM partners WHERE code = ?",
                [$data['code']]
            );
            if ($exists) {
                $errors['code'] = 'Code already exists';
            }
        }

        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/warehouse/partners/create');
            return;
        }

        // Generate code if empty
        if (empty($data['code'])) {
            $prefix = strtoupper(substr($data['type'], 0, 1));
            $count = $this->db->fetchColumn("SELECT COUNT(*) FROM partners WHERE type = ?", [$data['type']]);
            $data['code'] = $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        }

        // Create partner
        $id = $this->db->insert('partners', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->audit('partner.created', 'partners', $id, null, $data);
        $this->session->setFlash('success', 'Partner created successfully');
        $this->redirect("/warehouse/partners/{$id}");
    }

    /**
     * Edit partner form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('warehouse.partners.edit');

        $partner = $this->db->fetch(
            "SELECT * FROM partners WHERE id = ?",
            [$id]
        );

        if (!$partner) {
            $this->notFound();
        }

        $this->render('warehouse/partners/form', [
            'title' => "Edit: {$partner['name']}",
            'partner' => $partner
        ]);
    }

    /**
     * Update partner
     */
    public function update(string $id): void
    {
        $this->requirePermission('warehouse.partners.edit');
        $this->validateCSRF();

        $partner = $this->db->fetch("SELECT * FROM partners WHERE id = ?", [$id]);
        if (!$partner) {
            $this->notFound();
        }

        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'supplier',
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'tax_id' => trim($_POST['tax_id'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validation
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (!in_array($data['type'], ['supplier', 'customer', 'both'])) {
            $errors['type'] = 'Invalid partner type';
        }

        if ($data['code']) {
            $exists = $this->db->fetch(
                "SELECT id FROM partners WHERE code = ? AND id != ?",
                [$data['code'], $id]
            );
            if ($exists) {
                $errors['code'] = 'Code already exists';
            }
        }

        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/warehouse/partners/{$id}/edit");
            return;
        }

        // Update
        $this->db->update('partners', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]), ['id' => $id]);

        $this->audit('partner.updated', 'partners', $id, $partner, $data);
        $this->session->setFlash('success', 'Partner updated successfully');
        $this->redirect("/warehouse/partners/{$id}");
    }

    /**
     * Delete partner
     */
    public function delete(string $id): void
    {
        $this->requirePermission('warehouse.partners.delete');
        $this->validateCSRF();

        $partner = $this->db->fetch("SELECT * FROM partners WHERE id = ?", [$id]);
        if (!$partner) {
            $this->notFound();
        }

        // Check if partner has documents
        $docCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM documents WHERE partner_id = ?",
            [$id]
        );

        if ($docCount > 0) {
            $this->session->setFlash('error', "Cannot delete partner: {$docCount} documents are linked to this partner");
            $this->redirect("/warehouse/partners/{$id}");
            return;
        }

        $this->db->delete('partners', ['id' => $id]);
        $this->audit('partner.deleted', 'partners', $id, $partner, null);
        $this->session->setFlash('success', 'Partner deleted successfully');
        $this->redirect('/warehouse/partners');
    }
}
