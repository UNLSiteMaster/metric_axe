# axe core

This is an automated accessibility testing metric powered via [axe core](https://github.com/dequelabs/axe-core) and phantomjs. It defaults to displaying only WCAG 2.0 A and AA errors.

It also provides a bookmarklet for live testing. Normally axe testing is accomplished via browser extensions, however because the browser extensions can be updated to a different version of the axe core library than the version that SiteMaster is using, a bookmarklet is an easy way to ensure that the same exact tests are done live as they are done by SiteMaster.

## Install
To install follow these steps (note: requires node.js >= v15)

1. clone this repo to sitemaster's `plugins/metric_axe` directory
2. install `axe-core` with npm in the `plugins/metric_axe` directory: `npm install axe-core` Note: also requires puppeteer minimist which should be install with site_master base
3. update sitemaster's config to include the `metric_axe` plugin
4. if you want to customize the testing config, copy `plugins/metric_axe/config/axe-options.sample.json` to `plugins/metric_axe/config/axe-options.inc.json` and customize
5. Add the following to the `Config::set('PLUGINS', ` array and customize as needed. This will make SiteMaster aware of the plugin but not apply it to any groups.
```
'metric_axe' => [
  // 'weight' => 20, // Adjust weight if desired
  // 'execute_as_user' => 'webaudit-chrome',
  // 'sandbox' => false,
  // 'dark_mode' => false  // Whether to check for darkmode color constrast violations
],
```
6. Add the following your group configuration under the `METRICS` key. This will enable it for the specified group. All metric configuration will override the defaults set in the `'PLUGINS'` array.
```
'metric_axe' => [
  // 'weight' => 20, // Adjust weight if desired
  // 'dark_mode' => false  // Whether to check for darkmode color constrast violations
],
```
