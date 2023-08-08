/*
  How to use

  backstop reference --configPath=backstop-settings.js
       backstop test --configPath=backstop-settings.js

  backstop reference --configPath=backstop-settings.js --refhost=http://example.com
       backstop test --configPath=backstop-settings.js --host=http://example.com

  backstop reference --configPath=backstop-settings.js --paths=/,/contact
       backstop test --configPath=backstop-settings.js --paths=/,/contact

  backstop reference --configPath=backstop-settings.js --pathfile=paths
       backstop test --configPath=backstop-settings.js --pathfile=paths

https://fivemilemedia.co.uk/blog/backstopjs-javascript-configuration

 */

/*
  Set up some variables
 */
//var arguments = require('minimist')(process.argv.slice(2)); // grabs the process arguments
var defaultPaths = ['/']; // By default is just checks the homepage
var scenarios = []; // The array that'll have the pages to test

// using a custom function to process the arguments because minimist
// isnt part of the backstopjs docker container. :(
function getArgs() {
  const args = {};
  process.argv
    .slice(2, process.argv.length)
    .forEach(arg => {
      // long arg
      if (arg.slice(0, 2) === '--') {
        const longArg = arg.split('=');
        const longArgFlag = longArg[0].slice(2, longArg[0].length);
        const longArgValue = longArg.length > 1 ? longArg[1] : true;
        args[longArgFlag] = longArgValue;
      }
      // flags
      else if (arg[0] === '-') {
        const flags = arg.slice(1, arg.length).split('');
        flags.forEach(flag => {
          args[flag] = true;
        });
      }
    });
  return args;
}

const arguments = getArgs();
//console.log(arguments);

/*
  Work out the environments that are being compared
 */
// The host to test
if (!arguments.host) {
  arguments.host = "https://t-drupal-base-develop.finalist.nl"; // Default test host


}
// The host to reference
if (!arguments.refhost) {
  arguments.refhost = "https://t-drupal-base-develop.finalist.nl"; // Default test host
}

/*
  Work out which paths to use, either a supplied array, an array from a file, or the defaults
 */
if (arguments.paths) {
  pathString = arguments.paths;
  var paths = pathString.split(',');
} else if (arguments.pathfile) {
  var pathConfig = require('./' + arguments.pathfile + '.js');
  var paths = pathConfig.array;
} else {
  var paths = defaultPaths; // keep with the default of just the homepage
}

for (var k = 0; k < paths.length; k++) {

  scenarios.push({
    "label": paths[k],
    "referenceUrl": arguments.refhost + paths[k],
    "url": arguments.host + paths[k],
    "hideSelectors": ["div.user-form-page__wallpaper"],
    "cookiePath": "",
    "removeSelectors": ["#sitewidealert", "iframe"],
    "delay": 1000,
    "misMatchThreshold": "0.2",
    "onBeforeScript": "puppet/onBefore.js",
    "onReadyScript": "puppet/onReady.js"
  });
}

// Configuration
module.exports = {
  "id": "DBP-develop",
  "viewports": [{
    "label": "1920px",
    "width": 1920,
    "height": 1080
  },
    {
      "label": "1024px",
      "width": 1024,
      "height": 768
    },
    {
      "label": "768px",
      "width": 768,
      "height": 1024
    },
    {
      "label": "360px",
      "width": 360,
      "height": 640
    }
  ],
  "scenarios": scenarios,
  "paths": {
    "bitmaps_reference": "backstop_data/bitmaps_reference",
    "bitmaps_test": "backstop_data/bitmaps_test",
    "engine_scripts": "backstop_data/engine_scripts",
    "html_report": "backstop_data/html_report",
    "ci_report": "backstop_data/ci_report"
  },
  "report": ["CI", "html"],
  "engine": "puppeteer",
  "engineOptions": {
    "headless": true,
    "ignoreHTTPSErrors": true,
    "args": ["-- headless", "--no-sandbox", "--disable-setuid-sandbox"]
  },
  "ci": {
    "format": "junit",
    "testSuiteName": "backstopJS"
  },
  "asyncCaptureLimit": 3,
  "asyncCompareLimit": 10,
  "debug": false,
  "debugWindow": false
};
