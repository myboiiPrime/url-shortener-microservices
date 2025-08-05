<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

// Check session expiration
if (isset($_SESSION['expires_at']) && time() > $_SESSION['expires_at']) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// Configuration - Dual endpoint strategy
$API_BASE_SERVER = 'http://api-gateway';  // For server-side PHP requests
$API_BASE_CLIENT = 'http://localhost:7000'; // For client-side JavaScript requests
$SITE_NAME = 'QuickLink';
$user = $_SESSION['user'];
$token = $_SESSION['token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo $SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }

        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: white !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            padding: 0;
        }

        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .main-content {
            padding: 2rem;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: 700;
            margin: 0 auto 1rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #3730a3;
            transform: translateY(-1px);
        }

        .btn-danger {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 500;
            padding: 1rem 1.5rem;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            background: none;
        }

        .tab-content {
            padding-top: 2rem;
        }

        .verification-badge {
            background: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .unverified-badge {
            background: var(--warning-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .loading {
            display: none;
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-link text-primary me-2"></i><?php echo $SITE_NAME; ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="analytics.php"><i class="fas fa-chart-bar me-2"></i>Analytics</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="analytics.php">
                        <i class="fas fa-chart-bar me-2"></i>Analytics
                    </a>
                    <a class="nav-link active" href="profile.php">
                        <i class="fas fa-user-cog me-2"></i>Profile
                    </a>
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-cog text-primary me-2"></i>Profile Settings
                    </h1>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <div class="row">
                    <!-- Profile Overview -->
                    <div class="col-md-4">
                        <div class="profile-card text-center">
                            <div class="avatar">
                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                            </div>
                            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <?php if (isset($user['isEmailVerified']) && $user['isEmailVerified']): ?>
                                <span class="verification-badge">
                                    <i class="fas fa-check-circle me-1"></i>Verified
                                </span>
                            <?php else: ?>
                                <span class="unverified-badge">
                                    <i class="fas fa-exclamation-circle me-1"></i>Unverified
                                </span>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="text-start">
                                <small class="text-muted">Member since:</small><br>
                                <strong><?php echo date('F j, Y', strtotime($user['createdAt'])); ?></strong>
                            </div>
                            
                            <?php if (isset($user['lastLoginAt'])): ?>
                            <div class="text-start mt-2">
                                <small class="text-muted">Last login:</small><br>
                                <strong><?php echo date('F j, Y g:i A', strtotime($user['lastLoginAt'])); ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Account Stats -->
                        <div class="stats-card text-center">
                            <div class="stats-number text-primary"><?php echo $user['totalUrlsCreated'] ?? 0; ?></div>
                            <div class="stats-label">URLs Created</div>
                        </div>
                        
                        <div class="stats-card text-center">
                            <div class="stats-number text-success"><?php echo $user['totalClicks'] ?? 0; ?></div>
                            <div class="stats-label">Total Clicks</div>
                        </div>
                    </div>

                    <!-- Profile Settings -->
                    <div class="col-md-8">
                        <div class="profile-card">
                            <!-- Tabs -->
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab">
                                        <i class="fas fa-user me-2"></i>Account
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                        <i class="fas fa-shield-alt me-2"></i>Security
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                                        <i class="fas fa-cog me-2"></i>Preferences
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="profileTabContent">
                                <!-- Account Tab -->
                                <div class="tab-pane fade show active" id="account" role="tabpanel">
                                    <form id="accountForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!isset($user['isEmailVerified']) || !$user['isEmailVerified']): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Your email address is not verified. 
                                            <button type="button" class="btn btn-sm btn-outline-warning ms-2" onclick="sendVerificationEmail()">
                                                Send Verification Email
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <span class="loading spinner-border me-2" role="status"></span>
                                            <i class="fas fa-save me-2"></i>Update Account
                                        </button>
                                    </form>
                                </div>

                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <form id="passwordForm">
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="newPassword" class="form-label">New Password</label>
                                                    <input type="password" class="form-control" id="newPassword" required>
                                                    <div class="form-text">Minimum 8 characters with letters and numbers</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                    <input type="password" class="form-control" id="confirmPassword" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <span class="loading spinner-border me-2" role="status"></span>
                                            <i class="fas fa-key me-2"></i>Change Password
                                        </button>
                                    </form>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="text-danger">Danger Zone</h6>
                                    <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                                    <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                                        <i class="fas fa-trash me-2"></i>Delete Account
                                    </button>
                                </div>

                                <!-- Preferences Tab -->
                                <div class="tab-pane fade" id="preferences" role="tabpanel">
                                    <form id="preferencesForm">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Timezone</label>
                                            <select class="form-select" id="timezone">
                                                <option value="UTC">UTC</option>
                                                <option value="America/New_York">Eastern Time</option>
                                                <option value="America/Chicago">Central Time</option>
                                                <option value="America/Denver">Mountain Time</option>
                                                <option value="America/Los_Angeles">Pacific Time</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="language" class="form-label">Language</label>
                                            <select class="form-select" id="language">
                                                <option value="en">English</option>
                                                <option value="es">Spanish</option>
                                                <option value="fr">French</option>
                                                <option value="de">German</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                                <label class="form-check-label" for="emailNotifications">
                                                    Email notifications for URL analytics
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="publicProfile">
                                                <label class="form-check-label" for="publicProfile">
                                                    Make my profile public
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <span class="loading spinner-border me-2" role="status"></span>
                                            <i class="fas fa-save me-2"></i>Save Preferences
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        const token = '<?php echo $token; ?>';

        // Account form submission
        document.getElementById('accountForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const loading = submitBtn.querySelector('.loading');
            
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            try {
                const formData = {
                    username: document.getElementById('username').value,
                    email: document.getElementById('email').value
                };
                
                const response = await fetch(`${API_BASE}/api/user/profile`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                if (response.ok) {
                    showAlert('Account updated successfully!', 'success');
                    // Update session data if needed
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const error = await response.json();
                    showAlert(error.message || 'Failed to update account', 'danger');
                }
                
            } catch (error) {
                console.error('Error updating account:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            } finally {
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        // Password form submission
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showAlert('New passwords do not match', 'danger');
                return;
            }
            
            if (newPassword.length < 8) {
                showAlert('Password must be at least 8 characters long', 'danger');
                return;
            }
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const loading = submitBtn.querySelector('.loading');
            
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            try {
                const formData = {
                    currentPassword: document.getElementById('currentPassword').value,
                    newPassword: newPassword
                };
                
                const response = await fetch(`${API_BASE}/api/user/change-password`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                if (response.ok) {
                    showAlert('Password changed successfully!', 'success');
                    document.getElementById('passwordForm').reset();
                } else {
                    const error = await response.json();
                    showAlert(error.message || 'Failed to change password', 'danger');
                }
                
            } catch (error) {
                console.error('Error changing password:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            } finally {
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        // Preferences form submission
        document.getElementById('preferencesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const loading = submitBtn.querySelector('.loading');
            
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            try {
                const formData = {
                    timezone: document.getElementById('timezone').value,
                    language: document.getElementById('language').value,
                    emailNotifications: document.getElementById('emailNotifications').checked,
                    publicProfile: document.getElementById('publicProfile').checked
                };
                
                // For now, just save to localStorage since we don't have a preferences endpoint
                localStorage.setItem('userPreferences', JSON.stringify(formData));
                
                showAlert('Preferences saved successfully!', 'success');
                
            } catch (error) {
                console.error('Error saving preferences:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            } finally {
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        // Load saved preferences
        function loadPreferences() {
            const saved = localStorage.getItem('userPreferences');
            if (saved) {
                const prefs = JSON.parse(saved);
                document.getElementById('timezone').value = prefs.timezone || 'UTC';
                document.getElementById('language').value = prefs.language || 'en';
                document.getElementById('emailNotifications').checked = prefs.emailNotifications !== false;
                document.getElementById('publicProfile').checked = prefs.publicProfile || false;
            }
        }

        // Send verification email
        async function sendVerificationEmail() {
            try {
                const response = await fetch(`${API_BASE}/api/user/send-verification`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    showAlert('Verification email sent! Check your inbox.', 'success');
                } else {
                    showAlert('Failed to send verification email', 'danger');
                }
            } catch (error) {
                console.error('Error sending verification email:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        }

        // Confirm account deletion
        function confirmDeleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                if (confirm('This will permanently delete all your URLs and data. Type "DELETE" to confirm:')) {
                    const confirmation = prompt('Type "DELETE" to confirm account deletion:');
                    if (confirmation === 'DELETE') {
                        deleteAccount();
                    }
                }
            }
        }

        // Delete account
        async function deleteAccount() {
            try {
                const response = await fetch(`${API_BASE}/api/user/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    alert('Account deleted successfully. You will be redirected to the home page.');
                    window.location.href = 'logout.php';
                } else {
                    showAlert('Failed to delete account', 'danger');
                }
            } catch (error) {
                console.error('Error deleting account:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        }

        // Show alert message
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadPreferences();
        });
    </script>
</body>
</html>