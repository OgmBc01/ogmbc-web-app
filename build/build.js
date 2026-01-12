const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

// Configuration
const CONFIG = {
    inputDir: 'resources/js',
    outputDir: 'public/js',
    outputFile: 'bundle.min.js',
    sourceMap: false,
    
    // Files in order of dependency
    files: [
        'main.js',           // First - has DOMContentLoaded listeners
        'ratio-calculator.js', // Second - defines RatioCalculator
        'omni-chat.js'       // Third - depends on DOM being ready
    ]
};

async function build() {
    console.log('🔧 Starting JavaScript build process...\n');
    
    try {
        // Create output directory if it doesn't exist
        if (!fs.existsSync(CONFIG.outputDir)) {
            fs.mkdirSync(CONFIG.outputDir, { recursive: true });
            console.log(`📁 Created directory: ${CONFIG.outputDir}`);
        }
        
        // Read and combine all JS files in order
        let combinedCode = '';
        let fileSizes = {};
        
        for (const file of CONFIG.files) {
            const filePath = path.join(CONFIG.inputDir, file);
            
            if (fs.existsSync(filePath)) {
                const content = fs.readFileSync(filePath, 'utf8');
                const fileSize = Buffer.byteLength(content, 'utf8');
                fileSizes[file] = fileSize;
                
                // Add file separator comment
                combinedCode += `\n\n// ========== ${file} ==========\n\n`;
                
                // Fix potential issues before minification
                let processedContent = preProcessFile(content, file);
                
                combinedCode += processedContent;
                console.log(`✓ Processed: ${file} (${formatSize(fileSize)})`);
            } else {
                console.error(`❌ File not found: ${filePath}`);
                process.exit(1);
            }
        }
        
        const totalOriginalSize = Object.values(fileSizes).reduce((a, b) => a + b, 0);
        console.log(`\n📊 Original total size: ${formatSize(totalOriginalSize)}`);
        
        // Minify and obfuscate
        console.log('⚡ Minifying with Terser...');
        
        const result = await minify(combinedCode, {
            compress: {
                dead_code: true,
                drop_console: false,      // Keep console logs for debugging
                drop_debugger: true,
                conditionals: true,
                evaluate: true,
                booleans: true,
                loops: true,
                unused: true,
                hoist_funs: true,
                if_return: true,
                join_vars: true,
                collapse_vars: true,
                reduce_vars: true,
                warnings: true
            },
            mangle: {
                toplevel: true,
                reserved: [
                    // Preserve global objects
                    'window', 'document', 'console', 'localStorage', 'JSON', 
                    'fetch', 'Date', 'Promise', 'Image', 'Element', 'Event',
                    
                    // Preserve your specific classes and functions
                    'omniChat', 'RatioCalculator', 'LeadCapture',
                    'nopat', 'ebit', 'ebitda', // From ratio calculator
                    
                    // Preserve DOM APIs
                    'addEventListener', 'querySelector', 'querySelectorAll',
                    'getElementById', 'classList', 'createElement',
                    
                    // Preserve external library references
                    'bootstrap', 'html2pdf', 'jspdf', 'jsPDF'
                ]
            },
            format: {
                comments: false,
                beautify: false,
                indent_level: 0
            },
            sourceMap: CONFIG.sourceMap ? {
                filename: CONFIG.outputFile,
                url: `${CONFIG.outputFile}.map`
            } : false
        });
        
        if (result.error) {
            console.error('❌ Minification error:', result.error);
            process.exit(1);
        }
        
        // Write the minified bundle
        const outputPath = path.join(CONFIG.outputDir, CONFIG.outputFile);
        fs.writeFileSync(outputPath, result.code);
        
        const finalSize = Buffer.byteLength(result.code, 'utf8');
        const reduction = ((1 - finalSize / totalOriginalSize) * 100).toFixed(1);
        
        console.log(`
✅ Build successful!
─────────────────────────────────────────
📦 Output: ${outputPath}
📏 Original: ${formatSize(totalOriginalSize)}
📐 Minified: ${formatSize(finalSize)}
📉 Reduction: ${reduction}%
─────────────────────────────────────────
`);
        
        // Create a test HTML file to verify the bundle works
        createTestHtml();
        
    } catch (error) {
        console.error('❌ Build failed:', error.message);
        console.error(error.stack);
        process.exit(1);
    }
}

/**
 * Pre-process files to fix common issues before minification
 */
function preProcessFile(content, filename) {
    let processed = content;
    
    // Fix for main.js - wrap in IIFE
    if (filename === 'main.js') {
        processed = `(function() {
${content}
})();`;
    }
    
    // Fix for ratio-calculator.js - ensure encapsulation
    if (filename === 'ratio-calculator.js') {
        if (!processed.includes('(function() {')) {
            processed = `(function() {
${processed}
})();`;
        }
    }
    
    // Fix for omni-chat.js - DO NOT WRAP or add extra assignments
    // omni-chat.js already handles its own initialization in DOMContentLoaded
    // and exports to window.omniChat, so we don't need to modify it
    if (filename === 'omni-chat.js') {
        // No modification needed - omni-chat.js is self-contained
    }
    
    return processed;
}

/**
 * Format file size in human-readable format
 */
function formatSize(bytes) {
    if (bytes >= 1024 * 1024) {
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    }
    return bytes + ' bytes';
}

/**
 * Create a test HTML file to verify the bundle works
 */
function createTestHtml() {
    const testHtml = `<!DOCTYPE html>
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
                output.innerHTML += \`<div class="alert alert-info">Current year test: \${year}</div>\`;
            });
        });
    </script>
</body>
</html>`;
    
    fs.writeFileSync('test-bundle.html', testHtml);
    console.log('📄 Test HTML created: test-bundle.html');
}

// Run the build
build();