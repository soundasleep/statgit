<?php

namespace Statgit;

class Runner {

  function __construct($options, Logger $logger) {
    $this->options = $options;
    $this->logger = $logger;
  }

  function passthru($cmd) {
    $return = -1;
    $this->logger->log(">>> " . $cmd, $return);
    passthru($cmd, $return);
    if ($return !== 0) {
      throw new \Exception("passthru '$cmd' returned '$return'");
    }
  }

  function loadLocalDatabase() {
    if (file_exists($this->options["database"])) {
      $this->logger->log("Loading local database from '" . $this->options['database'] . "'...");
      $this->database = json_decode(file_get_contents($this->options["database"]), true /* assoc */);
    } else {
      $this->database = array();
    }
  }

  function saveLocalDatabase() {
    $this->logger->log("Saving local database to '" . $this->options['database'] . "'...");
    $args = 0;
    if (defined('JSON_PRETTY_PRINT')) {
      $args = JSON_PRETTY_PRINT;
    }
    file_put_contents($this->options['database'], json_encode($this->database, $args));
  }

  function updateGit() {
    $this->logger->log("Pulling latest from remote...");
    $this->passthru("cd " . escapeshellarg($this->options["root"]) . " && git pull origin master");
  }

  function exportLog() {
    $this->logger->log("Exporting complete log to JSON...");

    $specialCharacter = "(separator)";
    $endCharacter = "(end log entry)";

    $formatBits = array(
      "hash" => "%H",
      "hash_short" => "%h",
      "tree_hash" => "%T",
      "parent_hashes" => "%P",
      "author_name" => "%an",
      "author_email" => "%ae",
      "author_date" => "%ai",
      "committer_name" => "%cn",
      "committer_email" => "%ce",
      "committer_date" => "%ci",
      "subject" => "%s",
      "body" => "%b",
      "commit_notes" => "%N",
    );
    $format = implode($specialCharacter, array_values($formatBits)) . $endCharacter;
    $temp = $this->getTempFile();

    $this->passthru("cd " . escapeshellarg($this->options["root"]) . " && git log --reverse --format=\"" . $format . "\" > " . escapeshellarg($temp));
    $this->logger->log("Reading '$temp'...");

    $contents = file_get_contents($temp);
    $bits = explode($endCharacter, $contents);
    $result = array();
    foreach ($bits as $line) {
      if (!trim($line)) {
        continue;
      }

      $linebits = explode($specialCharacter, trim($line));
      $row = array();
      foreach (array_keys($formatBits) as $i => $key) {
        $row[$key] = $linebits[$i];
      }
      $result[] = $row;
    }

    $this->logger->log("Found " . number_format(count($result)) . " commits");

    // write to database
    $this->database["commits"] = $result;

    // delete temp file
    unlink($temp);
  }

  function getTempFile() {
    return tempnam(sys_get_temp_dir(), "statgit");
  }

  function exportRemotes() {
    $this->logger->log("Exporting remotes...");

    $temp = $this->getTempFile();

    $this->passthru("cd " . escapeshellarg($this->options["root"]) . " && git remote -v > " . escapeshellarg($temp));
    $this->logger->log("Reading '$temp'...");

    if (!isset($this->database['remotes'])) {
      $this->database['remotes'] = array();
    }

    $remotes = file($temp);
    foreach ($remotes as $line) {
      $line = trim(preg_replace("/\\s\\s+/", " ", str_replace("\t", " ", $line)));
      if (substr($line, -strlen("(fetch)")) == "(fetch)") {
        $remote = explode(" ", $line, 2);
        $remote[1] = trim(str_replace(" (fetch)", "", $remote[1]));
        $this->database['remotes'][$remote[0]] = $remote[1];
      }
    }

    // delete temp file
    unlink($temp);
  }

  function iterateOverEachCommit() {
    if (!isset($this->database["stats"])) {
      $this->database["stats"] = array();
    }
    foreach ($this->database["commits"] as $commit) {
      if (!isset($this->database['stats'][$commit['hash']])) {
        $this->logger->log("Checking out commit '" . $commit['hash'] . "'...");
        $this->passthru("cd " . escapeshellarg($this->options["root"]) . " && git checkout " . escapeshellarg($commit['hash']));

        // now lets do some basic stats with cloc
        $temp = $this->getTempFile();
        $this->passthru("cloc --csv --quiet " . escapeshellarg($this->options["root"]) . " > " . escapeshellarg($temp));

        $csv = file($temp);
        $stats = array();

        $i = 0;
        foreach ($csv as $line) {
          if (!trim($line)) {
            continue;
          }

          if ($i++ == 0) {
            $rows = str_getcsv(trim($line));

            $languageid = array_search("language", $rows);
          } else {
            $columns = str_getcsv(trim($line));
            $statrow = array();
            $language = null;
            foreach ($rows as $j => $value) {
              if (isset($columns[$j])) {
                $statrow[$value] = $columns[$j];
              }
              if ($j == $languageid) {
                $language = $columns[$j];
              }
            }
            $stats[$language] = $statrow;
          }
        }

        // store
        $this->database['stats'][$commit['hash']] = $stats;
        unlink($temp);

        // store database
        $this->saveLocalDatabase();

      }
    }

  }

  // temporary, only stored for this run
  var $stats = array();

  function compileStats() {
    $stats['summary'] = new SummaryStats($this->database);
    $stats['loc'] = new LinesOfCodeStats($this->database);
    $stats['tagcloud'] = new TagCloudStats($this->database);

    foreach ($stats as $key => $summary) {
      $this->logger->log("Compiling '$key' statistics...");
      $this->stats[$key] = $summary->compile();
    }
  }

  function generateHTML() {
    $this->logger->log("Generating HTML...");

    $generator = new HtmlGenerator($this->database, $this->stats, $this->logger, $this->options['output']);
    $generator->generate();

    if ($this->options['debug']) {
      print_r($this->stats);
    }

  }

}
