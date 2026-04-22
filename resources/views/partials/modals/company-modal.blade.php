{{-- ============================================================ --}}
{{-- resources/views/partials/modals/company-modal.blade.php     --}}
{{-- Modal: Tambah / Edit Company + Owner Business Account       --}}
{{-- ============================================================ --}}
<div class="modal-overlay" id="companyModal">
    <div class="modal-content" style="max-width:640px;">
        <span class="modal-close" id="closeCompanyModal">✖</span>
        <h2 class="modal-header" id="companyModalTitle">🏢 Add Company</h2>

        <form id="companyForm" enctype="multipart/form-data">
            <input type="hidden" id="companyId">
            <input type="hidden" id="companyMethod" value="POST">

            {{-- ── Tabs ── --}}
            <div style="display:flex;gap:.5rem;border-bottom:2px solid var(--border-color);margin-bottom:1.5rem;">
                <button type="button" class="co-tab active" data-tab="coTabInfo"
                    style="padding:.55rem 1.1rem;border:none;background:none;cursor:pointer;font-size:.875rem;font-weight:700;color:var(--primary-color);border-bottom:3px solid var(--primary-color);margin-bottom:-2px;">
                    🏢 Maklumat Syarikat
                </button>
                <button type="button" class="co-tab" data-tab="coTabOwner"
                    style="padding:.55rem 1.1rem;border:none;background:none;cursor:pointer;font-size:.875rem;font-weight:600;color:var(--text-muted);border-bottom:3px solid transparent;margin-bottom:-2px;">
                    👤 Akaun Owner
                </button>
            </div>

            {{-- ── Tab 1: Maklumat Syarikat ── --}}
            <div id="coTabInfo" class="co-tab-panel">
                {{-- Logo Upload --}}
                <div style="text-align:center;margin-bottom:1.5rem;">
                    <div id="companyLogoPreviewBox"
                         style="width:110px;height:110px;margin:0 auto 1rem;border:3px dashed var(--border-color);border-radius:16px;display:flex;align-items:center;justify-content:center;background:var(--input-readonly-bg);overflow:hidden;cursor:pointer;">
                        <img id="companyLogoPreview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                        <span id="companyLogoPlaceholder" style="font-size:2.5rem;">🏢</span>
                    </div>
                    <button type="button" id="selectLogoBtn" class="btn btn-secondary" style="font-size:.82rem;">📷 Upload Logo</button>
                    <input type="file" id="companyLogoInput" accept="image/*" style="display:none;">
                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:.4rem;">JPG/PNG, max 2MB</div>
                </div>

                <div class="form-grid" style="grid-template-columns:repeat(2,1fr);gap:1rem;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Nama Syarikat *</label>
                        <input id="companyName" name="name" type="text"
                               placeholder="e.g., Syarikat Pelancongan Sdn Bhd"
                               class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Pendaftaran *</label>
                        <input id="companyRegNo" name="registration_no" type="text"
                               placeholder="e.g., 202001012345"
                               class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telefon Syarikat</label>
                        <input id="companyPhone" name="phone" type="tel"
                               placeholder="e.g., 03-21234567"
                               class="form-input">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Alamat Syarikat *</label>
                        <textarea id="companyAddress" name="address" rows="3"
                                  placeholder="e.g., No. 12, Jalan Ampang, 50450 Kuala Lumpur"
                                  class="form-input" required></textarea>
                    </div>
                </div>
            </div>

            {{-- ── Tab 2: Akaun Owner Business ── --}}
            <div id="coTabOwner" class="co-tab-panel" style="display:none;">
                <div style="background:linear-gradient(135deg,#667eea10,#764ba215);border:2px solid #667eea30;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;font-size:.82rem;color:var(--text-secondary);">
                    <strong style="color:var(--text-primary);">👤 Owner Business Account</strong><br>
                    Cipta akaun login untuk pemilik syarikat. Mereka boleh log masuk ke sistem menggunakan emel dan kata laluan ini dengan role <code style="background:var(--input-readonly-bg);padding:1px 6px;border-radius:4px;">owner_business</code>.
                </div>

                {{-- Existing owner info (edit mode) --}}
                <div id="existingOwnerInfo" style="display:none;background:var(--input-readonly-bg);border-radius:10px;padding:.85rem 1rem;margin-bottom:1rem;border:1px solid var(--border-color);">
                    <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:.5rem;">AKAUN SEDIA ADA</div>
                    <div id="existingOwnerDetails" style="font-size:.85rem;color:var(--text-primary);"></div>
                </div>

                <div class="form-grid" style="grid-template-columns:repeat(2,1fr);gap:1rem;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Nama Owner</label>
                        <input id="ownerName" name="owner_name" type="text"
                               placeholder="e.g., Ahmad bin Hassan"
                               class="form-input">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
    <label class="form-label">Role Akaun</label>
    <select id="ownerRole" name="owner_role" class="form-input">
        <option value="owner_business">👔 Owner Business</option>
        <option value="tester">🧪 Tester</option>
    </select>
    <small style="color:var(--text-muted);font-size:.72rem;margin-top:3px;display:block;">
        Tester — akses penuh untuk demo & ujian sistem
    </small>
</div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Emel Login</label>
                        <input id="ownerEmail" name="owner_email" type="email"
                               placeholder="owner@syarikat.com"
                               class="form-input" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kata Laluan</label>
                        <div style="position:relative;">
                            <input type="password" id="ownerPassword" name="owner_password"
                                   placeholder="Min. 6 aksara"
                                   class="form-input" style="padding-right:46px;" autocomplete="new-password">
                            <button type="button" id="toggleOwnerPw"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:18px;padding:4px;">👁️</button>
                        </div>
                        <small style="color:var(--text-muted);font-size:.72rem;margin-top:3px;display:block;">Kosongkan jika tidak mahu tukar kata laluan</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sahkan Kata Laluan</label>
                        <input type="password" id="ownerPasswordConfirm"
                               placeholder="Ulang kata laluan"
                               class="form-input" autocomplete="new-password">
                        <div id="ownerPwMatchErr" style="display:none;color:var(--error-color);font-size:.75rem;margin-top:3px;">⚠️ Kata laluan tidak sepadan</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telefon Owner</label>
                        <input id="ownerPhone" name="owner_phone" type="tel"
                               placeholder="e.g., 0123456789"
                               class="form-input">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;">
                        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem;font-weight:600;padding-bottom:.75rem;">
                            <input type="checkbox" id="ownerActive" name="owner_active" value="1" checked
                                   style="width:17px;height:17px;accent-color:#667eea;cursor:pointer;">
                            Akaun aktif
                        </label>
                    </div>
                </div>
            </div>

            <div style="text-align:center;margin-top:1.5rem;">
                <button type="submit" class="btn btn-success btn-lg">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
.co-tab.active { color:var(--primary-color)!important;border-bottom-color:var(--primary-color)!important;font-weight:700!important; }
</style>