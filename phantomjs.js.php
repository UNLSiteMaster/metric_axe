
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

page.evaluateAsync(function() {
    var axe_options = <?php echo $options ?>;
    
    axe.a11yCheck(window.document, axe_options, function (results) {
        var phantomResults = {};
        phantomResults.metric = 'axe';
        phantomResults.results = results;
        window.callPhantom(phantomResults);
    });
});
