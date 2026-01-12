const fs = require('fs');
const path = require('path');

const SOURCE_DIR = 'resources/js';
const BACKUP_DIR = 'build/backup';

// Create backup directory
if (!fs.existsSync(BACKUP_DIR)) {
    fs.mkdirSync(BACKUP_DIR, { recursive: true });
}

// Copy files
const files = fs.readdirSync(SOURCE_DIR);
files.forEach(file => {
    if (file.endsWith('.js')) {
        const source = path.join(SOURCE_DIR, file);
        const dest = path.join(BACKUP_DIR, file);
        fs.copyFileSync(source, dest);
        console.log(`✓ Backed up: ${file}`);
    }
});

console.log(`\n✅ Backup complete in ${BACKUP_DIR}/`);
console.log('Original files are preserved.');