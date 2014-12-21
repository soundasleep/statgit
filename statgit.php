<?php

/**
 * This basically tries to do the same thing as statsvn.
 * Execution order:
 * 1. update Git to latest master
 * 2. export complete Git log to a format
 * 3. check out each commit that has not yet been checked out
 * 4. execute stats on each commit
 * 5. compile these stats together
 * 6. generate HTML
 */

require(__DIR__ . "/vendor/autoload.php");

if ($argc < 2) {
  throw new Exception("Expected root parameter");
}

// default options
$options = array(
  "root" => $argv[1],
  "output" => "./statgit/",
  "database" => $argv[1] . "/.statgit.json",
  "skip_git" => true,
);

// overwrite
if (isset($argv[2])) {
  $options['output'] = $argv[2];
}

$logger = new Statgit\Logger();

$statgit = new Statgit\Runner($options, $logger);

// argh this should be wrapped with a finally {} but this requires PHP 5.5+
$statgit->loadLocalDatabase();
if (!$options['skip_git']) {
  $statgit->updateGit();
  $statgit->exportLog();
  $statgit->exportRemotes();
  $statgit->iterateOverEachCommit();
}
$statgit->compileStats();
$statgit->generateHTML();
$statgit->saveLocalDatabase();
