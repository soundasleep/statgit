<?php

namespace Statgit;

class Runner {

  function __construct($options, Logger $logger) {
    $this->options = $options;
    $this->logger = $logger;

    date_default_timezone_set($this->options["timezone"]);
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

  // get a list of all files that exist
  function updateFiles() {
    $this->logger->log("Finding files...");
    $this->database["files"] = $this->findAllFiles($this->options["root"], $this->options["root"]);
    $this->logger->log("Found " . number_format(count($this->database["files"])) . " files");
  }

  function findAllFiles($dir, $root) {
    $result = array();
    if ($handle = opendir($dir)) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != ".git") {
          if (is_dir($dir . "/" . $entry)) {
            $result = array_merge($result, $this->findAllFiles($dir . "/" . $entry, $root));
          } else {
            $result[str_replace($root . "/", "", $dir . "/" . $entry)] = filesize($dir . "/" . $entry);
          }
        }
      }
      closedir($handle);
    }
    return $result;
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

  function checkOut($hash) {
    $this->logger->log("Checking out commit '" . $hash . "'...");
    $this->passthru("cd " . escapeshellarg($this->options["root"]) . " && git checkout " . escapeshellarg($hash));
  }

  function trimCommits() {
    if ($this->options['last'] > 0) {
      $this->logger->log("Selecting last " . $this->options['last'] . " commits...");
      $this->database["commits"] = array_slice($this->database["commits"], -$this->options["last"]);
    }
  }

  function iterateOverEachCommit() {
    if (!isset($this->database["stats"])) {
      $this->database["stats"] = array();
    }
    if (!isset($this->database["phpstats"])) {
      $this->database["phpstats"] = array();
    }
    if (!isset($this->database["diffs"])) {
      $this->database["diffs"] = array();
    }

    foreach ($this->database["commits"] as $commit) {
      if (!isset($this->database['stats'][$commit['hash']])) {
        $this->checkOut($commit['hash']);

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

      // if there were any PHP files, calculate PHP statistics
      if (isset($this->database['stats'][$commit['hash']]['PHP'])) {
        if (!isset($this->database['phpstats'][$commit['hash']]) || $this->options["force-php-stats"]) {
          $this->checkOut($commit['hash']);

          // find PHP stats
          $phpstats = new PHPStatsFinder($this->options["root"], $this->logger);
          $this->logger->log("Generating PHP statistics...");
          $this->database['phpstats'][$commit['hash']] = $phpstats->compile();

          $this->logger->log("Found " . number_format($this->database['phpstats'][$commit['hash']]['statements']) . " statements");

          // store database
          $this->saveLocalDatabase();

        }
      }

      // if there were any Ruby files, calculate Ruby statistics
      if (isset($this->database['stats'][$commit['hash']]['Ruby'])) {
        if (!isset($this->database['rubystats'][$commit['hash']]) || $this->options["force-ruby-stats"]) {
          $this->checkOut($commit['hash']);

          // find Ruby stats
          $rubystats = new RubyStatsFinder($this->options["root"], $this->logger);
          $this->logger->log("Generating Ruby statistics...");
          $this->database['rubystats'][$commit['hash']] = $rubystats->compile();

          $this->logger->log("Found " . number_format($this->database['rubystats'][$commit['hash']]['classes']) . " classes");

          // store database
          $this->saveLocalDatabase();

        }
      }

      // calculate diff statistics
      if (!isset($this->database['diffs'][$commit['hash']]) || $this->options["force-diff-stats"]) {
        $this->checkOut($commit['hash']);

        // generate a patch file
        $temp = $this->getTempFile();
        $this->passthru("cd " . escapeshellarg($this->options["root"]) . " && git show --numstat > " . escapeshellarg($temp));

        // find diff stats
        $diffstats = new DiffStatsFinder($this->logger);
        $stats = $diffstats->compile($temp);

        $this->database['diffs'][$commit['hash']] = $stats;
        $this->logger->log(number_format(count($stats)) . " files changed");

        // store database
        $this->saveLocalDatabase();

      }

      // calculate composer statistics
      if (!isset($this->database['composer'][$commit['hash']]) || $this->options["force-composer-stats"]) {
        $this->checkOut($commit['hash']);

        // find composer stats
        $composerstats = new ComposerStatsFinder($this->options["root"], $this->logger);
        $this->logger->log("Generating Composer statistics...");
        $this->database['composer'][$commit['hash']] = $composerstats->compile();

        // store database
        $this->saveLocalDatabase();
      }

      // calculate gemfile statistics
      if (!isset($this->database['gemfile'][$commit['hash']]) || $this->options["force-gemfile-stats"]) {
        $this->checkOut($commit['hash']);

        // find gemfile stats
        $gemfilestats = new GemfileStatsFinder($this->options["root"], $this->logger);
        $this->logger->log("Generating Gemfile statistics...");
        $this->database['gemfile'][$commit['hash']] = $gemfilestats->compile();

        $this->logger->log("Found " . number_format($this->database['gemfile'][$commit['hash']]['specs']) . " specs");

        // store database
        $this->saveLocalDatabase();
      }

    }

  }

  function updateRubygems() {
    if (!isset($this->database["rubygems"])) {
      $this->database["rubygems"] = array();
    }

    if (isset($this->stats['summary']['gemfile'])) {
      $this->logger->log("Updating information from rubygems...");

      foreach ($this->stats['summary']['gemfile']['dependencies'] as $dependency) {
        if (!isset($this->database["rubygems"][$dependency])) {
          $rubygem = $this->loadRubygem($dependency);
          if ($rubygem) {
            $this->database["rubygems"][$dependency] = $rubygem;
            $this->saveLocalDatabase();
          }
        }
      }

      $this->logger->log("Found " . number_format(count($this->database["rubygems"])) . " rubygems");
    }
  }

  function loadRubygem($rubygem) {
    if (!preg_match("/^[A-Za-z0-9\.\-_]+$/", $rubygem)) {
      // invalid gem name: https://github.com/rubygems/rubygems.org/blob/master/lib/patterns.rb
      return false;
    }

    $url = "https://rubygems.org/api/v1/gems/$rubygem.json";
    $this->logger->log("Requesting '$url'...");
    $json = json_decode(file_get_contents($url), true /* assoc */);
    if ($json) {
      return $json;
    } else {
      $this->logger->log("Could not load '$url'");
      return false;
    }
  }

  // temporary, only stored for this run
  var $stats = array();

  function compileStats() {
    $stats['summary'] = new SummaryStats($this->database);
    $stats['loc'] = new LinesOfCodeStats($this->database);
    $stats['tagcloud'] = new TagCloudStats($this->database);
    $stats['file_revisions'] = new FileRevisionsStats($this->database);

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
