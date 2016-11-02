<p>
    aXe can't catch all accessibility errors. It'll catch many of them, but <strong>you must do manual checking as well</strong>. Automated accessibility testing tools can only test about 40% of accessibility problems.
</p>
<p>
    <?php $widget_url = \SiteMaster\Core\Config::get('URL') . 'plugins/metric_axe/widget.js.php'; ?>
    This metric tests your pages against the WCAG 2.0 level AA standard. To locate and fix errors on your page, drag this link to your browser bookmarks bar:
    <a class="wdn-button" href="javascript: (function () {
        var jsCode = document.createElement('script'); 
        jsCode.setAttribute('src', '<?php echo $widget_url ?>');
        document.body.appendChild(jsCode); 
     }());" onclick="alert('Drag this to your browser Bookmarks Bar to install.'); return false;">SiteMaster Axe</a>, then click the 'SiteMaster aXe' bookmark to run accessibility tests on any page. <strong>The bookmarklet will log information to the JavaScript Console</strong>. For best results, run the bookmarklet at a mobile width.
</p>
<?php
$included_file = __DIR__.'/../../../custom-message.html';
if (file_exists($included_file)) {
    include $included_file;
}

?>
<p>
    This service is provided by <a href="https://github.com/dequelabs/axe-core">aXe core</a>
</p>
