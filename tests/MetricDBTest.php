<?php
namespace SiteMaster\Plugins\Metric_axe;

use SiteMaster\Core\Auditor\PhantomjsRunner;
use SiteMaster\Core\Auditor\Scan;
use SiteMaster\Core\Auditor\Site\Page;
use SiteMaster\Core\DBTests\AbstractMetricDBTest;
use SiteMaster\Core\Registry\Site;

class MetricDBTest extends AbstractMetricDBTest
{
    /**
     * A test to ensure that axe is working via phantomjs integration
     *
     * @test
     */
    public function testAxe()
    {
        $this->setUpDB();
        
        $phantomjs_runner = new PhantomjsRunner();
        $phantomjs_runner->deleteCompliedScript();
        
        $results = $phantomjs_runner->run(self::INTEGRATION_TESTING_URL);
        
        $this->assertArrayHasKey('axe', $results);
    }

    public function testAxeMarkPage()
    {
        $this->setUpDB();

        $site = Site::getByBaseURL(self::INTEGRATION_TESTING_URL);

        //Schedule a scan
        $site->scheduleScan();
        
        $this->runScan();

        //get the scan
        $scan = $site->getLatestScan();

        $metric = new Metric('axe');
        $metric_record = $metric->getMetricRecord();

        $this->assertEquals(Scan::STATUS_COMPLETE, $scan->status, 'the scan should be completed');

        foreach ($scan->getPages() as $page) {
            
            if ($page->uri != self::INTEGRATION_TESTING_URL) {
                continue;
            }

            /**
             * @var $page Page
             */
            $grade = $page->getMetricGrade($metric_record->id);
            $errors = $grade->getErrors();

            $this->assertGreaterThan(0, $errors->count(), 'some errors should be logged');
        }
    }

    /**
     * Get the plugin object for this metric
     *
     * @return \SiteMaster\Core\Plugin\PluginInterface
     */
    function getPlugin()
    {
        return new Plugin();
    }
}


