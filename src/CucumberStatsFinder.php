<?php

namespace Statgit;

/**
 * Uses very poor ways to try identify the # of features,
 * scenarios etc in a Rails application.
 */
class CucumberStatsFinder extends \PhpParser\NodeVisitorAbstract {

  var $root;

  function __construct($root, $logger, $options) {
    $this->root = $root;
    $this->logger = $logger;
    $this->options = $options;
  }

  /**
   * Return an array of statistics.
   */
  function compile() {
    $this->stats = array(
      "features" => 0,
      "scenarios" => 0,
      "givens" => 0,
      "whens" => 0,
      "ands" => 0,
      "thens" => 0,
      "annotations" => 0,
    );

    // iterate over all files
    $this->iterateOver($this->root);

    return $this->stats;
  }

  function iterateOver($dir) {
    if ($handle = opendir($dir)) {
      while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 1) != ".") {
          if (is_dir($dir . "/" . $entry)) {
            $this->iterateOver($dir . "/" . $entry);
          } else if ($this->isFeatureFile($entry)) {
            $code = file_get_contents($dir . "/" . $entry);
            $this->parseFeature($code);
          }
        }
      }
      closedir($handle);
    }
  }

  function isFeatureFile($filename) {
    return
      substr(strtolower($filename), -strlen(".feature")) === ".feature";
  }

  function parseFeature($source) {
    $source = "\n" . $source . "\n";

    // try in a horrible way to remove all strings
    $source = preg_replace("/\"[^\"]+?\"/", "string", $source);
    $source = preg_replace("/'[^'']+?'/", "string", $source);

    // now do horrible regular expressions
    $identifier = "[A-Za-z0-9_]+";
    $symbol = ":[A-Za-z0-9_]+";

    if (preg_match_all("/\sFeature:\s+/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["features"] += count($matches);
    }

    if (preg_match_all("/\sScenario:\s+/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["scenarios"] += count($matches);
    }

    if (preg_match_all("/\sGiven\s+/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["givens"] += count($matches);
    }

    if (preg_match_all("/\sWhen\s+/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["whens"] += count($matches);
    }

    if (preg_match_all("/\sThen\s+/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["thens"] += count($matches);
    }

    if (preg_match_all("/\sAnd\s+/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["ands"] += count($matches);
    }

    if (preg_match_all("/\s@$identifier/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["annotations"] += count($matches);
    }

  }

}
