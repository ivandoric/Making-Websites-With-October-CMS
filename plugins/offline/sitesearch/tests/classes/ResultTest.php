<?php namespace OFFLINE\SnipcartShop\Tests\Models;

use OFFLINE\SiteSearch\Classes\Result;
use PluginTestCase;

class ResultTest extends PluginTestCase
{
    public function test_excerpt_edge_case()
    {
        $result        = new Result('this is a long search query', 1);
        $result->title = 'Title';
        $result->text  = str_repeat('this is a long search query ', 20);
        $result->url   = 'http://url';

        // Since not all the marked tags fit into the excerpt, the last
        // occurence should not be wrapped in <mark> tags to prevent broken html
        $expected = str_repeat('<mark>this is a long search query</mark> ', 8) . 'this is a long search quer...';
        $this->assertEquals($expected, $result->excerpt);
    }
}