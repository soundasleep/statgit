<?php

namespace Statgit;

/**
 * Uses https://github.com/nikic/PHP-Parser/ to generate some
 * basic PHP stats for all PHP files within the given root directory.
 */
class PHPStatsFinder extends \PhpParser\NodeVisitorAbstract {

  var $root;
  var $traverser;
  var $parser;

  function __construct($root, $logger) {
    $this->root = $root;
    $this->logger = $logger;
  }

  /**
   * Return an array of statistics.
   */
  function compile() {
    $this->parser = new \PhpParser\Parser(new \PhpParser\Lexer\Emulative);
    $this->traverser = new \PhpParser\NodeTraverser;

    // add your visitor
    $this->traverser->addVisitor($this);
    $this->stats = array(
      "nodes" => 0,
      "statements" => 0,
      "expressions" => 0,
      "namespaces" => 0,
      "classes" => 0,
      "interfaces" => 0,
      "class_methods" => 0,
      "includes" => 0,
      "functions" => 0,
      "inline_html" => 0,
    );

    // iterate over all files
    $this->iterateOver($this->root);

    return $this->getStats();
  }

  function iterateOver($dir) {
    if ($handle = opendir($dir)) {
      while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 1) != ".") {
          if (is_dir($dir . "/" . $entry)) {
            $this->iterateOver($dir . "/" . $entry);
          } else if ($this->isPHPFile($entry)) {
            $code = file_get_contents($dir . "/" . $entry);
            try {
              $stmts = $this->parser->parse($code);
              $this->traverser->traverse($stmts);
            } catch (\PhpParser\Error $e) {
              // in the case of parse error, ignore this file and continue
              $this->logger->log("Could not parse '" . $dir . "/" . $entry . "': " . $e->getMessage());
            }
          }
        }
      }
      closedir($handle);
    }
  }

  function isPHPFile($filename) {
    return substr(strtolower($filename), -4) === ".php";
  }

  function getStats() {
    return $this->stats;
  }

  function enterNode(\PhpParser\Node $node) {
    $this->stats['nodes']++;

    // statements
    if ($node instanceof \PhpParser\Node\Stmt) {
      $this->stats['statements']++;
    }
    if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
      $this->stats['namespaces']++;
    }
    if ($node instanceof \PhpParser\Node\Stmt\Class_) {
      $this->stats['classes']++;
    }
    if ($node instanceof \PhpParser\Node\Stmt\Interface_) {
      $this->stats['interfaces']++;
    }
    if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
      $this->stats['class_methods']++;
    }
    if ($node instanceof \PhpParser\Node\Stmt\InlineHtml) {
      $this->stats['inline_html']++;
    }
    if ($node instanceof \PhpParser\Node\Stmt\Function_) {
      $this->stats['functions']++;
    }

    // expressions
    if ($node instanceof \PhpParser\Node\Expr) {
      $this->stats['expressions']++;
    }
    if ($node instanceof \PhpParser\Node\Expr\Include_) {
      $this->stats['includes']++;
    }
  }

}
