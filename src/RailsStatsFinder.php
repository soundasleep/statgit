<?php

namespace Statgit;

/**
 * Uses very poor ways to try identify the # of controllers,
 * services etc in a Rails application.
 */
class RailsStatsFinder extends \PhpParser\NodeVisitorAbstract {

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
      "controllers" => 0,
      "helpers" => 0,
      "mailers" => 0,
      "models" => 0,
      "presenters" => 0,
      "services" => 0,
      "serializers" => 0,
      "decorators" => 0,
      "concepts" => 0,
      "validators" => 0,
      "views" => 0,
      "partials" => 0,
    );

    // iterate over all files
    $this->iterateOver($this->root);

    // find the routes
    $this->stats["routes"] = 0;
    $this->findRoutes();

    return $this->stats;
  }

  function iterateOver($dir) {
    if ($handle = opendir($dir)) {
      while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 1) != ".") {
          if (is_dir($dir . "/" . $entry)) {
            $this->iterateOver($dir . "/" . $entry);
          } else if ($this->isRailsFile($entry)) {
            $this->parseFilename($dir . "/" . $entry);
          }
        }
      }
      closedir($handle);
    }
  }

  function isRailsFile($filename) {
    return
      substr(strtolower($filename), -3) === ".rb" ||
      substr(strtolower($filename), -4) === ".erb" ||
      substr(strtolower($filename), -5) === ".haml";
  }

  function parseFilename($filename) {
    foreach ($this->stats as $key => $ignored) {
      if ($key == "partials" || $key == "views") {
        continue;
      }

      if (preg_match("#app/$key/#", $filename)) {
        $this->stats[$key] += 1;
      }
    }

    if (preg_match("#app/views/#", $filename)) {
      if (strpos($filename, "/_") !== false) {
        $this->stats["partials"] += 1;
      } else {
        $this->stats["views"] += 1;
      }
    }
  }

  function getTempFile() {
    return tempnam(sys_get_temp_dir(), "statgit_rails");
  }

  function findRoutes() {
    $temp = $this->getTempFile();

    $this->passthru("cd " . escapeshellarg($this->root) . " && " . $this->options["rake-args"] . " bin/rake routes > " . escapeshellarg($temp));
    $this->logger->log("Reading '$temp'...");

    if (file_exists($temp) && $routes = file($temp)) {
      $this->stats["routes"] = count($routes) - 1;
    }
  }

  function passthru($cmd) {
    $return = -1;
    $this->logger->log(">>> " . $cmd, $return);
    passthru($cmd, $return);
    if ($return !== 0) {
      // throw new \Exception("passthru '$cmd' returned '$return'");
    }
  }

}
