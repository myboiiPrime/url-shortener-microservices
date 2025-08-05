<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Config Test</title>
</head>
<body>
    <h1>API Configuration Test</h1>
    <div>
        <h3>Server-side Configuration:</h3>
        <p><strong>Environment:</strong> <?php echo $ENVIRONMENT; ?></p>
        <p><strong>API Server:</strong> <?php echo $API_BASE_SERVER; ?></p>
        <p><strong>API Client:</strong> <?php echo $API_BASE_CLIENT; ?></p>
        <p><strong>Frontend Base:</strong> <?php echo $FRONTEND_BASE_URL; ?></p>
        <p><strong>HTTP Host:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'not set'; ?></p>
    </div>
    
    <div>
        <h3>Client-side JavaScript Test:</h3>
        <p><strong>API Base from PHP:</strong> <span id="apiBase"></span></p>
        <button onclick="testAPI()">Test API Connection</button>
        <div id="result"></div>
    </div>

    <script>
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        document.getElementById('apiBase').textContent = API_BASE;
        
        async function testAPI() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>Testing API connection to: ' + API_BASE + '</p>';
            
            try {
                const response = await fetch(API_BASE + '/api/health');
                const result = await response.text();
                resultDiv.innerHTML += '<p style="color: green;">Success! Response: ' + result + '</p>';
            } catch (error) {
                resultDiv.innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
    </script>
</body>
</html>