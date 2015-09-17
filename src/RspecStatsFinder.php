<?php

namespace Statgit;

/**
 * Uses horrible regular expressions to generate some
 * basic Rspec stats for all Rspec files within the given root directory.
 * (I haven't found a good Rspec AST in PHP library yet.)
 * Not very good at dealing with strings.
 */
class RspecStatsFinder extends \PhpParser\NodeVisitorAbstract {

  var $root;

  function __construct($root, $logger) {
    $this->root = $root;
    $this->logger = $logger;
  }

  /**
   * Return an array of statistics.
   */
  function compile() {
    $this->stats = array(
      "describes" => 0,
      "contexts" => 0,
      "its" => 0,
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
          } else if ($this->isRubyFile($entry)) {
            $code = file_get_contents($dir . "/" . $entry);
            $this->parseRuby($code);
          }
        }
      }
      closedir($handle);
    }
  }

  function isRubyFile($filename) {
    return substr(strtolower($filename), -strlen("_spec.rb")) === "_spec.rb";
  }

  function parseRuby($source) {
    $source = "\n" . $source . "\n";

    // try in a horrible way to remove all strings
    $source = preg_replace("/\"[^\"]+?\"/", "string", $source);
    $source = preg_replace("/'[^'']+?'/", "string", $source);

    // now do horrible regular expressions
    $identifier = "[A-Za-z0-9_]+";
    $symbol = ":[A-Za-z0-9_]+";

    if (preg_match_all("/\sRSpec.describe\s+($identifier|string)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["describes"] += count($matches);
    }

    if (preg_match_all("/\scontext\s+string/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["contexts"] += count($matches);
    }

    if (preg_match_all("/\sit\s+(string|{)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["its"] += count($matches);
    }
  }

}
