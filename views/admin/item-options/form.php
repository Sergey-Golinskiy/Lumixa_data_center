<?php $this->section('content'); ?>

<?php
    $action = $option
        ? "/admin/item-options/{$group}/{$option['id']}"
        : "/admin/item-options/{$group}";
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $option ? $this->__('edit_item_option') : $this->__('create_item_option') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $this->e($action) ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="150"
                               value="<?= $this->e($option['name'] ?? $this->old('name')) ?>">
                        <?php if ($this->hasError('name')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($showFilament): ?>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_filament" value="1" id="is_filament" class="form-check-input"
                                   <?= ($option['is_filament'] ?? $this->old('is_filament')) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_filament"><?= $this->__('filament') ?></label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($showColor)): ?>
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('alias_color') ?></label>
                        <input type="hidden" name="color" id="colorValue"
                               value="<?= $this->e($option['color'] ?? $this->old('color', '')) ?>">

                        <div class="card border">
                            <div class="card-body">
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#colorModeRgb" role="tab">RGB</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#colorModeCmyk" role="tab">CMYK</a>
                                    </li>
                                </ul>
                                <div class="tab-content pt-3">
                                    <div class="tab-pane active" id="colorModeRgb" role="tabpanel">
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            <input type="color" id="colorPicker"
                                                   value="<?= $this->e($option['color'] ?? '#3498db') ?>"
                                                   class="form-control form-control-color">
                                            <input type="text" id="colorHex" placeholder="#RRGGBB" maxlength="7"
                                                   value="<?= $this->e($option['color'] ?? '') ?>"
                                                   class="form-control" style="width: 100px; font-family: monospace;">
                                            <div class="d-flex gap-2 align-items-center">
                                                <div class="d-flex align-items-center gap-1">
                                                    <label class="form-label mb-0 small">R</label>
                                                    <input type="number" id="colorR" min="0" max="255" class="form-control form-control-sm" style="width: 60px;">
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <label class="form-label mb-0 small">G</label>
                                                    <input type="number" id="colorG" min="0" max="255" class="form-control form-control-sm" style="width: 60px;">
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <label class="form-label mb-0 small">B</label>
                                                    <input type="number" id="colorB" min="0" max="255" class="form-control form-control-sm" style="width: 60px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="colorModeCmyk" role="tabpanel">
                                        <div class="d-flex gap-2 align-items-center">
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="form-label mb-0 small">C</label>
                                                <input type="number" id="colorC" min="0" max="100" value="0" class="form-control form-control-sm" style="width: 60px;">
                                                <span class="small text-muted">%</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="form-label mb-0 small">M</label>
                                                <input type="number" id="colorM" min="0" max="100" value="0" class="form-control form-control-sm" style="width: 60px;">
                                                <span class="small text-muted">%</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="form-label mb-0 small">Y</label>
                                                <input type="number" id="colorY" min="0" max="100" value="0" class="form-control form-control-sm" style="width: 60px;">
                                                <span class="small text-muted">%</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="form-label mb-0 small">K</label>
                                                <input type="number" id="colorK" min="0" max="100" value="0" class="form-control form-control-sm" style="width: 60px;">
                                                <span class="small text-muted">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top">
                                    <span class="text-muted"><?= $this->__('preview') ?>:</span>
                                    <span class="badge rounded-pill px-3 py-2" id="colorPreviewBadge" style="font-size: 14px;">
                                        <?= $this->e($option['name'] ?? 'Alias') ?>
                                    </span>
                                    <button type="button" class="btn btn-soft-secondary btn-sm" id="colorClearBtn">
                                        <i class="ri-close-line me-1"></i>
                                        <?= $this->__('clear') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                                   <?= ($option['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $option ? $this->__('save_changes') : $this->__('create_item_option') ?>
                        </button>
                        <a href="/admin/item-options/<?= $this->e($group) ?>" class="btn btn-soft-secondary">
                            <i class="ri-arrow-left-line me-1"></i>
                            <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <a href="/admin/item-options/<?= $this->e($group) ?>" class="btn btn-soft-primary w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    <?= $this->__('back_to_list') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($showColor)): ?>
<script>
(function() {
    const hiddenInput = document.getElementById('colorValue');
    const picker = document.getElementById('colorPicker');
    const hexInput = document.getElementById('colorHex');
    const rInput = document.getElementById('colorR');
    const gInput = document.getElementById('colorG');
    const bInput = document.getElementById('colorB');
    const cInput = document.getElementById('colorC');
    const mInput = document.getElementById('colorM');
    const yInput = document.getElementById('colorY');
    const kInput = document.getElementById('colorK');
    const preview = document.getElementById('colorPreviewBadge');
    const nameInput = document.getElementById('name');
    const clearBtn = document.getElementById('colorClearBtn');

    function hexToRgb(hex) {
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
        const n = parseInt(hex, 16);
        return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
    }

    function rgbToHex(r, g, b) {
        return '#' + [r, g, b].map(x => {
            const h = Math.max(0, Math.min(255, Math.round(x))).toString(16);
            return h.length === 1 ? '0' + h : h;
        }).join('');
    }

    function rgbToCmyk(r, g, b) {
        if (r === 0 && g === 0 && b === 0) return { c: 0, m: 0, y: 0, k: 100 };
        const c1 = 1 - r / 255, m1 = 1 - g / 255, y1 = 1 - b / 255;
        const k = Math.min(c1, m1, y1);
        return {
            c: Math.round(((c1 - k) / (1 - k)) * 100),
            m: Math.round(((m1 - k) / (1 - k)) * 100),
            y: Math.round(((y1 - k) / (1 - k)) * 100),
            k: Math.round(k * 100)
        };
    }

    function cmykToRgb(c, m, y, k) {
        c /= 100; m /= 100; y /= 100; k /= 100;
        return {
            r: Math.round(255 * (1 - c) * (1 - k)),
            g: Math.round(255 * (1 - m) * (1 - k)),
            b: Math.round(255 * (1 - y) * (1 - k))
        };
    }

    function contrastColor(hex) {
        const { r, g, b } = hexToRgb(hex);
        const luma = 0.299 * r + 0.587 * g + 0.114 * b;
        return luma > 160 ? '#000000' : '#ffffff';
    }

    function updateAll(hex, source) {
        if (!hex || hex.length < 4) return;
        const { r, g, b } = hexToRgb(hex);
        const cmyk = rgbToCmyk(r, g, b);

        hiddenInput.value = hex;
        if (source !== 'picker') picker.value = hex;
        if (source !== 'hex') hexInput.value = hex;
        if (source !== 'rgb') { rInput.value = r; gInput.value = g; bInput.value = b; }
        if (source !== 'cmyk') { cInput.value = cmyk.c; mInput.value = cmyk.m; yInput.value = cmyk.y; kInput.value = cmyk.k; }

        preview.style.background = hex;
        preview.style.color = contrastColor(hex);
    }

    // Init from saved value
    const saved = hiddenInput.value;
    if (saved && saved.match(/^#[0-9a-fA-F]{6}$/)) {
        updateAll(saved, 'init');
    }

    picker.addEventListener('input', () => updateAll(picker.value, 'picker'));

    hexInput.addEventListener('input', () => {
        let v = hexInput.value;
        if (v && !v.startsWith('#')) v = '#' + v;
        if (v.match(/^#[0-9a-fA-F]{6}$/)) updateAll(v, 'hex');
    });

    [rInput, gInput, bInput].forEach(el => {
        el.addEventListener('input', () => {
            const hex = rgbToHex(+rInput.value, +gInput.value, +bInput.value);
            updateAll(hex, 'rgb');
        });
    });

    [cInput, mInput, yInput, kInput].forEach(el => {
        el.addEventListener('input', () => {
            const { r, g, b } = cmykToRgb(+cInput.value, +mInput.value, +yInput.value, +kInput.value);
            const hex = rgbToHex(r, g, b);
            updateAll(hex, 'cmyk');
        });
    });

    clearBtn.addEventListener('click', () => {
        hiddenInput.value = '';
        hexInput.value = '';
        picker.value = '#3498db';
        rInput.value = ''; gInput.value = ''; bInput.value = '';
        cInput.value = 0; mInput.value = 0; yInput.value = 0; kInput.value = 0;
        preview.style.background = '#3498db';
        preview.style.color = '#fff';
    });

    nameInput.addEventListener('input', () => {
        preview.textContent = nameInput.value || 'Alias';
    });
})();
</script>
<?php endif; ?>

<?php $this->endSection(); ?>
