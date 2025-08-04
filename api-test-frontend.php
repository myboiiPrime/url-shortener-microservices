<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener API Testing Interface</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .nav-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .nav-tab {
            padding: 15px 25px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            color: #495057;
            transition: all 0.3s;
        }
        .nav-tab.active {
            background: white;
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        .nav-tab:hover {
            background: #e9ecef;
        }
        .tab-content {
            display: none;
            padding: 30px;
        }
        .tab-content.active {
            display: block;
        }
        .endpoint-section {
            margin-bottom: 40px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        .endpoint-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .endpoint-title {
            font-weight: bold;
            color: #495057;
        }
        .method-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .method-get { background: #d4edda; color: #155724; }
        .method-post { background: #d1ecf1; color: #0c5460; }
        .method-put { background: #fff3cd; color: #856404; }
        .method-delete { background: #f8d7da; color: #721c24; }
        .endpoint-body {
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.25);
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd8;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .response-section {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #667eea;
        }
        .response-header {
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }
        .response-content {
            background: #ffffff;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-success { background: #28a745; }
        .status-error { background: #dc3545; }
        .status-warning { background: #ffc107; }
        .auth-section {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }
        .auth-section h3 {
            margin-top: 0;
            color: #007bff;
        }
        .token-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin-top: 10px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .nav-tabs {
                flex-wrap: wrap;
            }
            .nav-tab {
                flex: 1;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 URL Shortener API</h1>
            <p>Comprehensive API Testing Interface - Fixed Version</p>
        </div>

        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showTab('auth')">🔐 Authentication</button>
            <button class="nav-tab" onclick="showTab('urls')">🔗 URL Management</button>
            <button class="nav-tab" onclick="showTab('analytics')">📊 Analytics</button>
            <button class="nav-tab" onclick="showTab('gateway')">🌐 Gateway</button>
            <button class="nav-tab" onclick="showTab('health')">❤️ Health Checks</button>
        </div>

        <!-- Authentication Tab -->
        <div id="auth" class="tab-content active">
            <div class="alert alert-info">
                <strong>🔧 Fixed Issues:</strong> Updated to use correct API endpoints, proper request formats (Username instead of FirstName/LastName), and correct login field (UsernameOrEmail).
            </div>
            
            <div class="auth-section">
                <h3>🔐 Authentication Management</h3>
                <p>Manage user authentication and obtain JWT tokens for API access.</p>
                <div id="current-token" class="token-display" style="display: none;">
                    <strong>Current JWT Token:</strong><br>
                    <span id="token-value"></span>
                </div>
            </div>

            <!-- User Registration -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">POST /api/auth/register - User Registration</span>
                    <span class="method-badge method-post">POST</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label>Username: <span style="color: red;">*</span></label>
                                <input type="text" id="reg-username" class="form-control" placeholder="testuser123">
                            </div>
                            <div class="form-group">
                                <label>Email: <span style="color: red;">*</span></label>
                                <input type="email" id="reg-email" class="form-control" placeholder="user@example.com">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>Password: <span style="color: red;">*</span></label>
                                <input type="password" id="reg-password" class="form-control" placeholder="Password123!">
                            </div>
                            <div class="alert alert-warning" style="margin-top: 10px; font-size: 12px;">
                                <strong>Fixed:</strong> Now uses "Username" field instead of FirstName/LastName
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="registerUser()">Register User</button>
                    <div id="register-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="register-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <!-- User Login -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">POST /api/auth/login - User Login</span>
                    <span class="method-badge method-post">POST</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label>Username or Email: <span style="color: red;">*</span></label>
                                <input type="text" id="login-username" class="form-control" placeholder="testuser123 or user@example.com">
                            </div>
                            <div class="alert alert-warning" style="margin-top: 10px; font-size: 12px;">
                                <strong>Fixed:</strong> Now uses "UsernameOrEmail" field instead of just Email
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>Password: <span style="color: red;">*</span></label>
                                <input type="password" id="login-password" class="form-control" placeholder="Password123!">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="loginUser()">Login</button>
                    <div id="login-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="login-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <!-- Get User Profile -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/auth/me - Get User Profile</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <p><em>Requires authentication token</em></p>
                    <button class="btn btn-primary" onclick="getUserProfile()">Get Profile</button>
                    <div id="profile-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="profile-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <!-- Update User Profile -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">PUT /api/auth/me - Update User Profile</span>
                    <span class="method-badge method-put">PUT</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label>Username:</label>
                                <input type="text" id="update-username" class="form-control" placeholder="newusername">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" id="update-email" class="form-control" placeholder="newemail@example.com">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="updateUserProfile()">Update Profile</button>
                    <div id="update-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="update-content" class="response-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- URL Management Tab -->
        <div id="urls" class="tab-content">
            <!-- Shorten URL -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">POST /api/url/shorten - Shorten URL</span>
                    <span class="method-badge method-post">POST</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label>Original URL:</label>
                                <input type="url" id="shorten-url" class="form-control" placeholder="https://example.com/very-long-url">
                            </div>
                        </div>

                    </div>
                    <button class="btn btn-primary" onclick="shortenUrl()">Shorten URL</button>
                    <div id="shorten-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="shorten-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <!-- Get URL Info -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/url/{shortCode} - Get URL Information</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="form-group">
                        <label>Short Code:</label>
                        <input type="text" id="get-shortcode" class="form-control" placeholder="abc123">
                    </div>
                    <button class="btn btn-primary" onclick="getUrlInfo()">Get URL Info</button>
                    <div id="urlinfo-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="urlinfo-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <!-- Get User URLs -->
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/url/user/{userId} - Get User URLs</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <p><em>Requires authentication token</em></p>
                    <button class="btn btn-primary" onclick="getUserUrls()">Get My URLs</button>
                    <div id="userurls-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="userurls-content" class="response-content"></div>
                    </div>
                </div>
            </div>
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">DELETE /api/url/{shortCode} - Delete URL</span>
                    <span class="method-badge method-delete">DELETE</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label for="delete-shortcode">Short Code:</label>
                                <input type="text" id="delete-shortcode" class="form-control" placeholder="abc123">
                            </div>
                            <button class="btn btn-primary" onclick="deleteUrl()">Delete URL</button>
                        </div>
                        <div id="delete-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="delete-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics" class="tab-content">
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">POST /api/analytics/click - Record Click</span>
                    <span class="method-badge method-post">POST</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label for="click-shortcode">Short Code:</label>
                                <input type="text" id="click-shortcode" class="form-control" placeholder="abc123">
                            </div>
                            <button class="btn btn-primary" onclick="recordClick()">Record Click</button>
                        </div>
                        <div id="click-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="click-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/analytics/url/{shortCode} - Get URL Analytics</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label for="analytics-shortcode">Short Code:</label>
                                <input type="text" id="analytics-shortcode" class="form-control" placeholder="abc123">
                            </div>
                            <button class="btn btn-primary" onclick="getUrlAnalytics()">Get Analytics</button>
                        </div>
                        <div id="analytics-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="analytics-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/analytics/user - Get User Analytics</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <p>Requires authentication token</p>
                            <button class="btn btn-primary" onclick="getUserAnalytics()">Get User Analytics</button>
                        </div>
                        <div id="useranalytics-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="useranalytics-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/analytics/dashboard - Get Dashboard</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <p>Requires authentication token</p>
                            <button class="btn btn-primary" onclick="getDashboard()">Get Dashboard</button>
                        </div>
                        <div id="dashboard-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="dashboard-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/analytics/top-urls - Get Top URLs</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label for="top-limit">Limit:</label>
                                <input type="number" id="top-limit" class="form-control" placeholder="10" value="10">
                            </div>
                            <button class="btn btn-primary" onclick="getTopUrls()">Get Top URLs</button>
                        </div>
                        <div id="topurls-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="topurls-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Gateway Tab -->
        <div id="gateway" class="tab-content">
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/gateway/services - Get Services</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <button class="btn btn-primary" onclick="getServices()">Get Services</button>
                        </div>
                        <div id="services-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="services-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/gateway/routes - Get Routes</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <button class="btn btn-primary" onclick="getRoutes()">Get Routes</button>
                        </div>
                        <div id="routes-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="routes-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/gateway/load-balancer - Check Service Health</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label for="service-name">Service Name:</label>
                                <select id="service-name" class="form-control">
                                    <option value="UserService">User Service</option>
                                    <option value="UrlShorteningService">URL Shortening Service</option>
                                    <option value="AnalyticsService">Analytics Service</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" onclick="checkServiceHealth()">Check Health</button>
                        </div>
                        <div id="servicehealth-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="servicehealth-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">GET /api/gateway/next-endpoint - Get Next Endpoint</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <div class="grid">
                        <div>
                            <div class="form-group">
                                <label for="endpoint-service">Service Name:</label>
                                <select id="endpoint-service" class="form-control">
                                    <option value="UserService">User Service</option>
                                    <option value="UrlShorteningService">URL Shortening Service</option>
                                    <option value="AnalyticsService">Analytics Service</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" onclick="getNextEndpoint()">Get Next Endpoint</button>
                        </div>
                        <div id="nextendpoint-response" class="response-section" style="display: none;">
                            <div class="response-header">Response:</div>
                            <div id="nextendpoint-response-content" class="response-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Checks Tab -->
        <div id="health" class="tab-content">
            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">API Gateway Health</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <button class="btn btn-primary" onclick="checkHealth('gateway')">Check API Gateway</button>
                    <div id="health-gateway-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="health-gateway-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">User Service Health</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <button class="btn btn-primary" onclick="checkHealth('user')">Check User Service</button>
                    <div id="health-user-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="health-user-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">URL Service Health</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <button class="btn btn-primary" onclick="checkHealth('url')">Check URL Service</button>
                    <div id="health-url-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="health-url-content" class="response-content"></div>
                    </div>
                </div>
            </div>

            <div class="endpoint-section">
                <div class="endpoint-header">
                    <span class="endpoint-title">Analytics Service Health</span>
                    <span class="method-badge method-get">GET</span>
                </div>
                <div class="endpoint-body">
                    <button class="btn btn-primary" onclick="checkHealth('analytics')">Check Analytics Service</button>
                    <div id="health-analytics-response" class="response-section" style="display: none;">
                        <div class="response-header">Response:</div>
                        <div id="health-analytics-content" class="response-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables - UPDATED: Using production API Gateway URL
        let currentToken = '';
        let currentUserId = '';
        const API_BASE = 'https://url-shortener-microservices.onrender.com';

        // Tab management
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Utility functions
        function showResponse(elementId, data, status = 200) {
            const responseElement = document.getElementById(elementId);
            const contentElement = document.getElementById(elementId.replace('-response', '-content'));
            
            responseElement.style.display = 'block';
            
            const statusClass = status >= 200 && status < 300 ? 'status-success' : 
                               status >= 400 ? 'status-error' : 'status-warning';
            
            contentElement.innerHTML = `<div style="margin-bottom: 10px;">
                <span class="status-indicator ${statusClass}"></span>
                <strong>Status: ${status}</strong>
            </div>${JSON.stringify(data, null, 2)}`;
        }

        function getAuthHeaders() {
            return currentToken ? { 'Authorization': `Bearer ${currentToken}` } : {};
        }

        function updateTokenDisplay() {
            const tokenDisplay = document.getElementById('current-token');
            const tokenValue = document.getElementById('token-value');
            
            if (currentToken) {
                tokenDisplay.style.display = 'block';
                tokenValue.textContent = currentToken;
            } else {
                tokenDisplay.style.display = 'none';
            }
        }

        // API Functions

        // Authentication - FIXED: Updated to use correct field names
        async function registerUser() {
            const data = {
                Username: document.getElementById('reg-username').value,  // FIXED: Changed from firstName/lastName
                Email: document.getElementById('reg-email').value,
                Password: document.getElementById('reg-password').value
            };

            try {
                const response = await fetch(`${API_BASE}/api/auth/register`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok && result.token) {
                    currentToken = result.token;
                    if (result.user && result.user.id) {
                        currentUserId = result.user.id;
                    }
                    updateTokenDisplay();
                }
                
                showResponse('register-response', result, response.status);
            } catch (error) {
                showResponse('register-response', { error: error.message }, 500);
            }
        }

        async function loginUser() {
            const data = {
                UsernameOrEmail: document.getElementById('login-username').value,  // FIXED: Changed from email
                Password: document.getElementById('login-password').value
            };

            try {
                const response = await fetch(`${API_BASE}/api/auth/login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok && result.token) {
                    currentToken = result.token;
                    if (result.user && result.user.id) {
                        currentUserId = result.user.id;
                    }
                    updateTokenDisplay();
                }
                
                showResponse('login-response', result, response.status);
            } catch (error) {
                showResponse('login-response', { error: error.message }, 500);
            }
        }

        async function getUserProfile() {
            try {
                const response = await fetch(`${API_BASE}/api/auth/me`, {
                    headers: getAuthHeaders()
                });
                
                const result = await response.json();
                showResponse('profile-response', result, response.status);
            } catch (error) {
                showResponse('profile-response', { error: error.message }, 500);
            }
        }

        async function updateUserProfile() {
            const data = {
                Username: document.getElementById('update-username').value,
                Email: document.getElementById('update-email').value
            };

            try {
                const response = await fetch(`${API_BASE}/api/auth/me`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json',
                        ...getAuthHeaders()
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                showResponse('update-response', result, response.status);
            } catch (error) {
                showResponse('update-response', { error: error.message }, 500);
            }
        }

        // URL Management
        async function shortenUrl() {
            const data = {
                OriginalUrl: document.getElementById('shorten-url').value,  // FIXED: Capitalized field name
                UserId: currentUserId || 'anonymous'  // FIXED: Added required UserId field
            };

            try {
                const response = await fetch(`${API_BASE}/api/url/shorten`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        ...getAuthHeaders()
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                showResponse('shorten-response', result, response.status);
            } catch (error) {
                showResponse('shorten-response', { error: error.message }, 500);
            }
        }

        async function getUrlInfo() {
            const shortCode = document.getElementById('get-shortcode').value;
            
            try {
                const response = await fetch(`${API_BASE}/api/url/${shortCode}`);
                const result = await response.json();
                showResponse('urlinfo-response', result, response.status);
            } catch (error) {
                showResponse('urlinfo-response', { error: error.message }, 500);
            }
        }

        async function getUserUrls() {
            try {
                const response = await fetch(`${API_BASE}/api/url/user/${currentUserId}`, {
                    headers: getAuthHeaders()
                });
                
                const result = await response.json();
                showResponse('userurls-response', result, response.status);
            } catch (error) {
                showResponse('userurls-response', { error: error.message }, 500);
            }
        }

        // Analytics
        async function getUrlAnalytics() {
            const shortCode = document.getElementById('analytics-shortcode').value;
            
            try {
                const response = await fetch(`${API_BASE}/api/analytics/url/${shortCode}`);
                const result = await response.json();
                showResponse('analytics-response', result, response.status);
            } catch (error) {
                showResponse('analytics-response', { error: error.message }, 500);
            }
        }

        async function getUserAnalytics() {
            try {
                const response = await fetch(`${API_BASE}/api/analytics/user/${currentUserId}`, {
                    headers: getAuthHeaders()
                });
                
                const result = await response.json();
                showResponse('useranalytics-response', result, response.status);
            } catch (error) {
                showResponse('useranalytics-response', { error: error.message }, 500);
            }
        }

        async function recordClick() {
            const shortCode = document.getElementById('click-shortcode').value;
            
            try {
                const response = await fetch(`${API_BASE}/api/analytics/click`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ shortCode: shortCode })
                });
                
                const result = await response.json();
                showResponse('click-response', result, response.status);
            } catch (error) {
                showResponse('click-response', { error: error.message }, 500);
            }
        }

        async function getDashboard() {
            try {
                const response = await fetch(`${API_BASE}/api/analytics/dashboard`, {
                    headers: getAuthHeaders()
                });
                
                const result = await response.json();
                showResponse('dashboard-response', result, response.status);
            } catch (error) {
                showResponse('dashboard-response', { error: error.message }, 500);
            }
        }

        async function getTopUrls() {
            const limit = document.getElementById('top-limit').value;
            const url = limit ? `${API_BASE}/api/analytics/top-urls?limit=${limit}` : `${API_BASE}/api/analytics/top-urls`;
            
            try {
                const response = await fetch(url);
                const result = await response.json();
                showResponse('topurls-response', result, response.status);
            } catch (error) {
                showResponse('topurls-response', { error: error.message }, 500);
            }
        }

        async function deleteUrl() {
            const shortCode = document.getElementById('delete-shortcode').value;
            
            try {
                const response = await fetch(`${API_BASE}/api/url/${shortCode}`, {
                    method: 'DELETE',
                    headers: getAuthHeaders()
                });
                
                const result = await response.json();
                showResponse('delete-response', result, response.status);
            } catch (error) {
                showResponse('delete-response', { error: error.message }, 500);
            }
        }

        // Gateway
        async function getServices() {
            try {
                const response = await fetch(`${API_BASE}/api/gateway/services`);
                const result = await response.json();
                showResponse('services-response', result, response.status);
            } catch (error) {
                showResponse('services-response', { error: error.message }, 500);
            }
        }

        async function getRoutes() {
            try {
                const response = await fetch(`${API_BASE}/api/gateway/routes`);
                const result = await response.json();
                showResponse('routes-response', result, response.status);
            } catch (error) {
                showResponse('routes-response', { error: error.message }, 500);
            }
        }

        async function checkServiceHealth() {
            const serviceName = document.getElementById('service-name').value;
            
            try {
                const response = await fetch(`${API_BASE}/api/gateway/load-balancer/${serviceName}`);
                const result = await response.json();
                showResponse('servicehealth-response', result, response.status);
            } catch (error) {
                showResponse('servicehealth-response', { error: error.message }, 500);
            }
        }

        async function getNextEndpoint() {
            const serviceName = document.getElementById('endpoint-service').value;
            
            try {
                const response = await fetch(`${API_BASE}/api/gateway/next-endpoint/${serviceName}`);
                const result = await response.json();
                showResponse('nextendpoint-response', result, response.status);
            } catch (error) {
                showResponse('nextendpoint-response', { error: error.message }, 500);
            }
        }

        // Health Checks - FIXED: Use API Gateway health check endpoints
        async function checkHealth(service) {
            let endpoint;
            
            if (service === 'gateway') {
                endpoint = `${API_BASE}/api/gateway/health`;
            } else {
                // Use API Gateway's service health check endpoint
                const serviceNames = {
                    user: 'UserService',
                    url: 'UrlShorteningService', 
                    analytics: 'AnalyticsService'
                };
                endpoint = `${API_BASE}/api/gateway/services/${serviceNames[service]}/health-check`;
            }

            try {
                const response = await fetch(endpoint, {
                    method: service === 'gateway' ? 'GET' : 'POST',
                    mode: 'cors',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                showResponse(`health-${service}-response`, result, response.status);
            } catch (error) {
                showResponse(`health-${service}-response`, { error: error.message }, 500);
            }
        }

        async function checkAllHealth() {
            const services = ['gateway', 'user', 'url', 'analytics'];
            const results = {};

            for (const service of services) {
                try {
                    let endpoint;
                    let method = 'GET';
                    
                    if (service === 'gateway') {
                        endpoint = `${API_BASE}/api/gateway/health`;
                    } else {
                        const serviceNames = {
                            user: 'UserService',
                            url: 'UrlShorteningService',
                            analytics: 'AnalyticsService'
                        };
                        endpoint = `${API_BASE}/api/gateway/services/${serviceNames[service]}/health-check`;
                        method = 'POST';
                    }

                    const response = await fetch(endpoint, {
                        method: method,
                        mode: 'cors',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });
                    const result = await response.json();
                    results[service] = {
                        status: response.status,
                        healthy: response.ok,
                        data: result
                    };
                } catch (error) {
                    results[service] = {
                        status: 500,
                        healthy: false,
                        error: error.message
                    };
                }
            }

            const overallHealth = Object.values(results).every(r => r.healthy);
            const summary = {
                overallHealthy: overallHealth,
                timestamp: new Date().toISOString(),
                services: results
            };

            showResponse('health-all-response', summary, overallHealth ? 200 : 500);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set default values for testing
            document.getElementById('reg-username').value = 'testuser123';
            document.getElementById('reg-email').value = 'test@example.com';
            document.getElementById('reg-password').value = 'TestPassword123!';
            
            document.getElementById('login-username').value = 'testuser123';
            document.getElementById('login-password').value = 'TestPassword123!';
            
            document.getElementById('shorten-url').value = 'https://www.example.com/very-long-url-that-needs-shortening';
        });
    </script>
</body>
</html>