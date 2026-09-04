/**
 * Capture marketing screenshots from local SchoolCRM demo.
 * Usage: node scripts/capture_marketing_screenshots.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import puppeteer from 'puppeteer';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
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

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

async function main() {
  fs.mkdirSync(OUT, { recursive: true });
  const executablePath = findChrome();

  const browser = await puppeteer.launch({
    headless: true,
    executablePath: executablePath || undefined,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  const page = await browser.newPage();
  await page.setViewport(VIEWPORT);

  async function shot(name, url) {
    const file = path.join(OUT, `${name}.png`);
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
    await sleep(1500);
    await page.screenshot({ path: file, fullPage: false });
    console.log('Saved', file);
  }

  async function login(username, userType) {
    await page.goto(`${BASE}/index.php`, { waitUntil: 'networkidle2' });
    await page.evaluate(() => {
      document.querySelector('#yourUsername').value = '';
      document.querySelector('#yourPassword').value = '';
    });
    await page.type('#yourUsername', username, { delay: 15 });
    await page.type('#yourPassword', PASSWORD, { delay: 15 });
    const radioId = userType === 0 ? '#studentRadio' : userType === 1 ? '#teacherRadio' : '#adminRadio';
    await page.click(radioId);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
      page.click('button[type="submit"]'),
    ]);
  }

  await shot('01-login', `${BASE}/index.php`);

  await login('aditya.krishnan', 0);
  await shot('02-student-dashboard', `${BASE}/studentDashboard.php`);
  await shot('03-student-report-card', `${BASE}/reportCard.php`);
  await shot('04-student-fees', `${BASE}/FeeDetails.php`);

  await page.goto(`${BASE}/logout.php`, { waitUntil: 'networkidle2' }).catch(() => {});
  await login('priya.ramachandran', 1);
  await shot('05-teacher-dashboard', `${BASE}/teacher/dashboard.php`);
  await shot('06-teacher-attendance', `${BASE}/teacher/attendance.php`);
  await shot('07-teacher-timetable', `${BASE}/teacher/class_timetable.php`);

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
