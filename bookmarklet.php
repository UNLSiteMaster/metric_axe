<?php
ini_set('display_errors', true);
require_once(__DIR__ . '/../../init.php');


$widget_url = \SiteMaster\Core\Config::get('URL') . 'plugins/metric_axe/widget.js.php';

?>

<a href="javascript: (function () { 
    var jsCode = document.createElement('script'); 
    jsCode.setAttribute('src', '<?php echo $widget_url ?>');
  document.body.appendChild(jsCode); 
 }());">SiteMaster Axe</a>
