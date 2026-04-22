// ============================================================
// Tab switching for Company Modal
// Add this to your companies.js or main.js file
// ============================================================

$(function() {
    
    // ── Tab Switching ─────────────────────────────────────────
    
    $(document).on('click', '.co-tab', function() {
        const targetTab = $(this).data('tab');
        
        // Update tab buttons
        $('.co-tab').removeClass('active');
        $(this).addClass('active');
        
        // Update tab panels
        $('.co-tab-panel').hide();
        $('#' + targetTab).show();
    });
    
    
    // ── Owner Password Toggle ─────────────────────────────────
    
    $('#toggleOwnerPw').click(function() {
        const input = $('#ownerPassword');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).text(type === 'password' ? '👁️' : '🙈');
    });
    
    
    // ── Owner Password Validation ─────────────────────────────
    
    $('#ownerPasswordConfirm').on('input', function() {
        const password = $('#ownerPassword').val();
        const confirm = $(this).val();
        
        if (confirm && password !== confirm) {
            $('#ownerPwMatchErr').show();
        } else {
            $('#ownerPwMatchErr').hide();
        }
    });
    
    $('#ownerPassword').on('input', function() {
        const password = $(this).val();
        const confirm = $('#ownerPasswordConfirm').val();
        
        if (confirm && password !== confirm) {
            $('#ownerPwMatchErr').show();
        } else {
            $('#ownerPwMatchErr').hide();
        }
    });
    
});