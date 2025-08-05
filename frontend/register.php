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
    <title>Sign Up - <?php echo $SITE_NAME; ?></title>
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
            padding: 2rem 0;
        }
        .alert-link {
            font-weight: 600;
            color: inherit !important;
            text-decoration: underline;
        }

        .alert-link:hover {
            text-decoration: none;
            opacity: 0.8;
        }

        .form-control.conflict-error {
            border-color: var(--danger-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25) !important;
            animation: shake 0.8s ease-in-out;
            background-color: rgba(239, 68, 68, 0.05) !important;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-12px); }
            20%, 40%, 60%, 80% { transform: translateX(12px); }
        }

        /* Ensure animation works for all input types */
        input.conflict-error,
        input[type="text"].conflict-error,
        input[type="email"].conflict-error {
            border-color: var(--danger-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25) !important;
            animation: shake 0.8s ease-in-out !important;
            background-color: rgba(239, 68, 68, 0.05) !important;
        }

        .conflict-suggestion {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #856404;
        }

        .conflict-suggestion .fas {
            margin-right: 8px;
            color: #f39c12;
        }

        .conflict-suggestion .fas {
            margin-right: 8px;
            color: #f39c12;
        }


        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
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

        .form-control.is-valid {
            border-color: var(--success-color);
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
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

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }

        .strength-weak { background: var(--danger-color); }
        .strength-medium { background: var(--warning-color); }
        .strength-strong { background: var(--success-color); }

        .validation-feedback {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .valid-feedback {
            color: var(--success-color);
        }

        .invalid-feedback {
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-link"></i>
            <h2><?php echo $SITE_NAME; ?></h2>
            <p class="text-muted">Create your account</p>
        </div>

        <form id="registerForm">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" placeholder="Username" required>
                <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                <div class="validation-feedback" id="usernameValidation"></div>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" placeholder="Email" required>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                <div class="validation-feedback" id="emailValidation"></div>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <small class="text-muted" id="strengthText">Password strength</small>
                </div>
                <div class="validation-feedback" id="passwordValidation"></div>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>
                <label for="confirmPassword"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                <div class="validation-feedback" id="confirmPasswordValidation"></div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                <label class="form-check-label" for="agreeTerms">
                    I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" disabled>
                <span class="loading">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                </span>
                <span class="btn-text">Create Account</span>
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
            <span>Already have an account?</span>
        </div>

        <div class="text-center">
            <a href="login.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
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
        // Cache buster: <?php echo time(); ?>
        // Use the client-side API URL for JavaScript requests
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        console.log('API_BASE set to:', API_BASE);
        
        // Form validation
        const form = document.getElementById('registerForm');
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const agreeTerms = document.getElementById('agreeTerms');
        const submitBtn = document.querySelector('button[type="submit"]');

        // Validation state
        let validationState = {
            username: false,
            email: false,
            password: false,
            confirmPassword: false,
            terms: false
        };

        // Username validation
        username.addEventListener('input', function() {
            const value = this.value.trim();
            const validation = document.getElementById('usernameValidation');
            
            if (value.length < 3) {
                setFieldInvalid(this, validation, 'Username must be at least 3 characters long');
                validationState.username = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                setFieldInvalid(this, validation, 'Username can only contain letters, numbers, and underscores');
                validationState.username = false;
            } else {
                setFieldValid(this, validation, 'Username looks good!');
                validationState.username = true;
            }
            updateSubmitButton();
        });

        // Email validation
        email.addEventListener('input', function() {
            const value = this.value.trim();
            const validation = document.getElementById('emailValidation');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(value)) {
                setFieldInvalid(this, validation, 'Please enter a valid email address');
                validationState.email = false;
            } else {
                setFieldValid(this, validation, 'Email looks good!');
                validationState.email = true;
            }
            updateSubmitButton();
        });

        // Password validation
        password.addEventListener('input', function() {
            const value = this.value;
            const validation = document.getElementById('passwordValidation');
            const strength = calculatePasswordStrength(value);
            
            updatePasswordStrength(strength);
            
            if (value.length < 8) {
                setFieldInvalid(this, validation, 'Password must be at least 8 characters long');
                validationState.password = false;
            } else if (strength.score < 2) {
                setFieldInvalid(this, validation, 'Password is too weak. Add numbers, symbols, or mix case');
                validationState.password = false;
            } else {
                setFieldValid(this, validation, 'Password strength is good!');
                validationState.password = true;
            }
            
            // Revalidate confirm password
            if (confirmPassword.value) {
                confirmPassword.dispatchEvent(new Event('input'));
            }
            
            updateSubmitButton();
        });

        // Confirm password validation
        confirmPassword.addEventListener('input', function() {
            const value = this.value;
            const validation = document.getElementById('confirmPasswordValidation');
            
            if (value !== password.value) {
                setFieldInvalid(this, validation, 'Passwords do not match');
                validationState.confirmPassword = false;
            } else if (value.length === 0) {
                setFieldInvalid(this, validation, 'Please confirm your password');
                validationState.confirmPassword = false;
            } else {
                setFieldValid(this, validation, 'Passwords match!');
                validationState.confirmPassword = true;
            }
            updateSubmitButton();
        });

        // Terms checkbox
        agreeTerms.addEventListener('change', function() {
            validationState.terms = this.checked;
            updateSubmitButton();
        });

        function setFieldValid(field, validation, message) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            validation.textContent = message;
            validation.className = 'validation-feedback valid-feedback';
        }

        function setFieldInvalid(field, validation, message) {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            validation.textContent = message;
            validation.className = 'validation-feedback invalid-feedback';
        }

        function calculatePasswordStrength(password) {
            let score = 0;
            let feedback = [];

            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            return { score, feedback };
        }

        function updatePasswordStrength(strength) {
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            
            if (strength.score <= 2) {
                fill.style.width = '33%';
                fill.className = 'strength-fill strength-weak';
                text.textContent = 'Weak password';
            } else if (strength.score <= 4) {
                fill.style.width = '66%';
                fill.className = 'strength-fill strength-medium';
                text.textContent = 'Medium password';
            } else {
                fill.style.width = '100%';
                fill.className = 'strength-fill strength-strong';
                text.textContent = 'Strong password';
            }
        }

        function updateSubmitButton() {
            const allValid = Object.values(validationState).every(valid => valid);
            submitBtn.disabled = !allValid;
        }

        // Enhanced form submission with conflict handling
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!Object.values(validationState).every(valid => valid)) {
                showError('Please fix all validation errors before submitting');
                return;
            }

            setLoading(true);
            hideAlerts();
            clearConflictErrors();

            try {
                const url = `${API_BASE}/api/auth/register`;
                console.log('Making request to:', url);
                console.log('Request body:', {
                    Username: username.value.trim(),
                    Email: email.value.trim(),
                    Password: '***'
                });
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        Username: username.value.trim(),
                        Email: email.value.trim(),
                        Password: password.value
                    })
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                let result;
                try {
                    result = await response.json();
                    console.log('Response body:', result);
                } catch (parseError) {
                    console.error('Failed to parse response as JSON:', parseError);
                    result = { error: 'Invalid response format' };
                }

                if (response.ok) {
                    showSuccess('Account created successfully! Redirecting to login...');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else if (response.status === 409) {
                    console.log('409 Conflict detected!');
                    console.log('Full conflict response:', result);
                    handleConflictError(result);
                } else if (response.status === 429) {
                    showError('Too many registration attempts. Please wait a few minutes and try again.');
                } else {
                    const errorMessage = result.error || result.Error || result.message || 'Failed to create account';
                    console.log('Other error:', response.status, errorMessage);
                    showError(errorMessage);
                }
            } catch (error) {
                console.error('Network/fetch error:', error);
                showError('Network error. Please try again.');
            } finally {
                setLoading(false);
            }
        });

        function handleConflictError(errorResponse) {
            console.log('=== CONFLICT ERROR HANDLER ===');
            console.log('Error response received:', errorResponse);
            
            hideAlerts(); // Clear any existing alerts
            
            const usernameField = document.getElementById('username');
            const emailField = document.getElementById('email');
            
            if (!usernameField || !emailField) {
                console.error('Username or email field not found!');
                showError('Form fields not found. Please refresh the page.');
                return;
            }
            
            console.log('Username field:', usernameField);
            console.log('Email field:', emailField);
            
            // Initialize conflict detection flags
            let usernameConflict = false;
            let emailConflict = false;
            let conflictDetected = false;
            
            // Check if we have a structured response with Field and Type
            if (errorResponse && typeof errorResponse === 'object') {
                const field = errorResponse.field || errorResponse.Field;
                const type = errorResponse.type || errorResponse.Type;
                const errorMessage = errorResponse.error || errorResponse.Error || errorResponse.message;
                
                console.log('Structured response - Field:', field, 'Type:', type, 'Message:', errorMessage);
                
                // Check for username conflict
                if (field === 'username' || type === 'username_conflict') {
                    usernameConflict = true;
                    console.log('Username conflict detected via structured response');
                }
                
                // Check for email conflict
                if (field === 'email' || type === 'email_conflict') {
                    emailConflict = true;
                    console.log('Email conflict detected via structured response');
                }
                
                // Handle multiple field conflicts (check for arrays or multiple fields)
                if (Array.isArray(errorResponse)) {
                    errorResponse.forEach(error => {
                        const errorField = error.field || error.Field;
                        const errorType = error.type || error.Type;
                        
                        if (errorField === 'username' || errorType === 'username_conflict') {
                            usernameConflict = true;
                        }
                        if (errorField === 'email' || errorType === 'email_conflict') {
                            emailConflict = true;
                        }
                    });
                }
            }
            
            // Fallback: check error message content (for backward compatibility)
            if (!usernameConflict && !emailConflict) {
                const errorMessage = typeof errorResponse === 'string' ? errorResponse :
                    (errorResponse?.error || errorResponse?.Error || errorResponse?.message || '');
                const lowerError = errorMessage.toLowerCase();
                
                console.log('Fallback message parsing for:', errorMessage);
                
                if (lowerError.includes('username')) {
                    usernameConflict = true;
                    console.log('Username conflict detected via message parsing');
                }
                
                if (lowerError.includes('email')) {
                    emailConflict = true;
                    console.log('Email conflict detected via message parsing');
                }
            }
            
            // Handle conflicts based on detection results
            if (usernameConflict && emailConflict) {
                // Both fields have conflicts - animate both simultaneously
                console.log('Both username and email conflicts detected - animating both fields');
                
                triggerShakeAnimation(usernameField);
                triggerShakeAnimation(emailField);
                
                showError('Both username and email are already taken. Please choose different credentials.');
                
                // Focus on the first field (username) after animation
                setTimeout(() => {
                    usernameField.focus();
                    usernameField.select();
                }, 100);
                
                conflictDetected = true;
            }
            else if (usernameConflict) {
                // Only username conflict
                console.log('Username-only conflict detected');
                
                triggerShakeAnimation(usernameField);
                showError('Username is already taken. Please choose a different username.');
                
                setTimeout(() => {
                    usernameField.focus();
                    usernameField.select();
                }, 100);
                
                conflictDetected = true;
            }
            else if (emailConflict) {
                // Only email conflict
                console.log('Email-only conflict detected');
                
                triggerShakeAnimation(emailField);
                showErrorWithLoginOption('This email is already registered. You can <a href="login.php" class="alert-link">try logging in</a> instead or use a different email.');
                
                setTimeout(() => {
                    emailField.focus();
                    emailField.select();
                }, 100);
                
                conflictDetected = true;
            }
            
            if (!conflictDetected) {
                // Generic conflict - highlight both fields and show error
                console.log('Generic conflict - highlighting both fields');
                
                // Add a small delay between animations to make them more visible
                triggerShakeAnimation(usernameField);
                setTimeout(() => {
                    triggerShakeAnimation(emailField);
                }, 100);
                
                showError('Username or email already exists. Please try different credentials.');
            }
            
            console.log('=== END CONFLICT ERROR HANDLER ===');
        }

        function triggerShakeAnimation(element) {
            console.log('=== SHAKE ANIMATION ===');
            console.log('Element:', element);
            console.log('Element ID:', element.id);
            console.log('Element type:', element.type);
            console.log('Element classes before:', element.className);
            
            // Remove any existing conflict-error class and reset styles
            element.classList.remove('conflict-error');
            element.style.border = '';
            element.style.boxShadow = '';
            element.style.backgroundColor = '';
            
            // Force a reflow to ensure the class removal takes effect
            void element.offsetHeight;
            
            // Add the conflict-error class to trigger animation
            element.classList.add('conflict-error');
            console.log('Element classes after adding conflict-error:', element.className);
            
            // Add immediate visual feedback with inline styles as backup
            element.style.border = '2px solid #ef4444 !important';
            element.style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.6)';
            element.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
            
            // Ensure the element is visible and focused
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Remove the class and reset styles after animation completes
            setTimeout(() => {
                element.classList.remove('conflict-error');
                element.style.border = '';
                element.style.boxShadow = '';
                element.style.backgroundColor = '';
                console.log('Animation completed for', element.id, '- classes removed');
            }, 1200); // Increased timeout to match longer animation
            
            console.log('=== END SHAKE ANIMATION ===');
        }

        function clearConflictErrors() {
            const usernameField = document.getElementById('username');
            const emailField = document.getElementById('email');
            
            if (usernameField) {
                usernameField.classList.remove('conflict-error');
                usernameField.style.border = '';
                usernameField.style.boxShadow = '';
            }
            
            if (emailField) {
                emailField.classList.remove('conflict-error');
                emailField.style.border = '';
                emailField.style.boxShadow = '';
            }
            
            hideAlerts();
        }

        function showErrorWithLoginOption(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            errorMessage.innerHTML = message;
            errorAlert.style.display = 'block';
        }

        // Enhanced username validation
        username.addEventListener('input', function() {
            // Clear conflict errors when user starts typing
            this.classList.remove('conflict-error');
            this.style.border = '';
            this.style.boxShadow = '';
            hideAlerts();
            
            const value = this.value.trim();
            const validation = document.getElementById('usernameValidation');
            
            if (value.length < 3) {
                setFieldInvalid(this, validation, 'Username must be at least 3 characters long');
                validationState.username = false;
            } else if (value.length > 50) {
                setFieldInvalid(this, validation, 'Username must be less than 50 characters');
                validationState.username = false;
            } else if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
                setFieldInvalid(this, validation, 'Username can only contain letters, numbers, hyphens, and underscores');
                validationState.username = false;
            } else {
                setFieldValid(this, validation, 'Username looks good!');
                validationState.username = true;
            }
            updateSubmitButton();
        });

        // Enhanced email validation
        email.addEventListener('input', function() {
            // Clear conflict errors when user starts typing
            this.classList.remove('conflict-error');
            this.style.border = '';
            this.style.boxShadow = '';
            hideAlerts();
            
            const value = this.value.trim();
            const validation = document.getElementById('emailValidation');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(value)) {
                setFieldInvalid(this, validation, 'Please enter a valid email address');
                validationState.email = false;
            } else {
                setFieldValid(this, validation, 'Email looks good!');
                validationState.email = true;
            }
            updateSubmitButton();
        });

        function setLoading(loading) {
            const loadingSpinner = document.querySelector('.loading');
            const btnText = document.querySelector('.btn-text');
            
            if (loading) {
                loadingSpinner.style.display = 'inline-block';
                btnText.textContent = 'Creating Account...';
                submitBtn.disabled = true;
            } else {
                loadingSpinner.style.display = 'none';
                btnText.textContent = 'Create Account';
                updateSubmitButton();
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

        // Test function for debugging - you can call this in browser console
        window.testShakeAnimations = function() {
            console.log('Testing shake animations...');
            const usernameField = document.getElementById('username');
            const emailField = document.getElementById('email');
            
            console.log('Testing username field shake...');
            triggerShakeAnimation(usernameField);
            
            setTimeout(() => {
                console.log('Testing email field shake...');
                triggerShakeAnimation(emailField);
            }, 1500);
        };

        // Make triggerShakeAnimation available globally for testing
        window.triggerShakeAnimation = triggerShakeAnimation;
    </script>
</body>
</html>
