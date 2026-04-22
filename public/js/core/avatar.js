// ============================================================
// public/js/core/avatar.js - FIXED VERSION
// Avatar Upload — profile picture change with image compression
// Depends: app.js
// ============================================================

(function() {
    'use strict';

    // ══════════════════════════════════════════════════════════
    // 🔥 PREVENT DOUBLE INITIALIZATION
    // ══════════════════════════════════════════════════════════
    if (window.avatarModuleInitialized) {
        console.warn('⚠️ Avatar module already initialized, skipping...');
        return;
    }
    window.avatarModuleInitialized = true;
    console.log('✅ Initializing avatar module...');

    let selectedAvatarFile = null;
    let onlineUsersInterval = null;
    let notificationsInterval = null;

    // ══════════════════════════════════════════════════════════
    // CLEAR OLD INTERVALS (kalau ada)
    // ══════════════════════════════════════════════════════════
    function clearOldIntervals() {
        if (onlineUsersInterval) {
            clearInterval(onlineUsersInterval);
            onlineUsersInterval = null;
        }
        if (notificationsInterval) {
            clearInterval(notificationsInterval);
            notificationsInterval = null;
        }
    }

    // ══════════════════════════════════════════════════════════
    // PROFILE DROPDOWN - Guna .off() sebelum .on()
    // ══════════════════════════════════════════════════════════

    $(document)
        .off('click', '#profileTrigger')
        .on('click', '#profileTrigger', function(e) {
            e.preventDefault(); 
            e.stopPropagation();
            $(this).toggleClass('active');
            $('#profileDropdownMenu').fadeToggle(200);
        });

    $(document)
        .off('click.avatarModule')
        .on('click.avatarModule', function(e) {
            if (!$(e.target).closest('.user-profile-dropdown').length) {
                $('#profileTrigger').removeClass('active');
                $('#profileDropdownMenu').fadeOut(200);
            }
        });

    $('#profileDropdownMenu')
        .off('click.avatarModule')
        .on('click.avatarModule', function(e) {
            e.stopPropagation();
        });

    // ══════════════════════════════════════════════════════════
    // OPEN AVATAR MODAL
    // ══════════════════════════════════════════════════════════

    $(document)
        .off('click', '#uploadAvatarBtn')
        .on('click', '#uploadAvatarBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            resetAvatarPreview();
            $('#profileDropdownMenu').fadeOut(200);
            $('#profileTrigger').removeClass('active');
            
            setTimeout(function() {
                $('#avatarPreviewModal').addClass('show');
            }, 150);
        });

    $('#selectAvatarBtn')
        .off('click')
        .on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#avatarFileInput').trigger('click');
        });

    // ══════════════════════════════════════════════════════════
    // FILE SELECTED
    // ══════════════════════════════════════════════════════════

    $('#avatarFileInput')
        .off('change')
        .on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                alert('Please select JPG or PNG only!'); 
                $(this).val(''); 
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                alert('File must be less than 2MB!'); 
                $(this).val(''); 
                return;
            }

            $('#avatarPlaceholder').html('<div class="processing-loader">⏳ Processing...</div>');

            compressImage(file, function(compressed) {
                selectedAvatarFile = compressed;

                const reader = new FileReader();
                reader.onload = function(ev) {
                    $('#avatarPreview').attr('src', ev.target.result).addClass('show');
                    $('#avatarPlaceholder').hide();
                    $('#uploadAvatarSubmitBtn').show();
                };
                reader.readAsDataURL(compressed);

                $('#fileNameDisplay').text(file.name);
                $('#fileSizeDisplay').text(formatFileSize(compressed.size) + ' (compressed)');

                const img = new Image();
                img.onload = function() {
                    $('#fileDimensionsDisplay').text(`${this.width} x ${this.height}px`);
                    if (this.width < 200 || this.height < 200) {
                        alert('Minimum 200x200px required!');
                        resetAvatarPreview();
                    }
                };
                img.src = URL.createObjectURL(compressed);
            });
        });

    // ══════════════════════════════════════════════════════════
    // UPLOAD SUBMIT
    // ══════════════════════════════════════════════════════════

    $('#uploadAvatarSubmitBtn')
        .off('click')
        .on('click', function() {
            if (!selectedAvatarFile) { 
                alert('Please select an image!'); 
                return; 
            }

            const btn  = $(this);
            const orig = btn.html();
            showUploadProgress();
            btn.prop('disabled', true).html('⏳ Uploading...');

            const formData = new FormData();
            formData.append('avatar', selectedAvatarFile);

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    updateUploadProgress((e.loaded / e.total) * 100);
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        updateUploadProgress(100);
                        setTimeout(function() {
                            // Update semua avatar images
                            const timestamp = Date.now();
                            $('.user-avatar, .dropdown-avatar, #userAvatar').each(function() {
                                $(this).attr('src', resp.avatar_url + '?t=' + timestamp);
                            });
                            
                            if (window.showNotification) {
                                window.showNotification('Avatar uploaded! ✅', 'success');
                            }
                            
                            setTimeout(function() {
                                hideUploadProgress();
                                closeAvatarModal();
                                btn.prop('disabled', false).html(orig);
                            }, 1000);
                        }, 300);
                    } else {
                        if (window.showNotification) {
                            window.showNotification('Upload failed: ' + resp.message, 'error');
                        }
                        hideUploadProgress();
                        btn.prop('disabled', false).html(orig);
                    }
                } else {
                    if (window.showNotification) {
                        window.showNotification('Upload failed! Server error.', 'error');
                    }
                    hideUploadProgress();
                    btn.prop('disabled', false).html(orig);
                }
            });

            xhr.addEventListener('error', function() {
                if (window.showNotification) {
                    window.showNotification('Network error!', 'error');
                }
                hideUploadProgress();
                btn.prop('disabled', false).html(orig);
            });

            xhr.open('POST', '/profile/avatar');
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            xhr.send(formData);
        });

    // ══════════════════════════════════════════════════════════
    // PROGRESS BAR
    // ══════════════════════════════════════════════════════════

    function showUploadProgress() {
        // Remove existing progress bar first
        $('#uploadProgressBar').remove();
        
        $('body').append(`
            <div id="uploadProgressBar" style="position:fixed;top:50%;left:50%;
                transform:translate(-50%,-50%);width:400px;max-width:90%;background:white;border-radius:15px;
                padding:25px;box-shadow:0 10px 40px rgba(0,0,0,0.3);z-index:10002;">
                <div style="text-align:center;font-size:3rem;margin-bottom:15px;">📤</div>
                <div style="font-weight:600;margin-bottom:15px;text-align:center;">Uploading Avatar...</div>
                <div style="width:100%;height:10px;background:#e0e0e0;border-radius:5px;overflow:hidden;margin-bottom:10px;">
                    <div id="progressFill" style="width:0%;height:100%;
                        background:linear-gradient(90deg,#667eea,#764ba2);transition:width 0.3s;"></div>
                </div>
                <div id="progressText" style="text-align:center;font-size:0.9rem;color:#666;font-weight:600;">0%</div>
            </div>
        `);
    }

    function updateUploadProgress(pct) {
        const r = Math.round(pct);
        $('#progressFill').css('width', r + '%');
        $('#progressText').text(r + '%');
    }

    function hideUploadProgress() {
        setTimeout(() => $('#uploadProgressBar').remove(), 300);
    }

    function closeAvatarModal() {
        $('#avatarPreviewModal').removeClass('show');
        resetAvatarPreview();
    }

    function resetAvatarPreview() {
        selectedAvatarFile = null;
        $('#avatarFileInput').val('');
        $('#avatarPreview').removeClass('show').attr('src', '');
        $('#avatarPlaceholder')
            .html('<span class="placeholder-icon">📷</span><span class="placeholder-text">No image selected</span>')
            .show();
        $('#uploadAvatarSubmitBtn').hide();
        $('#fileNameDisplay, #fileSizeDisplay, #fileDimensionsDisplay').text('-');
    }

    // ══════════════════════════════════════════════════════════
    // CLOSE MODAL HANDLERS - dengan .off() untuk elak berganda
    // ══════════════════════════════════════════════════════════

    $('#closeAvatarModal, #cancelAvatarBtn')
        .off('click')
        .on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeAvatarModal();
        });

    $('#avatarPreviewModal')
        .off('click.avatarModal')
        .on('click.avatarModal', function(e) {
            if ($(e.target).is('#avatarPreviewModal')) {
                closeAvatarModal();
            }
        });

    $('.avatar-modal')
        .off('click.avatarModal')
        .on('click.avatarModal', function(e) {
            e.stopPropagation();
        });

    $(document)
        .off('keydown.avatarModal')
        .on('keydown.avatarModal', function(e) {
            if (e.key === 'Escape' && $('#avatarPreviewModal').hasClass('show')) {
                closeAvatarModal();
            }
        });

    // ══════════════════════════════════════════════════════════
    // IMAGE COMPRESSION
    // ══════════════════════════════════════════════════════════

    function compressImage(file, callback) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let w = img.width, h = img.height;
                const max = 500;
                
                if (w > max || h > max) {
                    if (w > h) { 
                        h = h / w * max; 
                        w = max; 
                    } else { 
                        w = w / h * max; 
                        h = max; 
                    }
                }
                
                canvas.width = w; 
                canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                
                canvas.toBlob(
                    blob => callback(new File([blob], file.name, {
                        type: 'image/jpeg', 
                        lastModified: Date.now()
                    })),
                    'image/jpeg', 
                    0.85
                );
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }

    function formatFileSize(bytes) {
        if (!bytes) return '0 Bytes';
        const k     = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i     = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // ══════════════════════════════════════════════════════════
    // NAVBAR COUNTERS (online users + notifications)
    // ══════════════════════════════════════════════════════════

    function updateOnlineUsersCount() {
        $.get('/online-users', function(u) {
            var staff = Array.isArray(u) 
                ? u.filter(function(user) { return user.role !== 'tourist'; }) 
                : [];
            $('#onlineUsersCount').text(staff.length);
        }).fail(function(err) {
            console.error('Failed to update online users:', err);
        });
    }

    function updateNotificationsCount() {
        $.get('/notifications/unread-count', function(res) {
            if (res.count > 0) {
                $('#notificationBadge').text(res.count).show();
            } else {
                $('#notificationBadge').hide();
            }
        }).fail(function(err) {
            console.error('Failed to update notifications:', err);
        });
    }

    // ══════════════════════════════════════════════════════════
    // 🔥 CLEAR OLD INTERVALS & SET NEW ONES
    // ══════════════════════════════════════════════════════════
    clearOldIntervals();

    // Initial updates
    updateOnlineUsersCount();
    updateNotificationsCount();

    // Set new intervals
    onlineUsersInterval = setInterval(updateOnlineUsersCount, 30000);
    notificationsInterval = setInterval(updateNotificationsCount, 10000);

    console.log('✅ Avatar module initialized successfully!');

})();