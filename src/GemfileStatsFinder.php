<?php

namespace Statgit;

/**
 * Generate some basic Gemfile and Gemfile.lock stats
 * for a single snapshot in history
 */
class GemfileStatsFinder {

  var $root;

  function __construct($root, $logger) {
    $this->root = $root;
    $this->logger = $logger;
  }

  /**
   * Return an array of statistics.
   */
  function compile() {
    $result = array(
      "sources" => 0,
      "gems" => 0,
      "groups" => 0,
      "specs" => 0,
      "dependencies" => array(),
    );

    if (file_exists($this->root . "/Gemfile")) {
      $source = "\n" . file_get_contents($this->root . "/Gemfile") . "\n";

      if (preg_match_all("/\ssource\s+.+/", $source, $matches, PREG_SET_ORDER)) {
        $result["sources"] += count($matches);
      }

      if (preg_match_all("/\sgem\s+.+/", $source, $matches, PREG_SET_ORDER)) {
        $result["gem"] += count($matches);
      }

      if (preg_match_all("/\sgroup\s+.+/", $source, $matches, PREG_SET_ORDER)) {
        $result["group"] += count($matches);
      }
    }

    if (file_exists($this->root . "/Gemfile.lock")) {
      $source = file_get_contents($this->root . "/Gemfile.lock");

      $bits = explode("\n\n", $source);
      $specs = 0;
      foreach ($bits as $bit) {
        if (substr($bit, 0, strlen("GEM\n")) == "GEM\n") {
          $in_specs = false;
          $lines = explode("\n", $bit);
          foreach ($lines as $line) {
            if (substr($line, 0, strlen("  specs:")) == "  specs:") {
              $in_specs = true;
            } elseif (substr($line, 0, strlen("    ")) == "    ") {
              if (trim($line) == substr($line, strlen("    ")) && $in_specs) {
                // a valid spec
                $specs += 1;
              }
            } else {
              $in_specs = false;
            }
          }
        }

        if (substr($bit, 0, strlen("DEPENDENCIES\n")) == "DEPENDENCIES\n") {
          $in_specs = false;
          $lines = explode("\n", $bit);
          foreach ($lines as $line) {
            if (substr($line, 0, 2) == "  ") {
              $result['dependencies'][] = $this->parseDependency(substr($line, 2));
            }
          }
        }
      }

      $result["specs"] = $specs;
    }

    return $result;
  }

  function parseDependency($s) {
    $bits = explode(" ", $s);
    return $bits[0];
  }

}
