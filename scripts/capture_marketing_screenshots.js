/**
 * One-off: capture marketing screenshots from local SchoolCRM demo.
 * Usage: node scripts/capture_marketing_screenshots.js
 */
const fs = require('fs');
const path = require('path');

const BASE = 'http://localhost/SchoolCRM';
const PASSWORD = 'Demo@2026';
const OUT = path.join(__dirname, '..', 'marketing', 'screenshots');
const VIEWPORT = { width: 1440, height: 900 };

const chromePaths = [
  process.env.CHROME_PATH,
  'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
].filter(Boolean);

function findChrome() {
  for (const p of chromePaths) {
    if (fs.existsSync(p)) return p;
  }
  return null;
}

async function main() {
  let puppeteer;
  try {
    puppeteer = require('puppeteer');
  } catch {
    console.error('Run: npm install puppeteer --no-save (in project root) or use npx puppeteer');
    process.exit(1);
  }

  fs.mkdirSync(OUT, { recursive: true });
  const executablePath = findChrome();

  const browser = await puppeteer.launch({
    headless: 'new',
    executablePath: executablePath || undefined,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900'],
  });

  const page = await browser.newPage();
  await page.setViewport(VIEWPORT);

  async function shot(name, url) {
    const file = path.join(OUT, `${name}.png`);
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
    await page.waitForTimeout(1200);
    await page.screenshot({ path: file, fullPage: false });
    console.log('Saved', file);
  }

  async function login(username, userType) {
    await page.goto(`${BASE}/index.php`, { waitUntil: 'networkidle2' });
    await page.type('#yourUsername', username, { delay: 20 });
    await page.type('#yourPassword', PASSWORD, { delay: 20 });
    const radioId = userType === 0 ? '#studentRadio' : userType === 1 ? '#teacherRadio' : '#adminRadio';
    await page.click(radioId);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
      page.click('button[type="submit"], input[type="submit"], .btn-primary'),
    ]);
  }

  // Login page (no auth)
  await shot('01-login', `${BASE}/index.php`);

  // Student
  await login('aditya.krishnan', 0);
  await shot('02-student-dashboard', `${BASE}/studentDashboard.php`);
  await shot('03-student-report-card', `${BASE}/reportCard.php`);
  await shot('04-student-fees', `${BASE}/FeeDetails.php`);

  // Teacher
  await page.goto(`${BASE}/logout.php`, { waitUntil: 'networkidle2' }).catch(() => {});
  await login('priya.ramachandran', 1);
  await shot('05-teacher-dashboard', `${BASE}/teacher/dashboard.php`);
  await shot('06-teacher-attendance', `${BASE}/teacher/attendance.php`);
  await shot('07-teacher-timetable', `${BASE}/teacher/class_timetable.php`);

  // Admin
  await page.goto(`${BASE}/logout.php`, { waitUntil: 'networkidle2' }).catch(() => {});
  await login('admin', 2);
  await shot('08-admin-dashboard', `${BASE}/admin/dashboard.php`);
  await shot('09-admin-fees', `${BASE}/admin/fees.php`);
  await shot('10-admin-planner', `${BASE}/admin/planner.php`);

  await browser.close();
  console.log('Done.');
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
