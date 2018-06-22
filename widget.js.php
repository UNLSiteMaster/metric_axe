<?php
require_once(__DIR__ . '/../../init.php');

header('Content-Type: application/javascript');
$axe_url = \SiteMaster\Core\Config::get('URL') . 'plugins/metric_axe/node_modules/axe-core/axe.min.js';

$options_file = __DIR__ . '/config/axe-options.inc.json';
if (!file_exists($options_file)) {
    $options_file = __DIR__ . '/config/axe-options.sample.json';
}
    
$options = file_get_contents($options_file);
?>
(function ()  {
    if (!($ = window.jQuery)) {
        var script = document.createElement( 'script' );
        script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js';
        script.onload=sitemaster_loadAxe;
        document.body.appendChild(script);
    }
    else {
        sitemaster_loadAxe();
    }

    function sitemaster_loadAxe() {
        if (!window.sitemaster_axe) {
            var path = '<?php echo $axe_url ?>';
            jQuery.getScript(path, function() {
                window.sitemaster_axe = axe;
                sitemaster_runAxe();
            });
        } else {
            sitemaster_runAxe();
        }
    }
    
    function sitemaster_runAxe() {
        //Notify user of how to use the tool
        if(!localStorage.getItem('sitemaster_axe')) {
            if (window.confirm("Thank you for using SiteMaster aXe. The page will now be tested and results can be found in your JavaScript console." +
                    "\n\nPress okay to suppress this message on this site in the future.")) {
                localStorage.setItem('sitemaster_axe', true);
            }
        }
        
        //Run axe
        console.log('Starting axe testing, this make take a few seconds...');
        
        var options = <?php echo $options ?>;
        
        window.scrollTo(0,0); //Ensure we are at the top of the page
        window.sitemaster_axe.run(document, options, function (err, results) {
            window.scrollTo(0,0); //Go back to the top of the page
            console.log('Finished! There were %s violations.', results.violations.length);
            if (results.violations.length == 0) {
                console.log('Good job! No accessibility violations found.');
            } else {
                var report = [];

                for (var violation in results.violations) {
                    for (node in results.violations[violation].nodes) {
                        var error = {};
                        error.message = results.violations[violation].help;
                        error.description = results.violations[violation].description;
                        error.helpUrl = results.violations[violation].helpUrl;
                        error.target = results.violations[violation].nodes[node].target[0];
                        error.node = $(error.target)[0];
                        error.all = results.violations[violation].nodes[node].all.concat(results.violations[violation].nodes[node].none);
                        error.any = results.violations[violation].nodes[node].any;
                        error.fix = [];
                        
                        if (error.all.length) {
                            error.fix_type = 'all';
                            
                            for (var fix in error.all) {
                                error.fix.push(error.all[fix].message);
                            }
                        } else {
                            error.fix_type = 'any';

                            for (var fix in error.any) {
                                error.fix.push(error.any[fix].message);
                            }
                        }
                        
                        report.push(error);
                    }
                }
                
                for (var error in report) {
                    var message = "Axe error: %s on node %o." +
                        "\n-"+report[error].description +
                        "\n-Learn More: "+report[error].helpUrl +
                        "\n-Fix " + report[error].fix_type + " of ";
                    
                    for (fix in report[error].fix) {
                        message += '\n\t * ' + report[error].fix[fix];
                    }
                    
                    console.warn(message, report[error].message, report[error].node);
                }
            }
            console.log(results);
        });
    }
})();
