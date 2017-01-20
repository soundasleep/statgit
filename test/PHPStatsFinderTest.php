<?php

/**
 * Test that the PHP stats finder works.
 */
class PHPStatsFinderTest extends PHPUnit_Framework_TestCase {

  function testParser() {
    $root = __DIR__ . "/php_stats";

    $parser = new Statgit\PHPStatsFinder($root, $logger);
    $stats = $parser->compile();
    $this->assertEquals($stats, array(
      'nodes' => 2,
      'statements' => 1,
      'expressions' => 1,
      'namespaces' => 0,
      'classes' => 0,
      'interfaces' => 0,
      'class_methods' => 0,
      'includes' => 0,
      'functions' => 0,
      'inline_html' => 0,
    ));
  }

}
