<?php

namespace Statgit;

/**
 * Get the X most revised files in the database
 */
class FileRevisionsStats extends StatisticsGenerator {

  var $database;

  function __construct($database) {
    $this->database = $database;
  }

  function compile() {
    $data = array();

    foreach ($this->database["diffs"] as $commit) {
      foreach ($commit as $file => $diff) {
        if (!isset($data[$file])) {
          $data[$file] = array(
            "revisions" => 0,
            "added" => 0,
            "removed" => 0,
            "added_count" => 0,
            "removed_count" => 0,
          );
        }
        $data[$file]["revisions"]++;
        $data[$file]["added"] += $diff["added"];
        $data[$file]["removed"] += $diff["removed"];
        if ($diff["added"] > 0) {
          $data[$file]["added_count"]++;
        }
        if ($diff["removed"] > 0) {
          $data[$file]["removed_count"]++;
        }
      }
    }

    // find all files which still exist
    foreach ($data as $file => $ignored) {
      $data[$file]["exists"] = isset($this->database["files"][$file]);
    }

    return $data;
  }

}
