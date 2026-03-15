// cdp_records_faker.js
// Usage: node cdp_records_faker.js
// Requires: npm install mysql2 faker

const mysql = require('mysql2/promise');
const { faker } = require('@faker-js/faker');

// Update these with your DB credentials
const dbConfig = {
  host: 'localhost',
  user: 'root',
  password: 'rootpassword',
  database: 'ogmbc',
  port: 3306
};

// Use your actual employee IDs from your employees table
const employeeIds = [2, 3, 4, 13, 15];
const cdpTypes = ['CERTIFICATE', 'COURSE', 'LOYALTY', 'BEHAVIOR'];
const statuses = ['PENDING', 'APPROVED', 'REJECTED'];

function randomDate(start, end) {
  return faker.date.between({ from: new Date(start), to: new Date(end) }).toISOString().slice(0, 10);
}

async function main() {
  const conn = await mysql.createConnection(dbConfig);
  const records = 50; // Number of records to insert
  for (let i = 0; i < records; i++) {
    const employee_id = faker.helpers.arrayElement(employeeIds);
    const cdp_type = faker.helpers.arrayElement(cdpTypes);
    const status = faker.helpers.arrayElement(statuses);
    const title = faker.person.jobTitle() + ' ' + faker.word.sample();
    const description = faker.lorem.sentence();
    const uplift_percentage = faker.number.float({ min: 2, max: 18, precision: 0.01 });
    const effective_date = randomDate('2024-01-01', '2026-03-15');
    const created_by = faker.helpers.arrayElement(employeeIds);
    const created_at = randomDate('2024-01-01', '2026-03-15');
    let approved_by = null, approved_at = null, approval_notes = null;
    if (status === 'APPROVED' || status === 'REJECTED') {
      approved_by = faker.helpers.arrayElement(employeeIds); // always a valid user_id
      approved_at = randomDate(effective_date, '2026-03-15');
      approval_notes = status === 'REJECTED' ? faker.lorem.sentence() : null;
    }
    await conn.execute(
      `INSERT INTO cdp_records (employee_id, cdp_type, title, description, uplift_percentage, effective_date, status, approved_by, approved_at, approval_notes, created_by, created_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [employee_id, cdp_type, title, description, uplift_percentage, effective_date, status, approved_by, approved_at, approval_notes, created_by, created_at]
    );
    console.log(`Inserted record ${i + 1}`);
  }
  await conn.end();
  console.log('Done!');
}

main().catch(err => {
  console.error(err);
});
