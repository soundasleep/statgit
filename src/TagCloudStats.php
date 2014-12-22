<?php

namespace Statgit;

/**
 * Get the X most used words in commit messages
 */
class TagCloudStats extends StatisticsGenerator {

  var $database;

  function __construct($database) {
    $this->database = $database;
  }

  function compile() {
    $data = array();

    $allwords = array();

    foreach ($this->database['commits'] as $commit) {
      $words = strtolower($commit['subject'] . " " . $commit['body']);

      // remove any 'git-svn-id: ' lines
      $words = preg_replace("/git-svn-id: .+/", "", $words);

      $words = trim(preg_replace("/\\s\\s+/i", " ", $words));

      $words = preg_split("/[^\\w]+/i", $words);  // TODO do we want to preg_split based on word boundaries instead?
      foreach ($words as $w) {
        if ($w) {
          if (!isset($allwords[$w])) {
            $allwords[$w] = array('word' => $w, 'count' => 0);
          }
          $allwords[$w]['count']++;
        }
      }

    }

    // sort
    usort($allwords, function($a, $b) {
      if ($a['count'] == $b['count']) {
        return 0;
      }
      return $a['count'] < $b['count'] ? 1 : -1;
    });

    // get the top N
    for ($i = 0; $i < 30 && $i < count($allwords); $i++) {
      $data[$allwords[$i]['word']] = $allwords[$i]['count'];
    }

    return $data;
  }

}
