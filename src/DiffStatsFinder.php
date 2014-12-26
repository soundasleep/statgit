<?php

namespace Statgit;

/**
 * Generate stats from patch files.
 */
class DiffStatsFinder {

  var $logger;

  function __construct($logger) {
    $this->logger = $logger;
  }

  /**
   * Return an array of statistics.
   */
  function compile($patch) {
    $stats = array();

    foreach (file($patch) as $line) {
      if (preg_match("/[0-9]+\t[0-9]+\t.+/", $line)) {
        $line = explode("\t", trim($line), 3);

        $stats[$line[2]] = array("added" => $line[0], "removed" => $line[1]);
      }
    }

    return $stats;
  }
}
