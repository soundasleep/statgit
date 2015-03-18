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
    $data['authors'] = $this->getDevelopers();
    $data['author_count'] = count($data['authors']);
    $data['commits'] = count($this->database['commits']);

    $firstlast = $this->getBorderCommits();

    $data['first_commit'] = $firstlast['first']['author_date'];
    $data['last_commit'] = $firstlast['last']['author_date'];
    $data['first_hash'] = $firstlast['first']['hash'];
    $data['last_hash'] = $firstlast['last']['hash'];

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

    if (isset($this->database['composer'][$data['last_hash']])) {
      $data['composer'] = $this->database['composer'][$data['last_hash']];
    }

    return $data;
  }

  function getDevelopers() {
    $developers = array();

    foreach ($this->database['commits'] as $commit) {
      if (!isset($developers[$commit['author_email']])) {
        $developers[$commit['author_email']] = array(
          'email' => $commit['author_email'],
          'name' => $commit['author_name'],
          'first_commit' => $commit['author_date'],
          'last_commit' => $commit['author_date'],
        );
      }

      $author = &$developers[$commit['author_email']];

      if (strtotime($commit['author_date']) < strtotime($author['first_commit'])) {
        $author['first_commit'] = $commit['author_date'];
      }
      if (strtotime($commit['author_date']) > strtotime($author['last_commit'])) {
        $author['last_commit'] = $commit['author_date'];
      }
    }

    return $developers;
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
