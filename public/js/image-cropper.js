// ============================================================
// public/js/image-cropper.js
// Modern Image Cropper — Trip Photos, Company Logo, Avatar
// Uses Cropper.js (loaded from CDN in blade)
// ============================================================

window.ImageCropperSystem = (function ($) {
    'use strict';

    var cropperInstance = null;
    var currentConfig   = {};
    var onCropDone      = null;

    // ── Inject modal HTML sekali sahaja ──────────────────────────────────────
    function injectModal() {
        if ($('#smartCropperModal').length) return;

        $('head').append('<style id="cropperModalStyle">' + getStyles() + '</style>');

        var html = [
            '<div id="smartCropperModal" class="sc-overlay">',
            '  <div class="sc-dialog">',

            '    <!-- HEADER -->',
            '    <div class="sc-header">',
            '      <div class="sc-header-left">',
            '        <div class="sc-icon-wrap" id="scIconWrap"><span id="scHeaderIcon">✂️</span></div>',
            '        <div>',
            '          <div class="sc-title" id="scTitle">Tetapkan Gambar</div>',
            '          <div class="sc-subtitle" id="scSubtitle">Laraskan kedudukan dan saiz</div>',
            '        </div>',
            '      </div>',
            '      <button class="sc-close" id="scClose">✕</button>',
            '    </div>',

            '    <!-- CANVAS AREA -->',
            '    <div class="sc-canvas-wrap">',
            '      <div class="sc-canvas-inner">',
            '        <img id="scCropperImg" src="" alt="crop">',
            '      </div>',
            '      <!-- Overlay hint -->',
            '      <div class="sc-hint" id="scHint">',
            '        <span>🖱️</span> Drag untuk alih &nbsp;·&nbsp; <span>🤏</span> Pinch/scroll untuk zum',
            '      </div>',
            '    </div>',

            '    <!-- CONTROLS -->',
            '    <div class="sc-controls">',

            '      <!-- Zoom slider -->',
            '      <div class="sc-ctrl-group">',
            '        <span class="sc-ctrl-label">Zum</span>',
            '        <div class="sc-zoom-row">',
            '          <button class="sc-ctrl-btn" id="scZoomOut" title="Zoom Out">－</button>',
            '          <input type="range" id="scZoomSlider" class="sc-slider" min="0" max="3" step="0.01" value="0">',
            '          <button class="sc-ctrl-btn" id="scZoomIn" title="Zoom In">＋</button>',
            '        </div>',
            '      </div>',

            '      <!-- Rotate + Flip -->',
            '      <div class="sc-ctrl-group">',
            '        <span class="sc-ctrl-label">Putar & Balik</span>',
            '        <div class="sc-btn-row">',
            '          <button class="sc-ctrl-btn" id="scRotL" title="Putar Kiri">↺</button>',
            '          <button class="sc-ctrl-btn" id="scRotR" title="Putar Kanan">↻</button>',
            '          <button class="sc-ctrl-btn" id="scFlipH" title="Balik Mendatar">⇄</button>',
            '          <button class="sc-ctrl-btn" id="scFlipV" title="Balik Menegak">⇅</button>',
            '          <button class="sc-ctrl-btn sc-ctrl-btn--reset" id="scReset" title="Reset">⟳ Reset</button>',
            '        </div>',
            '      </div>',

            '      <!-- Fit options -->',
            '      <div class="sc-ctrl-group">',
            '        <span class="sc-ctrl-label">Mod Paparan</span>',
            '        <div class="sc-btn-row">',
            '          <button class="sc-fit-btn active" id="scFitFull" data-mode="full">',
            '            <span>⛶</span> Penuh',
            '          </button>',
            '          <button class="sc-fit-btn" id="scFitCrop" data-mode="crop">',
            '            <span>⧉</span> Potong',
            '          </button>',
            '        </div>',
            '      </div>',

            '    </div>',

            '    <!-- PREVIEW ROW -->',
            '    <div class="sc-preview-row" id="scPreviewRow">',
            '      <div class="sc-preview-label">Pratonton:</div>',
            '      <div class="sc-previews" id="scPreviews"></div>',
            '    </div>',

            '    <!-- FOOTER -->',
            '    <div class="sc-footer">',
            '      <div class="sc-file-info" id="scFileInfo"></div>',
            '      <div class="sc-footer-btns">',
            '        <button class="sc-btn-cancel" id="scCancel">Batal</button>',
            '        <button class="sc-btn-apply" id="scApply">',
            '          <span id="scApplySpinner" style="display:none" class="sc-spinner"></span>',
            '          <span id="scApplyText">✔ Guna Gambar</span>',
            '        </button>',
            '      </div>',
            '    </div>',

            '  </div>',
            '</div>',
        ].join('\n');

        $('body').append(html);
        bindEvents();
    }

    // ── Bind semua events ────────────────────────────────────────────────────
    function bindEvents() {
        $('#scClose, #scCancel').on('click', closeModal);
        $('#smartCropperModal').on('click', function (e) {
            if (e.target === this) closeModal();
        });

        $('#scZoomIn').on('click',  function () { if (cropperInstance) cropperInstance.zoom(0.1); });
        $('#scZoomOut').on('click', function () { if (cropperInstance) cropperInstance.zoom(-0.1); });

        $('#scZoomSlider').on('input', function () {
            if (cropperInstance) cropperInstance.zoomTo(parseFloat($(this).val()));
        });

        $('#scRotL').on('click',  function () { if (cropperInstance) cropperInstance.rotate(-90); });
        $('#scRotR').on('click',  function () { if (cropperInstance) cropperInstance.rotate(90); });

        var flipH = 1, flipV = 1;
        $('#scFlipH').on('click', function () {
            if (!cropperInstance) return;
            flipH *= -1;
            cropperInstance.scaleX(flipH);
        });
        $('#scFlipV').on('click', function () {
            if (!cropperInstance) return;
            flipV *= -1;
            cropperInstance.scaleY(flipV);
        });

        $('#scReset').on('click', function () {
            if (cropperInstance) {
                cropperInstance.reset();
                flipH = 1; flipV = 1;
                $('#scZoomSlider').val(0);
                setMode('full');
            }
        });

        // Fit mode buttons
        $(document).on('click', '.sc-fit-btn', function () {
            setMode($(this).data('mode'));
        });

        $('#scApply').on('click', applyImage);

        // Sync zoom slider bila cropper zoom berubah
        $('#scCropperImg').on('zoom.cropper', function (e) {
            var ratio = e.originalEvent.detail.ratio;
            $('#scZoomSlider').val(Math.min(3, Math.max(0, ratio)));
        });
    }

    // ── Set mode: full (contain) atau crop ──────────────────────────────────
    function setMode(mode) {
        if (!cropperInstance) return;
        $('.sc-fit-btn').removeClass('active');
        $('.sc-fit-btn[data-mode="' + mode + '"]').addClass('active');

        if (mode === 'full') {
            // Tunjuk SEMUA gambar — crop box = seluruh canvas
            var canvasData = cropperInstance.getCanvasData();
            cropperInstance.setCropBoxData({
                left:   canvasData.left,
                top:    canvasData.top,
                width:  canvasData.width,
                height: canvasData.height,
            });
            cropperInstance.setDragMode('move');
        } else {
            // Mode potong bebas
            cropperInstance.setDragMode('crop');
        }
    }

    // ── Buka modal ──────────────────────────────────────────────────────────
    function open(config) {
        /*
         * config = {
         *   file        : File object
         *   aspectRatio : number | NaN (free)  — default NaN
         *   shape       : 'rect' | 'circle'    — default 'rect'
         *   title       : string
         *   subtitle    : string
         *   icon        : emoji
         *   previews    : [{ size: 'sm'|'md'|'lg', label: string, shape: 'circle'|'rect' }]
         *   maxWidth    : number (output width px)   — default 1200
         *   maxHeight   : number (output height px)  — default 900
         *   quality     : number 0-1                 — default 0.92
         *   onDone      : function(blob, dataUrl)
         * }
         */
        injectModal();
        currentConfig = $.extend({
            aspectRatio : NaN,
            shape       : 'rect',
            title       : 'Tetapkan Gambar',
            subtitle    : 'Laraskan kedudukan dan saiz',
            icon        : '🖼️',
            previews    : [],
            maxWidth    : 1200,
            maxHeight   : 900,
            quality     : 0.92,
        }, config);

        onCropDone = config.onDone || null;

        // Set header
        $('#scTitle').text(currentConfig.title);
        $('#scSubtitle').text(currentConfig.subtitle);
        $('#scHeaderIcon').text(currentConfig.icon);

        // Build previews
        buildPreviews(currentConfig.previews, currentConfig.shape);

        // Show file info
        if (config.file) {
            var sizeMb = (config.file.size / 1024 / 1024).toFixed(2);
            $('#scFileInfo').text('📄 ' + config.file.name + ' · ' + sizeMb + ' MB');
        }

        // Load image
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#scCropperImg').attr('src', e.target.result);
            showModal();
            setTimeout(function () { initCropper(); }, 50);
        };
        reader.readAsDataURL(config.file);
    }

    // ── Init Cropper.js ──────────────────────────────────────────────────────
    function initCropper() {
        destroyCropper();

        var img = document.getElementById('scCropperImg');

        cropperInstance = new Cropper(img, {
            aspectRatio    : currentConfig.aspectRatio,
            viewMode       : 1,          // Crop box tidak boleh keluar dari canvas
            dragMode       : 'move',     // Default: gerak gambar
            autoCropArea   : 1.0,        // Crop box = 100% canvas (tunjuk semua)
            restore        : false,
            guides         : true,
            center         : true,
            highlight      : true,
            cropBoxMovable : true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview        : '.sc-preview-thumb',
            ready: function () {
                // Default: tunjuk semua gambar
                setMode('full');
                $('#scHint').fadeOut(2000);
            },
        });
    }

    // ── Build preview thumbnails ─────────────────────────────────────────────
    function buildPreviews(previews, defaultShape) {
        var container = $('#scPreviews').empty();
        if (!previews || !previews.length) {
            $('#scPreviewRow').hide();
            return;
        }
        $('#scPreviewRow').show();

        var sizes = { sm: 48, md: 72, lg: 96 };
        previews.forEach(function (p) {
            var px    = sizes[p.size] || 72;
            var shape = p.shape || defaultShape || 'rect';
            var isCircle = shape === 'circle';

            var thumb = $('<div class="sc-preview-item">');
            var wrap  = $('<div class="sc-preview-thumb" style="' +
                'width:' + px + 'px;height:' + px + 'px;' +
                'border-radius:' + (isCircle ? '50%' : '8px') + ';' +
                'overflow:hidden;border:2px solid #e2e8f0;">').appendTo(thumb);
            var label = $('<div class="sc-preview-item-label">').text(p.label || '');
            thumb.append(label);
            container.append(thumb);
        });
    }

    // ── Apply — output sebagai Blob ──────────────────────────────────────────
    function applyImage() {
        if (!cropperInstance) return;

        var $btn = $('#scApply');
        $btn.prop('disabled', true);
        $('#scApplySpinner').show();
        $('#scApplyText').text('Memproses...');

        setTimeout(function () {
            try {
                var canvas = cropperInstance.getCroppedCanvas({
                    maxWidth    : currentConfig.maxWidth,
                    maxHeight   : currentConfig.maxHeight,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                    fillColor   : '#fff',
                });

                // Circle mask kalau perlu
                if (currentConfig.shape === 'circle') {
                    canvas = circleClip(canvas);
                }

                var mimeType = 'image/jpeg';
                if (currentConfig.shape === 'circle') mimeType = 'image/png';

                var dataUrl = canvas.toDataURL(mimeType, currentConfig.quality);

                canvas.toBlob(function (blob) {
                    closeModal();
                    if (typeof onCropDone === 'function') {
                        onCropDone(blob, dataUrl);
                    }
                }, mimeType, currentConfig.quality);

            } catch (err) {
                console.error('Cropper error:', err);
                closeModal();
            }
        }, 100);
    }

    // ── Circle clip ──────────────────────────────────────────────────────────
    function circleClip(sourceCanvas) {
        var size   = Math.min(sourceCanvas.width, sourceCanvas.height);
        var canvas = document.createElement('canvas');
        canvas.width  = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d');
        ctx.beginPath();
        ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
        ctx.clip();
        var offsetX = (sourceCanvas.width  - size) / 2;
        var offsetY = (sourceCanvas.height - size) / 2;
        ctx.drawImage(sourceCanvas, -offsetX, -offsetY);
        return canvas;
    }

    // ── Show / hide / destroy ────────────────────────────────────────────────
    function showModal() {
        var $modal = $('#smartCropperModal');
        $modal.css('display', 'flex');
        setTimeout(function () { $modal.addClass('sc-visible'); }, 10);
        $('body').css('overflow', 'hidden');
    }

    function closeModal() {
        var $modal = $('#smartCropperModal');
        $modal.removeClass('sc-visible');
        setTimeout(function () {
            $modal.css('display', 'none');
            destroyCropper();
            $('body').css('overflow', '');
            $('#scApply').prop('disabled', false);
            $('#scApplySpinner').hide();
            $('#scApplyText').text('✔ Guna Gambar');
        }, 300);
    }

    function destroyCropper() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
    }

    // ── CSS ──────────────────────────────────────────────────────────────────
    function getStyles() {
        return [
        /* overlay */
        '#smartCropperModal.sc-overlay{',
            'display:none;position:fixed;inset:0;',
            'background:rgba(0,0,0,.72);backdrop-filter:blur(8px);',
            'z-index:99999;align-items:center;justify-content:center;',
            'padding:16px;opacity:0;transition:opacity .3s ease;',
        '}',
        '#smartCropperModal.sc-overlay.sc-visible{opacity:1;}',

        /* dialog */
        '.sc-dialog{',
            'background:#fff;border-radius:20px;width:100%;max-width:760px;',
            'max-height:94vh;display:flex;flex-direction:column;overflow:hidden;',
            'box-shadow:0 30px 80px rgba(0,0,0,.4);',
            'transform:translateY(24px) scale(.97);transition:transform .3s cubic-bezier(.16,1,.3,1);',
        '}',
        '.sc-visible .sc-dialog{transform:translateY(0) scale(1);}',

        /* header */
        '.sc-header{',
            'display:flex;align-items:center;justify-content:space-between;',
            'padding:18px 22px 14px;border-bottom:1px solid #f0f0f0;flex-shrink:0;',
        '}',
        '.sc-header-left{display:flex;align-items:center;gap:14px;}',
        '.sc-icon-wrap{',
            'width:44px;height:44px;border-radius:12px;',
            'background:linear-gradient(135deg,#667eea,#764ba2);',
            'display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;',
        '}',
        '.sc-title{font-size:1.05rem;font-weight:800;color:#1a1a2e;line-height:1.2;}',
        '.sc-subtitle{font-size:.78rem;color:#a0aec0;font-weight:500;margin-top:2px;}',
        '.sc-close{',
            'width:34px;height:34px;border-radius:50%;border:none;background:#f7f7f7;',
            'cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;',
            'color:#666;transition:all .2s;',
        '}',
        '.sc-close:hover{background:#fee;color:#e53e3e;transform:rotate(90deg);}',

        /* canvas */
        '.sc-canvas-wrap{',
            'flex:1;min-height:0;position:relative;background:#1a1a2e;overflow:hidden;',
        '}',
        '.sc-canvas-inner{height:100%;min-height:300px;max-height:420px;}',
        '.sc-canvas-inner img{display:block;max-width:100%;}',
        '.sc-hint{',
            'position:absolute;bottom:12px;left:50%;transform:translateX(-50%);',
            'background:rgba(0,0,0,.6);color:white;padding:5px 14px;',
            'border-radius:20px;font-size:.75rem;pointer-events:none;',
            'backdrop-filter:blur(4px);white-space:nowrap;',
        '}',

        /* controls */
        '.sc-controls{',
            'display:flex;gap:0;flex-wrap:wrap;padding:12px 18px;',
            'border-top:1px solid #f0f0f0;border-bottom:1px solid #f0f0f0;',
            'background:#fafafa;flex-shrink:0;',
        '}',
        '.sc-ctrl-group{',
            'display:flex;flex-direction:column;gap:6px;padding:6px 12px;',
            'border-right:1px solid #ebebeb;',
        '}',
        '.sc-ctrl-group:last-child{border-right:none;}',
        '.sc-ctrl-label{font-size:.68rem;font-weight:700;color:#a0aec0;letter-spacing:.5px;text-transform:uppercase;}',
        '.sc-btn-row{display:flex;gap:5px;align-items:center;}',
        '.sc-zoom-row{display:flex;gap:6px;align-items:center;}',

        '.sc-ctrl-btn{',
            'min-width:34px;height:34px;border-radius:8px;border:1.5px solid #e2e8f0;',
            'background:white;cursor:pointer;font-size:.95rem;font-weight:700;',
            'color:#4a5568;transition:all .15s;display:flex;align-items:center;justify-content:center;',
            'padding:0 8px;white-space:nowrap;',
        '}',
        '.sc-ctrl-btn:hover{background:#667eea;color:white;border-color:#667eea;}',
        '.sc-ctrl-btn--reset{font-size:.78rem;padding:0 12px;}',

        '.sc-slider{',
            'width:90px;height:4px;accent-color:#667eea;cursor:pointer;',
        '}',

        '.sc-fit-btn{',
            'padding:5px 12px;border-radius:8px;border:1.5px solid #e2e8f0;',
            'background:white;cursor:pointer;font-size:.8rem;font-weight:600;',
            'color:#718096;transition:all .15s;display:flex;align-items:center;gap:5px;',
        '}',
        '.sc-fit-btn.active{background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-color:transparent;}',
        '.sc-fit-btn:hover:not(.active){background:#f7fafc;color:#2d3748;}',

        /* preview */
        '.sc-preview-row{',
            'display:flex;align-items:center;gap:16px;padding:10px 20px;',
            'background:#f7f7f7;flex-shrink:0;border-bottom:1px solid #ebebeb;',
        '}',
        '.sc-preview-label{font-size:.75rem;font-weight:700;color:#a0aec0;white-space:nowrap;}',
        '.sc-previews{display:flex;gap:12px;align-items:center;}',
        '.sc-preview-item{display:flex;flex-direction:column;align-items:center;gap:4px;}',
        '.sc-preview-item-label{font-size:.65rem;color:#a0aec0;font-weight:600;}',

        /* footer */
        '.sc-footer{',
            'display:flex;align-items:center;justify-content:space-between;',
            'padding:14px 20px;flex-shrink:0;flex-wrap:wrap;gap:10px;',
        '}',
        '.sc-file-info{font-size:.78rem;color:#a0aec0;font-weight:500;}',
        '.sc-footer-btns{display:flex;gap:8px;}',

        '.sc-btn-cancel{',
            'padding:10px 22px;border-radius:10px;border:1.5px solid #e2e8f0;',
            'background:white;color:#4a5568;font-weight:600;cursor:pointer;font-size:.88rem;',
            'transition:all .2s;',
        '}',
        '.sc-btn-cancel:hover{background:#f7fafc;border-color:#cbd5e0;}',

        '.sc-btn-apply{',
            'padding:10px 26px;border-radius:10px;border:none;',
            'background:linear-gradient(135deg,#667eea,#764ba2);',
            'color:white;font-weight:700;cursor:pointer;font-size:.9rem;',
            'box-shadow:0 4px 14px rgba(102,126,234,.4);',
            'transition:all .2s;display:flex;align-items:center;gap:7px;',
        '}',
        '.sc-btn-apply:hover{opacity:.9;transform:translateY(-1px);}',
        '.sc-btn-apply:disabled{opacity:.6;cursor:not-allowed;transform:none;}',

        '.sc-spinner{',
            'width:14px;height:14px;border:2px solid rgba(255,255,255,.35);',
            'border-top-color:white;border-radius:50%;',
            'animation:scSpin .6s linear infinite;',
        '}',
        '@keyframes scSpin{to{transform:rotate(360deg)}}',

        /* responsive */
        '@media(max-width:600px){',
            '.sc-canvas-inner{min-height:220px;max-height:280px;}',
            '.sc-controls{padding:8px 10px;}',
            '.sc-ctrl-group{padding:4px 8px;}',
            '.sc-slider{width:60px;}',
            '.sc-dialog{border-radius:14px;}',
        '}',
        ].join('');
    }

    // ── Public API ───────────────────────────────────────────────────────────
    return { open: open };

}(jQuery));


