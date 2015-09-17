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
  "database" => "database.json",
  "skip_git" => false,
  "debug" => false,
  "last" => -1,
  "force-php-stats" => false,
  "force-ruby-stats" => false,
);

// overwrite options as necessary
for ($i = 2; $i < count($argv); $i++) {
  switch ($argv[$i]) {
    case "--skip-git":
      $options['skip_git'] = true;
      continue;

    case "--output":
      $options['output'] = $argv[$i+1];
      $i++;
      continue;

    case "--database":
      $options['database'] = $argv[$i+1];
      $i++;
      continue;

    case "--debug":
      $options['debug'] = true;
      continue;

    case "--force-php-stats":
      $options['force-php-stats'] = true;
      continue;

    case "--force-ruby-stats":
      $options['force-ruby-stats'] = true;
      continue;

    case "--last":
      $options['last'] = $argv[$i+1];
      $i++;
      continue;

    default:
      throw new Exception("Unknown argument '" . $argv[$i] . "'");
  }
}

if (substr($options['output'], -1) !== "/") {
  $options['output'] .= "/";
}

$logger = new Statgit\Logger();

$statgit = new Statgit\Runner($options, $logger);

// argh this should be wrapped with a finally {} but this requires PHP 5.5+
$statgit->loadLocalDatabase();
if (!$options['skip_git']) {
  $statgit->updateGit();
  $statgit->updateFiles();
  $statgit->exportLog();
  $statgit->exportRemotes();
  $statgit->trimCommits();
  $statgit->iterateOverEachCommit();
}
$statgit->compileStats();
$statgit->generateHTML();
$statgit->saveLocalDatabase();

function something() {
  // hello, world
}
