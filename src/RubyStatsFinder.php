<?php

namespace Statgit;

/**
 * Uses horrible regular expressions to generate some
 * basic Ruby stats for all Ruby files within the given root directory.
 * (I haven't found a good Ruby AST in PHP library yet.)
 * Not very good at dealing with strings.
 */
class RubyStatsFinder extends \PhpParser\NodeVisitorAbstract {

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
      "classes" => 0,
      "defs" => 0,
      "modules" => 0,
      "includes" => 0,
      "requires" => 0,
      "helpers" => 0,
      "filters" => 0,
      "comments" => 0,
      "belongs_tos" => 0,
      "has_ones" => 0,
      "has_manys" => 0,
      "scopes" => 0,
      "validates" => 0,
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
    return substr(strtolower($filename), -3) === ".rb";
  }

  function parseRuby($source) {
    $source = "\n" . $source . "\n";

    // try in a horrible way to remove all strings
    $source = preg_replace("/\"[^\"\n]+?\"/", "string", $source);
    $source = preg_replace("/'[^'\n]+?'/", "string", $source);

    // now do horrible regular expressions
    $identifier = "[A-Za-z0-9_]+";
    $symbol = ":[A-Za-z0-9_]+";

    if (preg_match_all("/\sclass\s+$identifier/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["classes"] += count($matches);
    }

    if (preg_match_all("/\sdef\s+$identifier/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["defs"] += count($matches);
    }

    if (preg_match_all("/\smodule\s+$identifier/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["modules"] += count($matches);
    }

    if (preg_match_all("/\sinclude\s+$identifier/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["includes"] += count($matches);
    }

    if (preg_match_all("/\srequire\s+string/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["requires"] += count($matches);
    }

    if (preg_match_all("/\shelper\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["helpers"] += count($matches);
    }

    if (preg_match_all("/\s(skip_before_filter|before_filter|after_filter)helper\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["filters"] += count($matches);
    }

    if (preg_match_all("/\svalidates(|_[a-z_]+)\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["validates"] += count($matches);
    }

    if (preg_match_all("/\sbelongs_to\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["belongs_tos"] += count($matches);
    }

    if (preg_match_all("/\shas_one\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["has_ones"] += count($matches);
    }

    if (preg_match_all("/\shas_many\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["has_manys"] += count($matches);
    }

    if (preg_match_all("/\sscope\s+($identifier|$symbol)/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["scopes"] += count($matches);
    }

    if (preg_match_all("/\s#.+?/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["comments"] += count($matches);
    }

  }

}