// ============================================================
// INTEGRATION — Intercept semua upload gambar dalam sistem
// ============================================================
$(function () {

    // ── Util: Blob → File object ─────────────────────────────────────────────
    function blobToFile(blob, originalFile) {
        var ext  = blob.type === 'image/png' ? '.png' : '.jpg';
        var name = (originalFile.name.replace(/\.[^.]+$/, '') || 'image') + '_cropped' + ext;
        return new File([blob], name, { type: blob.type, lastModified: Date.now() });
    }

    // ── Util: Set file ke hidden input (DataTransfer trick) ──────────────────
    function injectFileToInput(inputEl, file) {
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            inputEl.files = dt.files;
        } catch (e) {
            console.warn('DataTransfer not supported:', e);
        }
    }

    // ========================================================================
    // 1. TRIP PHOTOS (pic1, pic2, pic3)
    // ========================================================================
    [1, 2, 3].forEach(function (num) {
        var inputId   = '#tripPic' + num;
        var previewId = '#pic' + num + 'Preview';
        var boxId     = '#pic' + num + 'Box';
        var holderId  = '#pic' + num + 'Placeholder';
        var removeId  = '#removePic' + num;

        $(document).on('change', inputId, function (e) {
            var file = e.target.files[0];
            if (!file) return;

            var originalInput = e.target;
            // Reset input value supaya event change trigger semula kalau user pilih fail sama
            e.target.value = '';

            ImageCropperSystem.open({
                file       : file,
                aspectRatio: 4 / 3,
                shape      : 'rect',
                title      : 'Photo ' + num + ' — Trip',
                subtitle   : 'Nisbah 4:3 · Semua gambar nampak penuh',
                icon       : '📸',
                maxWidth   : 1200,
                maxHeight  : 900,
                previews   : [
                    { size: 'lg', label: 'Kad Trip', shape: 'rect' },
                    { size: 'md', label: 'Thumbnail', shape: 'rect' },
                ],
                onDone: function (blob, dataUrl) {
                    var croppedFile = blobToFile(blob, file);
                    injectFileToInput(originalInput, croppedFile);

                    // Update preview box
                    $(previewId).attr('src', dataUrl).css({
                        display     : 'block',
                        objectFit   : 'contain',   // ← tunjuk SEMUA gambar
                        background  : '#1a1a2e',
                    });
                    $(holderId).hide();
                    $(removeId).show();
                    $(boxId).css('border', '2px solid #667eea');
                }
            });
        });
    });

    // ========================================================================
    // 2. COMPANY LOGO
    // ========================================================================
    $(document).on('change', '#companyLogoInput', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        var originalInput = e.target;
        e.target.value = '';

        ImageCropperSystem.open({
            file       : file,
            aspectRatio: 1,
            shape      : 'rect',
            title      : 'Logo Syarikat',
            subtitle   : 'Nisbah 1:1 · Saiz cadangan 500×500px',
            icon       : '🏢',
            maxWidth   : 600,
            maxHeight  : 600,
            previews   : [
                { size: 'lg', label: 'Besar', shape: 'rect' },
                { size: 'md', label: 'Sederhana', shape: 'rect' },
                { size: 'sm', label: 'Kecil', shape: 'rect' },
            ],
            onDone: function (blob, dataUrl) {
                var croppedFile = blobToFile(blob, file);
                injectFileToInput(originalInput, croppedFile);

                // Update preview dalam modal
                $('#companyLogoPreview').attr('src', dataUrl).css({
                    display   : 'block',
                    objectFit : 'contain',
                });
                $('#companyLogoPlaceholder').hide();
                $('#companyLogoPreviewBox').css('border', '2px solid #667eea');
            }
        });
    });

    // ========================================================================
    // 3. AVATAR PROFIL
    // ========================================================================
    $(document).on('change', '#avatarFileInput', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        var originalInput = e.target;
        e.target.value = '';

        ImageCropperSystem.open({
            file       : file,
            aspectRatio: 1,
            shape      : 'circle',
            title      : 'Gambar Profil',
            subtitle   : 'Bulat · Saiz cadangan 400×400px',
            icon       : '👤',
            maxWidth   : 500,
            maxHeight  : 500,
            quality    : 0.95,
            previews   : [
                { size: 'lg', label: 'Profil', shape: 'circle' },
                { size: 'md', label: 'Nav', shape: 'circle' },
                { size: 'sm', label: 'Mini', shape: 'circle' },
            ],
            onDone: function (blob, dataUrl) {
                var croppedFile = blobToFile(blob, file);
                injectFileToInput(originalInput, croppedFile);

                // Update preview dalam avatar modal
                var $preview = $('#avatarPreview');
                $preview.attr('src', dataUrl).addClass('show').css({
                    display    : 'block',
                    objectFit  : 'contain',
                    borderRadius: '50%',
                });
                $('#avatarPlaceholder').hide();
                $('#uploadAvatarSubmitBtn').show();
                $('#fileNameDisplay').text(file.name);
                $('#fileSizeDisplay').text((file.size / 1024 / 1024).toFixed(2) + ' MB');

                // Buka avatar modal kalau belum terbuka
                var $modal = $('#avatarPreviewModal');
                if (!$modal.hasClass('show') && $modal.css('display') === 'none') {
                    $modal.css('display', 'flex').addClass('show');
                }
            }
        });
    });

});