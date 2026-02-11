<?php

namespace Timber\Tests\Image;

use Timber\Tests\TimberIntegrationTestCase;
use Timber\Tests\TimberMultisiteTest;
use Timber\URLHelper;

class ImageMultisiteTest extends TimberIntegrationTestCase
{
    public function tear_down()
    {
        if (\is_multisite()) {
            \switch_to_blog(1);
        }
        TimberMultisiteTest::clear();
        parent::tear_down();
    }

    public function testSubDomainImageLocation()
    {
        if (!\is_multisite()) {
            $this->markTestSkipped('Test is only for Multisite');
            return;
        }
        $blog_id = TimberMultisiteTest::createSubDomainSite();
        $this->assertGreaterThan(1, $blog_id);
        $pretend_image = 'http://example.org/wp-content/2015/08/fake-pic.jpg';
        $is_external = URLHelper::is_external_content($pretend_image);
        $this->assertFalse($is_external);
    }

    public function testSubDirectoryImageLocation()
    {
        if (!\is_multisite()) {
            $this->markTestSkipped('Test is only for Multisite');
            return;
        }
        $blog_id = TimberMultisiteTest::createSubDirectorySite();
        $this->assertGreaterThan(1, $blog_id);
        $blog_details = \get_blog_details($blog_id);
        $pretend_image = 'http://example.org/wp-content/2015/08/fake-pic.jpg';
        $is_external = URLHelper::is_external_content($pretend_image);
        $this->assertFalse($is_external);
    }
}
