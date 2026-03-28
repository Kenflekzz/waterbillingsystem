const puppeteer = require('puppeteer-core');
const path = require('path');

async function run(exePath) {
  if (!exePath) {
    console.error('Executable path required as first arg');
    process.exit(1);
  }
  const browser = await puppeteer.launch({ executablePath: exePath, args:['--no-sandbox','--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    await page.setViewport({ width: 800, height: 1200 });
    const cwd = process.cwd();
    await page.goto('file://' + path.join(cwd, 'docs', 'dfd-context-wrap.html'));
    await page.screenshot({ path: path.join(cwd, 'docs', 'dfd-context-portrait.png'), clip: { x:0, y:0, width:800, height:1200 } });
    await page.goto('file://' + path.join(cwd, 'docs', 'dfd-level0-wrap.html'));
    await page.screenshot({ path: path.join(cwd, 'docs', 'dfd-level0-portrait.png'), clip: { x:0, y:0, width:800, height:1200 } });
    console.log('Portrait screenshots saved');
  } finally {
    await browser.close();
  }
}

run(process.argv[2]).catch(err => { console.error(err); process.exit(1); });
