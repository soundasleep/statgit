<?php

namespace Statgit;

/**
 * Generate stats from patch files.
 */
class DiffStatsFinder {

  var $logger;
  var $without;

  function __construct($logger, $without) {
    $this->logger = $logger;
    $this->without = $without;
  }

  /**
   * Return an array of statistics.
   */
  function compile($patch) {
    $stats = array();

    foreach (file($patch) as $line) {
      if (preg_match("/[0-9]+\t[0-9]+\t.+/", $line)) {
        $line = explode("\t", trim($line), 3);

        // ignore anything in without
        foreach ($this->without as $w) {
          if (substr($line[2], 0, strlen($w)) == $w) {
            continue 2;
          }
        }

        $stats[$line[2]] = array("added" => $line[0], "removed" => $line[1]);
      }
    }

    return $stats;
  }
}
