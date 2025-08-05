<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Configuration Test</title>
</head>
<body>
    <h1>API Configuration Test</h1>
    
    <h2>Server Configuration</h2>
    <p><strong>Environment:</strong> <?php echo $ENVIRONMENT; ?></p>
    <p><strong>API Server:</strong> <?php echo $API_BASE_SERVER; ?></p>
    <p><strong>API Client:</strong> <?php echo $API_BASE_CLIENT; ?></p>
    
    <h2>JavaScript Test</h2>
    <button onclick="testAPI()">Test API Connection</button>
    <div id="result"></div>
    
    <script>
        const API_BASE = '<?php echo $API_BASE_CLIENT; ?>';
        console.log('API_BASE:', API_BASE);
        
        async function testAPI() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = 'Testing...';
            
            try {
                // Test health endpoint
                const response = await fetch(`${API_BASE}/api/gateway/health`);
                const data = await response.json();
                
                resultDiv.innerHTML = `
                    <h3>API Test Results</h3>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Response:</strong> ${JSON.stringify(data, null, 2)}</p>
                    <p><strong>API Base URL:</strong> ${API_BASE}</p>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <h3>API Test Error</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p><strong>API Base URL:</strong> ${API_BASE}</p>
                `;
            }
        }
    </script>
</body>
</html>