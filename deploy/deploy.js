const fs = require('fs');
const path = require('path');

const DEPLOY_DIR = 'deploy';

// Items to EXCLUDE (not copy)
const EXCLUDE_LIST = [
    'node_modules',
    'build',
    '.git',
    'deploy',
    'package.json',
    'package-lock.json',
    'test-bundle.html',
    'resources/js'  // Don't deploy source JS files
];

async function deploy() {
    console.log('🚀 Creating deployment package...\n');
    
    // Clean deploy directory
    if (fs.existsSync(DEPLOY_DIR)) {
        fs.rmSync(DEPLOY_DIR, { recursive: true });
    }
    fs.mkdirSync(DEPLOY_DIR, { recursive: true });
    
    // Copy everything except excluded
    copyFolderSync('.', DEPLOY_DIR);
    
    console.log('\n✅ Deployment package created in: ' + DEPLOY_DIR);
    console.log('\n📦 Upload ALL contents of "' + DEPLOY_DIR + '" to public_html/');
}

function copyFolderSync(src, dest) {
    const entries = fs.readdirSync(src, { withFileTypes: true });
    
    for (const entry of entries) {
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);
        
        // Skip excluded items
        if (EXCLUDE_LIST.includes(entry.name)) {
            console.log(`  ✗ Skipping: ${entry.name}`);
            continue;
        }
        
        // Skip hidden files/folders (starting with . except .htaccess)
        if (entry.name.startsWith('.') && entry.name !== '.htaccess') {
            continue;
        }
        
        if (entry.isDirectory()) {
            fs.mkdirSync(destPath, { recursive: true });
            copyFolderSync(srcPath, destPath);
            console.log(`  ✓ Folder: ${entry.name}/`);
        } else {
            fs.copyFileSync(srcPath, destPath);
            console.log(`  ✓ File: ${entry.name}`);
        }
    }
}

deploy();