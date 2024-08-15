const argv = require('minimist')(process.argv.slice(2));
const { URL } = require('url');
//Convert to an absolute URL by default (for example, this will add a trailing slash after the domain if it wasn't provided)
const url = new URL(argv._[0]).href;

const util = require('util');
const puppeteer = require('puppeteer');
const axe = require('axe-core');
const fs = require('fs');
let path = __dirname + '/config/axe-options.inc.json';
if (!fs.existsSync(path)) {
  path = __dirname + '/config/axe-options.sample.json';
}
const axeOptions = JSON.parse(fs.readFileSync(path, 'utf8'));

const getViolations = async (url, darkMode = false) => {
  try {
    let options = {headless: "new"};
    if (argv.sandbox === 'false') {
      options.args = ['--no-sandbox', '--disable-setuid-sandbox'];
    }
    const prefersColorScheme = darkMode === true ? 'dark' : 'light';
    const browser = await puppeteer.launch(options);
    const page = await browser.newPage();

    if (argv.ua) {
      await page.setUserAgent(argv.ua);
    }

    await page.on('dialog', async dialog => {
      //Auto dismiss dialogs so that the process does not hang waiting on user input.
      await dialog.dismiss();
    });

    await page.emulateMediaFeatures([{name: 'prefers-color-scheme', value: prefersColorScheme}]);
    await page.goto(url);
    await page.addScriptTag({
      path: require.resolve('axe-core')
    });

    // run axe on the page
    const axeResults = await page.evaluate(async (axeOptions) => {
      return await axe.run(axeOptions);
    }, axeOptions);

    await browser.close();

    return axeResults.violations;
  } catch (e) {
    //fail early
    process.exit(1);
  }
};

(async () => {
  const violations = await getViolations(url, false);
  const darkModeContrastViolations = argv.dark_mode === 'true' ? await getViolations(url, true) : [];
  console.log(
    JSON.stringify(
      violations.concat(
        darkModeContrastViolations.filter(violation => violation.id === 'color-contrast')
          .map((item) => {
            item.id += '-darkmode';
            item.help += ' (Dark Mode)';
            return item;
          })
      )));
})();
