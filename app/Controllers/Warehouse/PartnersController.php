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
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM partners WHERE {$whereClause}",
            $params
        );

        // Get partners
        $offset = ($page - 1) * $perPage;
        $partners = $this->db()->fetchAll(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM documents WHERE partner_id = p.id) as document_count
             FROM partners p
             WHERE {$whereClause}
             ORDER BY p.name
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('warehouse/partners/index', [
            'title' => 'Suppliers',
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

        $partner = $this->db()->fetch(
            "SELECT * FROM partners WHERE id = ?",
            [$id]
        );

        if (!$partner) {
            $this->notFound();
        }

        // Get recent documents
        $documents = $this->db()->fetchAll(
            "SELECT d.*, u.name as created_by_name
             FROM documents d
             LEFT JOIN users u ON d.created_by = u.id
             WHERE d.partner_id = ?
             ORDER BY d.created_at DESC
             LIMIT 20",
            [$id]
        );

        // Get statistics
        $stats = $this->db()->fetch(
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

        // Get categories
        $categories = $this->db()->fetchAll(
            "SELECT * FROM partner_categories WHERE is_active = 1 ORDER BY name"
        );

        $this->render('warehouse/partners/form', [
            'title' => 'Create Supplier',
            'partner' => null,
            'categories' => $categories,
            'contacts' => []
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
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
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
            $errors['type'] = 'Invalid supplier type';
        }

        if ($data['code']) {
            $exists = $this->db()->fetch(
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
            $count = $this->db()->fetchColumn("SELECT COUNT(*) FROM partners WHERE type = ?", [$data['type']]);
            $data['code'] = $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        }

        // Create partner
        $id = $this->db()->insert('partners', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s')
        ]));

        // Save contacts
        if (!empty($_POST['contacts']) && is_array($_POST['contacts'])) {
            $this->savePartnerContacts($id, $_POST['contacts']);
        }

        $this->audit('partner.created', 'partners', $id, null, $data);
        $this->session->setFlash('success', 'Supplier created successfully');
        $this->redirect("/warehouse/partners/{$id}");
    }

    /**
     * Edit partner form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('warehouse.partners.edit');

        $partner = $this->db()->fetch(
            "SELECT * FROM partners WHERE id = ?",
            [$id]
        );

        if (!$partner) {
            $this->notFound();
        }

        // Get categories
        $categories = $this->db()->fetchAll(
            "SELECT * FROM partner_categories WHERE is_active = 1 ORDER BY name"
        );

        // Get contacts
        $contacts = $this->db()->fetchAll(
            "SELECT * FROM partner_contacts WHERE partner_id = ? ORDER BY is_primary DESC, id",
            [$id]
        );

        $this->render('warehouse/partners/form', [
            'title' => "Edit: {$partner['name']}",
            'partner' => $partner,
            'categories' => $categories,
            'contacts' => $contacts
        ]);
    }

    /**
     * Update partner
     */
    public function update(string $id): void
    {
        $this->requirePermission('warehouse.partners.edit');
        $this->validateCSRF();

        $partner = $this->db()->fetch("SELECT * FROM partners WHERE id = ?", [$id]);
        if (!$partner) {
            $this->notFound();
        }

        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'supplier',
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
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
            $errors['type'] = 'Invalid supplier type';
        }

        if ($data['code']) {
            $exists = $this->db()->fetch(
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
        $this->db()->update('partners', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]), ['id' => $id]);

        // Save contacts
        if (isset($_POST['contacts']) && is_array($_POST['contacts'])) {
            $this->savePartnerContacts($id, $_POST['contacts']);
        }

        $this->audit('partner.updated', 'partners', $id, $partner, $data);
        $this->session->setFlash('success', 'Supplier updated successfully');
        $this->redirect("/warehouse/partners/{$id}");
    }

    /**
     * Delete partner
     */
    public function delete(string $id): void
    {
        $this->requirePermission('warehouse.partners.delete');
        $this->validateCSRF();

        $partner = $this->db()->fetch("SELECT * FROM partners WHERE id = ?", [$id]);
        if (!$partner) {
            $this->notFound();
        }

        // Check if partner has documents
        $docCount = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM documents WHERE partner_id = ?",
            [$id]
        );

        if ($docCount > 0) {
            $this->session->setFlash('error', "Cannot delete supplier: {$docCount} documents are linked to this supplier");
            $this->redirect("/warehouse/partners/{$id}");
            return;
        }

        $this->db()->delete('partners', ['id' => $id]);
        $this->audit('partner.deleted', 'partners', $id, $partner, null);
        $this->session->setFlash('success', 'Supplier deleted successfully');
        $this->redirect('/warehouse/partners');
    }

    /**
     * Save partner contacts
     */
    private function savePartnerContacts(int $partnerId, array $contacts): void
    {
        // Get existing contact IDs
        $existingIds = [];
        foreach ($contacts as $contact) {
            if (!empty($contact['id'])) {
                $existingIds[] = (int)$contact['id'];
            }
        }

        // Delete contacts that are not in the submitted list
        if (!empty($existingIds)) {
            $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
            $this->db()->query(
                "DELETE FROM partner_contacts WHERE partner_id = ? AND id NOT IN ($placeholders)",
                array_merge([$partnerId], $existingIds)
            );
        } else {
            // Delete all contacts if none submitted
            $this->db()->delete('partner_contacts', ['partner_id' => $partnerId]);
        }

        // Insert or update contacts
        foreach ($contacts as $contact) {
            $contactData = [
                'partner_id' => $partnerId,
                'name' => trim($contact['name'] ?? ''),
                'position' => trim($contact['position'] ?? ''),
                'phone' => trim($contact['phone'] ?? ''),
                'email' => trim($contact['email'] ?? ''),
                'website' => trim($contact['website'] ?? ''),
                'social_media' => trim($contact['social_media'] ?? ''),
                'is_primary' => isset($contact['is_primary']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Skip empty contacts
            if (empty($contactData['name']) && empty($contactData['phone']) && empty($contactData['email'])) {
                continue;
            }

            if (!empty($contact['id'])) {
                // Update existing
                $this->db()->update('partner_contacts', $contactData, ['id' => (int)$contact['id']]);
            } else {
                // Insert new
                $contactData['created_at'] = date('Y-m-d H:i:s');
                $this->db()->insert('partner_contacts', $contactData);
            }
        }
    }
}
