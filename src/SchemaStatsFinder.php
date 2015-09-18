<?php

namespace Statgit;

/**
 * Uses very poor ways to try identify the # of tables, indexes
 * etc in a Rails application.
 */
class SchemaStatsFinder extends \PhpParser\NodeVisitorAbstract {

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
      "tables" => 0,

      "columns" => 0,
      "not_null" => 0,

      "indexes" => 0,
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
          } else if ($this->isSchemaFile($entry)) {
            $code = file_get_contents($dir . "/" . $entry);
            $this->parseSchema($code);
          }
        }
      }
      closedir($handle);
    }
  }

  function isSchemaFile($filename) {
    return $filename == "schema.rb";
  }

  function parseSchema($source) {
    $source = "\n" . $source . "\n";

    // try in a horrible way to remove all strings
    $source = preg_replace("/\"[^\"\n]+?\"/", "string", $source);
    $source = preg_replace("/'[^'\n]+?'/", "string", $source);

    // now do horrible regular expressions
    $identifier = "[A-Za-z0-9_]+";
    $symbol = ":[A-Za-z0-9_]+";

    if (preg_match_all("/\screate_table\s+string/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["tables"] += count($matches);
    }

    if (preg_match_all("/\st\.[a-z]+\s+string/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["columns"] += count($matches);
    }

    if (preg_match_all("/\snull:\s+false/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["not_null"] += count($matches);
    }

    if (preg_match_all("/\s:null\s+=>\s+false/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["not_null"] += count($matches);
    }

    if (preg_match_all("/\sadd_index\s+string/", $source, $matches, PREG_SET_ORDER)) {
      $this->stats["indexes"] += count($matches);
    }

  }

}
