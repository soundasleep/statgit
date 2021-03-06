<?php

namespace Statgit;

class SummaryStats extends StatisticsGenerator {

  var $database;

  function __construct($database) {
    $this->database = $database;
  }

  function compile() {
    $data = array();
    $data['generated'] = date('r');
    $data['authors'] = $this->getAuthors();
    $data['author_count'] = count($data['authors']);
    $data['commits'] = count($this->database['commits']);

    $firstlast = $this->getBorderCommits();

    $data['first_commit'] = $firstlast['first']['author_date'];
    $data['last_commit'] = $firstlast['last']['author_date'];
    $data['first_hash'] = $firstlast['first']['hash'];
    $data['last_hash'] = $firstlast['last']['hash'];
    $data['first_subject'] = $firstlast['first']['subject'];
    $data['last_subject'] = $firstlast['last']['subject'];

    $data['remote'] = $this->getRemote();
    $data['name'] = $this->getName();

    $data['total_files'] = $this->getTotalFiles($data['last_hash']);
    $data['total_loc'] = $this->getTotalLoc($data['last_hash']);
    $data['total_comments'] = $this->getTotalComments($data['last_hash']);
    $data['total_blanks'] = $this->getTotalBlanks($data['last_hash']);
    $data['total_lines'] = $data['total_loc'] + $data['total_comments'] + $data['total_blanks'];

    $top = $this->getTopLanguage($data['last_hash']);
    $data['language_top'] = $top['language'];
    $data['language_top_loc'] = $top['code'];

    $last = array("phpstats", "rubystats", "rails", "schema", "rspec",
        "cucumber", "composer", "gemfile");
    foreach ($last as $key) {
      if (isset($this->database[$key][$data['last_hash']])) {
        $data[$key] = $this->database[$key][$data['last_hash']];
      }
    }

    $data['days'] = array();
    for ($day = strtotime($data['first_commit']); $day <= strtotime($data['last_commit']); $day += (60 * 60 * 24)) {
      $data['days'][date('Y-m-d', $day)] = $this->getDaySummary($day);
    }

    return $data;
  }

  function getDaySummary($day) {
    $result = array(
      "changed" => 0,
      "added" => 0,
      "removed" => 0,
    );

    $formatted = date("Y-m-d", $day);

    foreach ($this->database['commits'] as $commit) {
      if (!isset($commit['author_date_ymd'])) {
        $commit['author_date_ymd'] = date("Y-m-d", strtotime($commit['author_date']));
      }

      if ($formatted == $commit['author_date_ymd']) {
        foreach ($this->database['diffs'][$commit['hash']] as $file => $diff) {
          $result['changed'] += $diff['added'];
          $result['changed'] += $diff['removed'];
          $result['added'] += $diff['added'];
          $result['removed'] += $diff['removed'];
        }
      }
    }

    return $result;
  }

  function getAuthors() {
    $authors = array();

    foreach ($this->database['commits'] as $commit) {
      if (!isset($authors[$commit['author_email']])) {
        $authors[$commit['author_email']] = array(
          'email' => $commit['author_email'],
          'name' => $commit['author_name'],
          'first_commit' => $commit['author_date'],
          'last_commit' => $commit['author_date'],
          'first_subject' => $commit['subject'],
          'last_subject' => $commit['suject'],
          'first_hash' => $commit['hash'],
          'last_hash' => $commit['hash'],
          'commits' => 0,
          'files' => array(),
          'changed' => 0,
          'added' => 0,
          'removed' => 0,
          'blame' => 0,
          'blame_files' => array(),
        );
      }

      $author = &$authors[$commit['author_email']];

      if (strtotime($commit['author_date']) < strtotime($author['first_commit'])) {
        $author['first_commit'] = $commit['author_date'];
        $author['first_subject'] = $commit['subject'];
        $author['first_hash'] = $commit['hash'];
      }
      if (strtotime($commit['author_date']) > strtotime($author['last_commit'])) {
        $author['last_commit'] = $commit['author_date'];
        $author['last_subject'] = $commit['subject'];
        $author['last_hash'] = $commit['hash'];
      }
    }

    foreach ($this->database['commits'] as $commit) {
      $authors[$commit['author_email']]['commits'] += 1;

      foreach ($this->database['diffs'][$commit['hash']] as $file => $diff) {
        $authors[$commit['author_email']]['changed'] += $diff['added'];
        $authors[$commit['author_email']]['changed'] += $diff['removed'];
        $authors[$commit['author_email']]['added'] += $diff['added'];
        $authors[$commit['author_email']]['removed'] += $diff['removed'];

        if (!isset($authors[$commit['author_email']]['files'][$file])) {
          $authors[$commit['author_email']]['files'][$file] = 0;
        }

        $authors[$commit['author_email']]['files'][$file] += 1;
      }
    }

    foreach (array_keys($authors) as $email) {
      arsort($authors[$email]['files']);
      $authors[$email]['files'] = array_slice($authors[$email]['files'], 0, 20, true /* preserve_keys */);
    }

    foreach ($this->database["blame"] as $file => $blame) {
      $all_lines = 0;
      foreach ($blame as $email => $lines) {
        $all_lines += $lines;
      }

      foreach ($blame as $email => $lines) {
        $authors[$email]['blame'] += $lines;
        $authors[$email]['blame_files'][$file] = ($lines / $all_lines);
      }
    }

    foreach (array_keys($authors) as $email) {
      arsort($authors[$email]['blame_files']);
      $authors[$email]['blame_files'] = array_slice($authors[$email]['blame_files'], 0, 20, true /* preserve_keys */);
    }

    return $authors;
  }

  function getBorderCommits() {
    $first = $this->database['commits'][0];
    $last = $this->database['commits'][0];

    foreach ($this->database['commits'] as $commit) {
      if (strtotime($commit['author_date']) < strtotime($first['author_date'])) {
        $first = $commit;
      }
      if (strtotime($commit['author_date']) > strtotime($last['author_date'])) {
        $last = $commit;
      }
    }

    return array('first' => $first, 'last' => $last);
  }

  function getRemote() {
    if (isset($this->database['remotes']['origin'])) {
      return $this->database['remotes']['origin'];
    } else {
      return null;
    }
  }

  function getName() {
    $remote = $this->getRemote();
    $bits = explode("/", $remote);
    $name = array();
    for ($j = 0, $i = count($bits) - 1; $i >= 0, $j < 2; $j++, $i--) {
      $name[] = $bits[$i];
    }
    return implode("/", array_reverse($name));
  }

  function getTotalFiles($hash) {
    $total = 0;
    $stats = $this->database['stats'][$hash];
    foreach ($stats as $language) {
      $total += $language['files'];
    }
    return $total;
  }

  function getTotalLoc($hash) {
    $total = 0;
    $stats = $this->database['stats'][$hash];
    foreach ($stats as $language) {
      $total += $language['code'];
    }
    return $total;
  }

  function getTotalComments($hash) {
    $total = 0;
    $stats = $this->database['stats'][$hash];
    foreach ($stats as $language) {
      $total += $language['comment'];
    }
    return $total;
  }

  function getTotalBlanks($hash) {
    $total = 0;
    $stats = $this->database['stats'][$hash];
    foreach ($stats as $language) {
      $total += $language['blank'];
    }
    return $total;
  }

  function getTopLanguage($hash) {
    $languages = array_values($this->database['stats'][$hash]);
    $top = $languages[0];
    foreach ($languages as $lang) {
      if ($lang['code'] > $top['code']) {
        $top = $lang;
      }
    }
    return $top;
  }

}
