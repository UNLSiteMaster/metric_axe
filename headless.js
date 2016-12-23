exports.evaluate = function(options) {
    //Get the axe options
    var fs = require('fs');
    var path = __dirname+'/config/axe-options.inc.json';
    if (!fs.existsSync(path)) {
        path = __dirname+'/config/axe-options.sample.json';
    }
    var axeOptions = JSON.parse(fs.readFileSync(path, 'utf8'));
    
    //using the given nightmare instance
    return function(nightmare) {
        
        nightmare
            //inject axe
            .inject('js', __dirname+'/node_modules/axe-core/axe.min.js')
            //Run the tests
            .evaluate(function(axeOptions) {
                //Now we need to return a result object
                var promise = axe.run(axeOptions).then(function(result) {
                    return {
                        //The results are stored in the 'results' property
                        //We are only interested in the violations array
                        'results': result.violations,
    
                        //The metric name is stored in the 'name' property with the same value used in Metric::getMachineName()
                        'name': 'axe'
                    };
                });
                
                return promise;
            }, axeOptions);
    };
};
