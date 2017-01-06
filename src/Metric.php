<?php
namespace SiteMaster\Plugins\Metric_axe;

use SiteMaster\Core\Auditor\Logger\Metrics;
use SiteMaster\Core\Auditor\MetricInterface;
use SiteMaster\Core\Exception;
use SiteMaster\Core\Registry\Site;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\RuntimeException;

class Metric extends MetricInterface
{

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
        if (false === $this->headless_results || isset($this->headless_results['exception'])) {
            //mark this metric as incomplete
            throw new RuntimeException('headless results are required for the axe metric');
        }
        
        foreach ($this->headless_results as $violation) {
            $machine_name = 'axe_'.$violation['id'];
            
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