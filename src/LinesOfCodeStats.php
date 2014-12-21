<?php

namespace Statgit;

/**
 * Get the total lines of code per date
 */
class LinesOfCodeStats extends StatisticsGenerator {

  var $database;

  function __construct($database) {
    $this->database = $database;
  }

  function compile() {
    $data = array();
    foreach ($this->database['commits'] as $commit) {
      $stats = $this->database['stats'][$commit['hash']];

      // ignores blank lines, comments
      $total_loc = 0;
      foreach ($stats as $language) {
        $total_loc += $language['code'];
      }

      $data[$commit['author_date']] = $total_loc;
    }

    return $data;
  }

}
