<?php

class TestTimberArchives extends Timber_UnitTestCase
{
    public function testArchivesLimit()
    {
        $dates = ['2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08', '2013-03-03'];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'monthly',
            'show_year' => false,
            'limit' => 3,
        ]);
        $this->assertSame(3, count($archives->items));
        $this->assertSame(2, $archives->items[1]['post_count']);
    }

    public function testArchiveMonthly()
    {
        $dates = ['2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08'];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'monthly',
            'show_year' => false,
        ]);
        $this->assertEquals('December', $archives->items[0]['name']);
        $this->assertSame(3, count($archives->items));
        $this->assertSame(2, $archives->items[1]['post_count']);
        $archives = new Timber\Archives([
            'type' => 'monthly',
            'show_year' => true,
        ]);
        $this->assertEquals('December 2013', $archives->items[0]['name']);
    }

    public function testArchiveYearly()
    {
        $dates = ['2011-11-08', '2011-12-08', '2013-11-09', '2014-07-04'];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'yearly',
            'show_year' => false,
        ]);
        $this->assertSame(3, count($archives->items));
        $this->assertSame(2, $archives->items[2]['post_count']);
    }

    public function testArchiveDaily()
    {
        $dates = ['2013-11-08', '2013-12-08', '2013-11-09', '2013-11-09', '2013-06-08', '2014-01-08',
        ];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'daily',
        ]);
        $this->assertSame(5, count($archives->items));
        $this->assertSame(2, $archives->items[2]['post_count']);
    }

    public function testArchiveYearlyMonthly()
    {
        $dates = ['2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08', '2014-01-08',
        ];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'monthly-nested',
        ]);
        $this->assertSame(2, count($archives->items));
        $this->assertSame(4, $archives->items[1]['post_count']);
        $this->assertSame(2, $archives->items[1]['children'][1]['post_count']);
        $archives = new Timber\Archives([
            'type' => 'yearlymonthly',
        ]);
        $this->assertSame(2, count($archives->items));
        $this->assertSame(4, $archives->items[1]['post_count']);
        $this->assertSame(2, $archives->items[1]['children'][1]['post_count']);
    }

    public function testArchiveWeekly()
    {
        $dates = ['2015-03-02', '2015-03-09', '2015-03-16', '2015-03-21', '2015-03-22',
        ];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'weekly',
        ]);
        $this->assertSame(3, count($archives->items));
        $this->assertSame(3, $archives->items[0]['post_count']);
    }

    public function testArchiveAlpha()
    {
        $posts = [
            [
                'date' => '2015-03-02',
                'post_title' => 'Jared loves Lauren',
            ],
            [
                'date' => '2015-03-02',
                'post_title' => 'Another fantastic post',
            ],
            [
                'date' => '2015-03-02',
                'post_title' => 'Foobar',
            ],
            [
                'date' => '2015-03-02',
                'post_title' => 'Quack Quack',
            ],
        ];
        foreach ($posts as $post) {
            $this->factory->post->create([
                'post_date' => $post['date'] . ' 19:46:41',
                'post_title' => $post['post_title'],
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives([
            'type' => 'alpha',
        ]);
        $this->assertSame(4, count($archives->items));
        $this->assertEquals('Quack Quack', $archives->items[3]['name']);
    }

    public function testArchivesWithArgs()
    {
        register_post_type('book');
        $dates = ['2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08', '2014-01-08',
        ];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
            ]);
        }
        $dates = ['2014-11-08', '2014-12-08', '2014-11-09', '2014-06-08', '2015-01-08', '2015-02-14',
        ];
        foreach ($dates as $date) {
            $this->factory->post->create([
                'post_date' => $date . ' 19:46:41',
                'post_type' => 'book',
            ]);
        }
        $this->go_to('/');
        $archives = new Timber\Archives();

        $this->assertSame(2, count($archives->items));
        $archives = new Timber\Archives([
            'post_type' => 'book',
            'type' => 'monthly',
        ]);
        $this->assertSame(5, count($archives->items));
    }
}
