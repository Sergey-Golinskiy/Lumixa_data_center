<?php
/**
 * PrintersController - Manage printer park
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class PrintersController extends Controller
{
    /**
     * List printers
     */
    public function index(): void
    {
        $this->requirePermission('admin.printers.view');
        if (!$this->ensurePrintersTable()) {
            return;
        }

        $columns = $this->getPrinterColumns();
        $printers = $this->db()->fetchAll(
            "SELECT " . implode(', ', $columns) . " FROM printers ORDER BY name"
        );

        $this->view('admin/printers/index', [
            'title' => $this->app->getTranslator()->get('printers'),
            'printers' => $printers
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requirePermission('admin.printers.create');
        if (!$this->ensurePrintersTable()) {
            return;
        }

        $hasCodeColumn = $this->db()->columnExists('printers', 'code');
        $this->view('admin/printers/form', [
            'title' => $this->app->getTranslator()->get('create_printer'),
            'printer' => null,
            'hasCodeColumn' => $hasCodeColumn
        ]);
    }

    /**
     * Store printer
     */
    public function store(): void
    {
        $this->requirePermission('admin.printers.create');
        if (!$this->ensurePrintersTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/printers/create');
            return;
        }

        $data = $this->getPayload();
        $errors = $this->validatePayload($data);

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/admin/printers/create');
            return;
        }

        $id = $this->db()->insert('printers', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->audit('printer.created', 'printers', $id, null, $data);
        $this->session->setFlash('success', $translator->get('printer_created_success'));
        $this->redirect('/admin/printers');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('admin.printers.edit');
        if (!$this->ensurePrintersTable()) {
            return;
        }

        $columns = $this->getPrinterColumns();
        $printer = $this->db()->fetch(
            "SELECT " . implode(', ', $columns) . " FROM printers WHERE id = ?",
            [$id]
        );

        if (!$printer) {
            $this->notFound();
        }

        $hasCodeColumn = $this->db()->columnExists('printers', 'code');
        $this->view('admin/printers/form', [
            'title' => $this->app->getTranslator()->get('edit_printer_title', ['name' => $printer['name']]),
            'printer' => $printer,
            'hasCodeColumn' => $hasCodeColumn
        ]);
    }

    /**
     * Update printer
     */
    public function update(string $id): void
    {
        $this->requirePermission('admin.printers.edit');
        if (!$this->ensurePrintersTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/printers/{$id}/edit");
            return;
        }

        $columns = $this->getPrinterColumns();
        $printer = $this->db()->fetch(
            "SELECT " . implode(', ', $columns) . " FROM printers WHERE id = ?",
            [$id]
        );

        if (!$printer) {
            $this->notFound();
        }

        $data = $this->getPayload($printer);
        $errors = $this->validatePayload($data, (int)$id);

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/admin/printers/{$id}/edit");
            return;
        }

        $this->db()->update('printers', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]), ['id' => $id]);

        $this->audit('printer.updated', 'printers', $id, $printer, $data);
        $this->session->setFlash('success', $translator->get('printer_updated_success'));
        $this->redirect('/admin/printers');
    }

    /**
     * Delete printer
     */
    public function delete(string $id): void
    {
        $this->requirePermission('admin.printers.delete');
        if (!$this->ensurePrintersTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/printers');
            return;
        }

        $columns = $this->getPrinterColumns();
        $printer = $this->db()->fetch(
            "SELECT " . implode(', ', $columns) . " FROM printers WHERE id = ?",
            [$id]
        );

        if (!$printer) {
            $this->notFound();
        }

        $inUse = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM details WHERE printer_id = ?",
            [$id]
        );

        if ((int)$inUse > 0) {
            $this->session->setFlash('error', $translator->get('printer_in_use'));
            $this->redirect('/admin/printers');
            return;
        }

        $this->db()->delete('printers', ['id' => $id]);
        $this->audit('printer.deleted', 'printers', $id, $printer, null);
        $this->session->setFlash('success', $translator->get('printer_deleted_success'));
        $this->redirect('/admin/printers');
    }

    private function getPayload(?array $existing = null): array
    {
        $data = [
            'name' => trim($this->post('name', ''))
        ];

        $optionalColumns = [
            'model' => fn () => trim($this->post('model', '')),
            'power_watts' => fn () => (float)$this->post('power_watts', 0),
            'electricity_cost_per_kwh' => fn () => (float)$this->post('electricity_cost_per_kwh', 0),
            'amortization_per_hour' => fn () => (float)$this->post('amortization_per_hour', 0),
            'maintenance_per_hour' => fn () => (float)$this->post('maintenance_per_hour', 0),
            'notes' => fn () => trim($this->post('notes', '')),
            'is_active' => fn () => $this->post('is_active') ? 1 : 0
        ];

        foreach ($optionalColumns as $column => $valueFactory) {
            if ($this->db()->columnExists('printers', $column)) {
                $data[$column] = $valueFactory();
            }
        }

        if ($this->db()->columnExists('printers', 'code')) {
            $postedCode = trim($this->post('code', ''));
            if ($postedCode !== '') {
                $data['code'] = $postedCode;
            } elseif (!empty($existing['code'])) {
                $data['code'] = $existing['code'];
            } else {
                $data['code'] = $this->generatePrinterCode($data['name']);
            }
        }

        return $data;
    }

    private function validatePayload(array $data, ?int $id = null): array
    {
        $errors = [];
        $translator = $this->app->getTranslator();

        if ($data['name'] === '') {
            $errors['name'] = $translator->get('printer_name_required');
        } else {
            $params = [$data['name']];
            $sql = "SELECT id FROM printers WHERE name = ?";
            if ($id) {
                $sql .= " AND id != ?";
                $params[] = $id;
            }
            $exists = $this->db()->fetch($sql, $params);
            if ($exists) {
                $errors['name'] = $translator->get('printer_name_exists');
            }
        }

        if ($this->db()->columnExists('printers', 'code')) {
            $code = trim($data['code'] ?? '');
            if ($code === '') {
                $errors['code'] = $translator->get('code_required');
            } else {
                $params = [$code];
                $sql = "SELECT id FROM printers WHERE code = ?";
                if ($id) {
                    $sql .= " AND id != ?";
                    $params[] = $id;
                }
                $exists = $this->db()->fetch($sql, $params);
                if ($exists) {
                    $errors['code'] = $translator->get('code_exists');
                }
            }
        }

        return $errors;
    }

    private function ensurePrintersTable(): bool
    {
        if ($this->db()->tableExists('printers')) {
            return true;
        }

        $translator = $this->app->getTranslator();
        $this->session->setFlash('error', $translator->get('printers_missing'));
        $this->redirect('/admin/diagnostics');
        return false;
    }

    private function getPrinterColumns(): array
    {
        $columns = [
            'id',
            'name'
        ];

        $optionalColumns = [
            'code',
            'model',
            'power_watts',
            'electricity_cost_per_kwh',
            'amortization_per_hour',
            'maintenance_per_hour',
            'notes',
            'is_active',
            'status',
            'type',
            'hourly_rate',
            'current_job_id',
            'created_at',
            'updated_at'
        ];

        foreach ($optionalColumns as $column) {
            if ($this->db()->columnExists('printers', $column)) {
                $columns[] = $column;
            } else {
                $columns[] = "NULL AS {$column}";
            }
        }

        return $columns;
    }

    private function generatePrinterCode(string $name): string
    {
        $base = strtoupper(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name)));
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'PRN';
        }

        $candidate = $base;
        $suffix = 1;
        while ($this->db()->fetchColumn("SELECT COUNT(*) FROM printers WHERE code = ?", [$candidate])) {
            $suffix++;
            $candidate = "{$base}-{$suffix}";
        }

        return $candidate;
    }
}
