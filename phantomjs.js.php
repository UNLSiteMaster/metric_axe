
var PATH_TO_AXE = '<?php echo __DIR__ ?>/node_modules/axe-core/axe.min.js';

<?php
$options_file = __DIR__ . '/config/axe-options.inc.json';
if (!file_exists($options_file)) {
    $options_file = __DIR__ . '/config/axe-options.sample.json';
}
$options = file_get_contents($options_file);
?>

page.injectJs(PATH_TO_AXE);

//We need to do async, so tell the sitemaster script to wait on us
async_metrics.push('axe');

//set a timeout to prevent an error from stalling the script
page.evaluateAsync(function() {
  var phantomResults = {};
  phantomResults.metric = 'axe';
  phantomResults.results = {'exception': 'timeout'};
  window.callPhantom(phantomResults);
}, 35000);

//run the normal tests
page.evaluateAsync(function() {
    var axe_options = <?php echo $options ?>;
    
    axe.a11yCheck(window.document, axe_options, function (results) {
        //We don't process the passed tests, so skip this as it can be a very huge array
        results.passes = [];
        
        var phantomResults = {};
        phantomResults.metric = 'axe';
        phantomResults.results = results;
        window.callPhantom(phantomResults);
    });
});
