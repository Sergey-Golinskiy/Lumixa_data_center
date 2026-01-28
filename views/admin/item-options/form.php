<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/item-options/<?= $this->e($group) ?>" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<?php
    $action = $option
        ? "/admin/item-options/{$group}/{$option['id']}"
        : "/admin/item-options/{$group}";
?>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $option ? $this->__('edit_item_option') : $this->__('create_item_option') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $this->e($action) ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= $this->e($option['name'] ?? $this->old('name')) ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <?php if ($showFilament): ?>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_filament" value="1"
                           <?= ($option['is_filament'] ?? $this->old('is_filament')) ? 'checked' : '' ?>>
                    <?= $this->__('filament') ?>
                </label>
            </div>
            <?php endif; ?>

            <?php if (!empty($showColor)): ?>
            <div class="form-group">
                <label><?= $this->__('alias_color') ?></label>
                <input type="hidden" name="color" id="colorValue"
                       value="<?= $this->e($option['color'] ?? $this->old('color', '')) ?>">

                <div class="color-picker-wrapper">
                    <div class="color-mode-tabs">
                        <button type="button" class="color-tab active" data-mode="rgb">RGB</button>
                        <button type="button" class="color-tab" data-mode="cmyk">CMYK</button>
                    </div>

                    <div class="color-mode-content" id="colorModeRgb">
                        <div class="color-rgb-row">
                            <input type="color" id="colorPicker"
                                   value="<?= $this->e($option['color'] ?? '#3498db') ?>"
                                   class="color-input-native">
                            <input type="text" id="colorHex" placeholder="#RRGGBB" maxlength="7"
                                   value="<?= $this->e($option['color'] ?? '') ?>"
                                   class="color-input-hex">
                            <div class="color-rgb-inputs">
                                <label>R <input type="number" id="colorR" min="0" max="255" class="color-num-input"></label>
                                <label>G <input type="number" id="colorG" min="0" max="255" class="color-num-input"></label>
                                <label>B <input type="number" id="colorB" min="0" max="255" class="color-num-input"></label>
                            </div>
                        </div>
                    </div>

                    <div class="color-mode-content" id="colorModeCmyk" style="display:none;">
                        <div class="color-cmyk-inputs">
                            <label>C <input type="number" id="colorC" min="0" max="100" value="0" class="color-num-input"> %</label>
                            <label>M <input type="number" id="colorM" min="0" max="100" value="0" class="color-num-input"> %</label>
                            <label>Y <input type="number" id="colorY" min="0" max="100" value="0" class="color-num-input"> %</label>
                            <label>K <input type="number" id="colorK" min="0" max="100" value="0" class="color-num-input"> %</label>
                        </div>
                    </div>

                    <div class="color-preview-row">
                        <span><?= $this->__('preview') ?>:</span>
                        <span class="color-preview-badge" id="colorPreviewBadge">
                            <?= $this->e($option['name'] ?? 'Alias') ?>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline" id="colorClearBtn"><?= $this->__('clear') ?></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($option['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $option ? $this->__('save_changes') : $this->__('create_item_option') ?>
                </button>
                <a href="/admin/item-options/<?= $this->e($group) ?>" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }

.color-picker-wrapper {
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 8px;
    padding: 15px;
    background: var(--bg-secondary, #f8f9fa);
}

.color-mode-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 15px;
    border-bottom: 2px solid var(--border, #e2e8f0);
}

.color-tab {
    padding: 8px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-muted, #6b7280);
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: color 0.2s, border-color 0.2s;
}

.color-tab:hover {
    color: var(--text, #1f2937);
}

.color-tab.active {
    color: var(--primary, #007bff);
    border-bottom-color: var(--primary, #007bff);
}

.color-rgb-row {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.color-input-native {
    width: 50px;
    height: 40px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 6px;
    cursor: pointer;
    padding: 2px;
}

.color-input-hex {
    width: 100px;
    font-family: monospace;
    font-size: 0.95rem;
    text-transform: uppercase;
}

.color-rgb-inputs, .color-cmyk-inputs {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-rgb-inputs label, .color-cmyk-inputs label {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
}

.color-num-input {
    width: 60px;
    text-align: center;
    font-size: 0.9rem;
}

.color-preview-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid var(--border, #e2e8f0);
    font-size: 0.9rem;
    color: var(--text-muted, #6b7280);
}

.color-preview-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    background: #3498db;
}
</style>

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
    const tabs = document.querySelectorAll('.color-tab');
    const rgbPanel = document.getElementById('colorModeRgb');
    const cmykPanel = document.getElementById('colorModeCmyk');

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

    // Tab switching
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            rgbPanel.style.display = this.dataset.mode === 'rgb' ? '' : 'none';
            cmykPanel.style.display = this.dataset.mode === 'cmyk' ? '' : 'none';
        });
    });

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
