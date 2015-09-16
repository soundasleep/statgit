<?php

namespace Statgit;

/**
 * Get the X most used words in commit messages.
 * Also X most used words per author.
 */
class TagCloudStats extends StatisticsGenerator {

  var $database;

  function __construct($database) {
    $this->database = $database;
  }

  function compile() {
    $result = array();
    $result['all'] = $this->compileForAuthor(false);

    foreach ($this->allUniqueAuthors() as $email) {
      $result[$email] = $this->compileForAuthor($email);
    }

    return $result;
  }

  function compileForAuthor($author = false) {
    $data = array();

    $allwords = array();

    foreach ($this->database['commits'] as $commit) {
      if ($commit['author_email'] == $author || $author === false) {
        $words = strtolower($commit['subject'] . " " . $commit['body']);

        // remove any 'git-svn-id: ' lines
        $words = preg_replace("/git-svn-id: .+/", "", $words);

        $words = trim(preg_replace("/\\s\\s+/i", " ", $words));

        $words = preg_split("/[^\\w]+/i", $words);  // TODO do we want to preg_split based on word boundaries instead?
        foreach ($words as $w) {
          if (strlen($w) >= 3 /* ignore connecting words */) {
            if (!isset($allwords[$w])) {
              $allwords[$w] = array('word' => $w, 'count' => 0);
            }
            $allwords[$w]['count']++;
          }
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
    for ($i = 0; $i < 50 && $i < count($allwords); $i++) {
      $data[$allwords[$i]['word']] = $allwords[$i]['count'];
    }

    return $data;
  }

  function allUniqueAuthors() {
    $result = array();
    foreach ($this->database['commits'] as $commit) {
      if (!isset($result[$commit['author_email']])) {
        $result[$commit['author_email']] = true;
      }
    }

    return array_keys($result);
  }

}
