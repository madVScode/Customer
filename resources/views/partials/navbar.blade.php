<!-- ================= TOP NAVBAR WITH AVATAR & LOGOUT ================= -->
<div class="top-navbar">
    <div class="navbar-left">
        <div class="app-logo">
            <span class="logo-icon">👥</span>
            <span class="logo-text">Customer Pro</span>
        </div>
        <div class="online-status-indicator">
            <span class="status-dot online"></span>
            <span class="status-text">System Online</span>
        </div>
    </div>
    
    <div class="navbar-right">
        <!-- Online Users Counter -->
        <div class="online-users-badge" id="onlineUsersBadge" title="Online Admins">
            <span class="badge-icon">👥</span>
            <span class="badge-count" id="onlineUsersCount">0</span>
        </div>

        <!-- Notifications Bell -->
        <div class="notifications-bell" id="notificationsBell" title="Notifications">
            <span class="bell-icon">🔔</span>
            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>

            <!-- Notification Dropdown -->
            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position:absolute; right:0; top:50px; width:400px; background:white; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.15); z-index:9999; overflow:hidden;">
                <!-- Header with Delete All Button -->
                <div class="dropdown-header" style="display:flex; justify-content:space-between; align-items:center; padding:15px; font-weight:bold; border-bottom:2px solid #eee; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white;">
                    <span style="font-size:1rem;">🔔 Notifications</span>
                    <!-- DELETE ALL BUTTON -->
                    <button type="button" id="deleteAllBtn" style="
                        font-size:0.75rem; 
                        background:rgba(255,255,255,0.3); 
                        color:white; 
                        border:none; 
                        border-radius:6px; 
                        padding:6px 12px; 
                        cursor:pointer; 
                        font-weight:600; 
                        transition: all 0.2s;
                    ">
                        🗑️ Clear All
                    </button>
                </div>
                
                <!-- Notification List -->
                <div class="dropdown-items" id="notificationList" style="max-height:450px; overflow-y:auto;">
                    <div style="padding:30px; text-align:center; color:#999;">
                        <div style="font-size:3rem; margin-bottom:10px;">🔔</div>
                        <div>No notifications</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="user-profile-dropdown" id="userProfileDropdown">
            <div class="profile-trigger" id="profileTrigger">
                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="user-avatar" id="userAvatar">
                <div class="user-info">
                    <div class="user-name" id="userName">{{ auth()->user()->name }}</div>
                    <div class="user-role" id="userRole">{{ auth()->user()->role_name }}</div>
                </div>
                <span class="dropdown-arrow">▼</span>
            </div>

            <!-- Dropdown Menu -->
            <div class="profile-dropdown-menu" id="profileDropdownMenu" style="display: none;">
                <div class="dropdown-header">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="dropdown-avatar">
                    <div class="dropdown-user-info">
                        <div class="dropdown-user-name">{{ auth()->user()->name }}</div>
                        <div class="dropdown-user-email">{{ auth()->user()->email }}</div>
                        <div class="dropdown-user-role-badge role-{{ auth()->user()->role }}">
                            @if(auth()->user()->isAdminManage())
                                🔧 Admin Manage
                            @else
                                📊 Admin Record
                            @endif
                        </div>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <!-- Upload Avatar Option -->
                <a href="#" class="dropdown-item" id="uploadAvatarBtn">
                    <span class="item-icon">📷</span>
                    <span class="item-text">Change Avatar</span>
                </a>

                <!-- Profile Option -->
                <a href="#" class="dropdown-item" id="viewProfileBtn">
                    <span class="item-icon">👤</span>
                    <span class="item-text">My Profile</span>
                </a>

                <!-- Online Status -->
                <div class="dropdown-item status-item">
                    <span class="item-icon">🟢</span>
                    <span class="item-text">Online</span>
                    <span class="status-time" id="onlineTime">Active now</span>
                </div>

                <div class="dropdown-divider"></div>

                <!-- Logout Form -->
                <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="dropdown-item logout-item">
                        <span class="item-icon">🚪</span>
                        <span class="item-text">Logout</span>
                        <span class="logout-arrow">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden File Input for Avatar Upload -->
<input type="file" id="avatarFileInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">

<!-- Avatar Preview Modal -->
<div class="modal-overlay" id="avatarPreviewModal">
    <div class="modal-content avatar-modal">
        <span class="modal-close" id="closeAvatarModal">✖</span>
        <h2 class="modal-header">📷 Upload New Avatar</h2>

        <div class="avatar-preview-container">
            <div class="avatar-preview-box">
                <img src="" alt="Preview" id="avatarPreview" class="avatar-preview-image">
                <div class="avatar-preview-placeholder" id="avatarPlaceholder">
                    <span class="placeholder-icon">📷</span>
                    <span class="placeholder-text">No image selected</span>
                </div>
            </div>
            <div class="avatar-preview-info">
                <div class="info-item">
                    <span class="info-label">File:</span>
                    <span class="info-value" id="fileNameDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Size:</span>
                    <span class="info-value" id="fileSizeDisplay">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dimensions:</span>
                    <span class="info-value" id="fileDimensionsDisplay">-</span>
                </div>
            </div>
        </div>

        <div class="avatar-upload-requirements">
            <div class="requirement-title">📋 Requirements:</div>
            <ul class="requirement-list">
                <li>✅ Format: JPG or PNG only</li>
                <li>✅ Max size: 2MB</li>
                <li>✅ Recommended: Square image (500x500px)</li>
                <li>✅ Min dimensions: 200x200px</li>
            </ul>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelAvatarBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="selectAvatarBtn">
                📁 Select Image
            </button>
            <button type="button" class="btn btn-success" id="uploadAvatarSubmitBtn" style="display: none;">
                ✅ Upload Avatar
            </button>
        </div>
    </div>
