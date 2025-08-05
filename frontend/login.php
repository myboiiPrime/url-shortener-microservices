<?php
session_start();

// Configuration - Dual endpoint strategy
$API_BASE_SERVER = 'http://api-gateway';  // For server-side PHP requests
$API_BASE_CLIENT = 'http://localhost:7000'; // For client-side JavaScript requests
$SITE_NAME = 'QuickLink';

// Redirect if already logged in
if (isset($_SESSION['user']) && isset($_SESSION['token'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .logo h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .form-floating label {
            color: #6b7280;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .loading {
            display: none;
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-link"></i>
            <h2><?php echo $SITE_NAME; ?></h2>
            <p class="text-muted">Sign in to your account</p>
        </div>

        <form id="loginForm">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" placeholder="Username" required>
                <label for="username"><i class="fas fa-user me-2"></i>Username</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="rememberMe">
                <label class="form-check-label" for="rememberMe">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                <span class="loading">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
                <span class="btn-text">Sign In</span>
            </button>
        </form>

        <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="errorMessage"></span>
        </div>

        <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
            <i class="fas fa-check-circle me-2"></i>
            <span id="successMessage"></span>
        </div>

        <div class="divider">
            <span>Don't have an account?</span>
        </div>

        <div class="text-center">
            <a href="register.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </a>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                showError('Please fill in all fields');
                return;
            }

            setLoading(true);
            hideAlerts();

            try {
                const response = await fetch(`${API_BASE}/api/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        UsernameOrEmail: username.trim(),
                        Password: password
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showSuccess('Login successful! Redirecting...');
                    
                    console.log('Login result:', result);
                    console.log('User data:', result.User);
                    console.log('Token:', result.Token);
                    
                    // Validate the response data
                    const user = result.User || result.user;
                    const token = result.Token || result.token;
                    
                    if (!user || !token) {
                        console.error('Missing user or token in response');
                        console.error('User:', user);
                        console.error('Token:', token);
                        showError('Invalid response from server. Please try again.');
                        return;
                    }
                    
                    // Store user data in session via AJAX
                    const sessionData = {
                        user: user,
                        token: token
                    };
                    
                    console.log('Session data being sent:', sessionData);
                    console.log('Session data JSON:', JSON.stringify(sessionData));
                    
                    const sessionResponse = await fetch('set_session.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(sessionData)
                    });

                    if (sessionResponse.ok) {
                        const sessionResult = await sessionResponse.json();
                        console.log('Session set successfully:', sessionResult);
                        setTimeout(() => {
                            window.location.href = 'dashboard.php';
                        }, 1500);
                    } else {
                        const sessionError = await sessionResponse.json();
                        console.error('Session error:', sessionError);
                        console.error('Session response status:', sessionResponse.status);
                        showError(`Session error: ${sessionError.error || 'Please try again.'}`);
                    }
                } else {
                    showError(result.error || 'Invalid username or password');
                }
            } catch (error) {
                showError('Network error. Please try again.');
                console.error('Error:', error);
            } finally {
                setLoading(false);
            }
        });

        function setLoading(loading) {
            const loadingSpinner = document.querySelector('.loading');
            const btnText = document.querySelector('.btn-text');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (loading) {
                loadingSpinner.style.display = 'inline-block';
                btnText.textContent = 'Signing In...';
                submitBtn.disabled = true;
            } else {
                loadingSpinner.style.display = 'none';
                btnText.textContent = 'Sign In';
                submitBtn.disabled = false;
            }
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorAlert').style.display = 'block';
        }

        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            document.getElementById('successAlert').style.display = 'block';
        }

        function hideAlerts() {
            document.getElementById('errorAlert').style.display = 'none';
            document.getElementById('successAlert').style.display = 'none';
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            hideAlerts();
        }, 5000);
    </script>
</body>
</html>