<?php
namespace SiteMaster\Plugins\Metric_axe;

use Monolog\Logger;
use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Config;
use SiteMaster\Core\Exception;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\RuntimeException;
use SiteMaster\Core\Util;

class Metric extends MetricInterface
{
    /**
     * @param string $plugin_name
     * @param array $options
     */
    public function __construct($plugin_name, array $options = array())
    {
        $options = array_replace_recursive([
            'execute_as_user' => false,
            'sandbox' => true,
            'dark_mode' => false
        ], $options);

        parent::__construct($plugin_name, $options);
    }

    /**
     * Get the human readable name of this metric
     *
     * @return string The human readable name of the metric
     */
    public function getName()
    {
        return 'aXe Accessibility Metric';
    }

    /**
     * Get the Machine name of this metric
     *
     * This is what defines this metric in the database
     *
     * @return string The unique string name of this metric
     */
    public function getMachineName()
    {
        return 'axe';
    }

    /**
     * Determine if this metric should be graded as pass-fail
     *
     * @return bool True if pass-fail, False if normally graded
     */
    public function isPassFail()
    {
        if (isset($this->options['pass_fail']) && $this->options['pass_fail'] == true) {
            //Simulate a pass/fail metric grade
            return true;
        }
        
        return false;
    }

    /**
     * Scan a given URI and apply all marks to it.
     *
     * All that this
     *
     * @param string $uri The uri to scan
     * @param \DOMXPath $xpath The xpath of the uri
     * @param int $depth The current depth of the scan
     * @param \SiteMaster\Core\Auditor\Site\Page $page The current page to scan
     * @param \SiteMaster\Core\Auditor\Logger\Metrics $context The logger class which calls this method, you can access the spider, page, and scan from this
     * @throws \Exception
     * @return bool True if there was a successful scan, false if not.  If false, the metric will be graded as incomplete
     */
    public function scan($uri, \DOMXPath $xpath, $depth, Page $page, Metrics $context)
    {
        $results = $this->run($uri);
        if (!is_array($results)) {
            throw new RuntimeException('headless results are required for the axe metric');
        }

        foreach ($results as $violation) {
            $machine_name = $this->getMachineName().'_'.$violation['id'];

            $mark = $this->getMark($machine_name, $violation['help'], 1, '', $violation['description']);

            foreach ($violation['nodes'] as $node) {
                $page->addMark($mark, array(
                    'context'   => htmlentities($node['html']),
                    'help_text' => $this->getHelpTextMd($node),
                ));
            }
        }

        return true;
    }

    public function run($url) {
        try {
            $command = '';

            if ($this->options['execute_as_user']) {
                //This option allows executing as a specific user, which can sandbox the script.
                $command .= 'sudo -u ' . escapeshellarg($this->options['execute_as_user']) . ' ';
            }

            $command .= 'timeout ' . escapeshellarg(Config::get('HEADLESS_TIMEOUT')) //Prevent excessively long runs
                . ' ' . Config::get('PATH_NODE')
                . ' ' . __DIR__.'/../check.js'
                . ' --ua ' . escapeshellarg(Config::get('USER_AGENT'));

            if (isset($this->options['sandbox']) && $this->options['sandbox'] === false) {
                $command .= ' --sandbox=false';
            }

            if (isset($this->options['dark_mode']) && $this->options['dark_mode'] === true) {
                $command .= ' --dark_mode=true';
            }

            $command .= ' ' . escapeshellarg($url);

            $result = trim(shell_exec($command));

            if (!$result) {
                return false;
            }

            return json_decode($result, true);

        } catch (Exception $e) {
            return false;
        }
    }

    protected function getHelpTextMd($node) {
        $index = ['all', 'any'];

        $node['all'] = array_merge_recursive($node['all'], $node['none']);
        
        $md = '';
        foreach ($index as $key) {
            if (empty($node[$key])) {
                continue;
            }
            $md .= 'Fix ' . $key . ' of' . PHP_EOL . PHP_EOL;
            
            foreach ($node[$key] as $item) {
                $md .= '  * ' . $item['message'] . PHP_EOL;
            }
            
            $md .= PHP_EOL;
        }

        return $md;
    }

    /**
     * Set the options array for this metric.
     * 
     * This is for testing purposes
     * 
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}