</div>

<style>
/* ===============================================
   TOP NAVBAR STYLES
   =============================================== */

.top-navbar {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 999;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 30px;
}

.app-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 1.3rem;
    font-weight: 700;
}

.logo-icon {
    font-size: 1.8rem;
    animation: bounce 2s infinite;
}

.online-status-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    color: white;
    font-size: 0.85rem;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #4caf50;
    animation: pulse 2s infinite;
}

.status-dot.online {
    background: #4caf50;
    box-shadow: 0 0 10px #4caf50;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.7;
        transform: scale(1.2);
    }
}

.navbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* Online Users Badge */
.online-users-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    padding: 10px 16px;
    border-radius: 25px;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
}

.online-users-badge:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.badge-icon {
    font-size: 1.2rem;
}

.badge-count {
    font-weight: 700;
    font-size: 0.95rem;
}

/* Notifications Bell */
.notifications-bell {
    position: relative;
    background: rgba(255, 255, 255, 0.2);
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.notifications-bell:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.bell-icon {
    font-size: 1.3rem;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f44336;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
    animation: bounceIn 0.5s;
}

@keyframes bounceIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

/* Notification Dropdown */
.notification-dropdown {
    animation: slideDownFade 0.3s ease;
}

@keyframes slideDownFade {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-dropdown .dropdown-items::-webkit-scrollbar {
    width: 6px;
}

.notification-dropdown .dropdown-items::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.notification-dropdown .dropdown-items::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.notification-dropdown .dropdown-items::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Delete All Button Hover */
#deleteAllBtn:hover {
    background: rgba(255, 255, 255, 0.5) !important;
    transform: scale(1.05);
}

/* Notification Item Styles */
.notification-item-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    transition: background 0.2s;
    cursor: pointer;
}

.notification-item-wrapper:hover {
    background: #f8f9fa;
}

.notification-item-wrapper:last-child {
    border-bottom: none;
}

.notification-item-content {
    flex: 1;
}

.notification-item-delete {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #999;
    font-size: 1.1rem;
}

.notification-item-delete:hover {
    background: #fee;
    color: #f44336;
    transform: scale(1.1);
}

/* User Profile Dropdown */
.user-profile-dropdown {
    position: relative;
}

.profile-trigger {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px 8px 8px;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s;
}

.profile-trigger:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 3px solid white;
    object-fit: cover;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name {
    color: white;
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1;
}

.user-role {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.75rem;
    line-height: 1;
}

.dropdown-arrow {
    color: white;
    font-size: 0.7rem;
    transition: transform 0.3s;
}

.profile-trigger.active .dropdown-arrow {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.profile-dropdown-menu {
    position: absolute;
    top: 60px;
    right: 0;
    width: 320px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    animation: slideDown 0.3s ease;
    z-index: 1000;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
}

.dropdown-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid white;
    object-fit: cover;
}

.dropdown-user-info {
    flex: 1;
}

.dropdown-user-name {
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 4px;
}

.dropdown-user-email {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.dropdown-user-role-badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    font-size: 0.75rem;
    color: white;
    font-weight: 600;
}

.dropdown-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 8px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    color: #2d3748;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 0.95rem;
}

.dropdown-item:hover {
    background: #f7fafc;
    color: #667eea;
}

.item-icon {
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
}

.item-text {
    flex: 1;
    font-weight: 500;
}

.status-item {
    cursor: default;
}

.status-item:hover {
    background: transparent;
    color: #2d3748;
}

.status-time {
    font-size: 0.8rem;
    color: #a0aec0;
}

.logout-item {
    color: #f56565;
    font-weight: 600;
}

.logout-item:hover {
    background: #fff5f5;
    color: #e53e3e;
}

.logout-arrow {
    font-size: 1.2rem;
    transition: transform 0.2s;
}

.logout-item:hover .logout-arrow {
    transform: translateX(5px);
}

/* Avatar Upload Modal */
.avatar-modal {
    max-width: 600px;
}

.avatar-preview-container {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.avatar-preview-box {
    flex: 1;
    aspect-ratio: 1;
    border: 3px dashed #cbd5e0;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    background: #f7fafc;
}

.avatar-preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: none;
}

.avatar-preview-image.show {
    display: block;
}

.avatar-preview-placeholder {
    text-align: center;
    color: #a0aec0;
}

.placeholder-icon {
    font-size: 4rem;
    display: block;
    margin-bottom: 10px;
}

.placeholder-text {
    font-size: 0.9rem;
}

.avatar-preview-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 0.85rem;
    color: #718096;
    font-weight: 600;
}

.info-value {
    font-size: 0.95rem;
    color: #2d3748;
    padding: 8px 12px;
    background: #f7fafc;
    border-radius: 8px;
}

.avatar-upload-requirements {
    background: #edf2f7;
    padding: 15px;
    border-radius: 10px;
    margin: 20px 0;
}

.requirement-title {
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 10px;
}

.requirement-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.requirement-list li {
    padding: 5px 0;
    font-size: 0.9rem;
    color: #4a5568;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 768px) {
    .top-navbar {
        left: 0;
        padding: 0 15px;
    }

    .app-logo .logo-text {
        display: none;
    }

    .online-status-indicator {
        display: none;
    }

    .user-info {
        display: none;
    }

    .profile-dropdown-menu {
        width: 280px;
    }
    
    .notification-dropdown {
        width: 320px;
    }
}

/* Adjust main content for navbar */
.main-content {
    margin-top: 70px;
}
</style>