<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bundle</title>
    <!-- Bootstrap for testing -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>JavaScript Bundle Test</h1>
        <p>Testing the minified bundle functionality.</p>
        
        <div class="mt-4">
            <button id="testBtn" class="btn btn-primary">Test Functions</button>
            <div id="testOutput" class="mt-3"></div>
        </div>
    </div>
    
    <!-- Test the bundle -->
    <script src="public/js/bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const output = document.getElementById('testOutput');
            
            // Test 1: Check if omniChat exists
            if (typeof window.omniChat !== 'undefined') {
                output.innerHTML += '<div class="alert alert-success">✓ omniChat class loaded</div>';
            } else {
                output.innerHTML += '<div class="alert alert-danger">✗ omniChat class not found</div>';
            }
            
            // Test 2: Check if RatioCalculator exists
            if (typeof RatioCalculator !== 'undefined') {
                output.innerHTML += '<div class="alert alert-success">✓ RatioCalculator loaded</div>';
            } else {
                output.innerHTML += '<div class="alert alert-danger">✗ RatioCalculator not found</div>';
            }
            
            // Test 3: Check if LeadCapture exists
            if (typeof LeadCapture !== 'undefined') {
                output.innerHTML += '<div class="alert alert-success">✓ LeadCapture loaded</div>';
            } else {
                output.innerHTML += '<div class="alert alert-danger">✗ LeadCapture not found</div>';
            }
            
            // Test button functionality
            document.getElementById('testBtn').addEventListener('click', function() {
                const year = new Date().getFullYear();
                output.innerHTML += `<div class="alert alert-info">Current year test: ${year}</div>`;
            });
        });
    </script>
</body>
</html>