<?php

namespace Statgit;

class HtmlGenerator {

  var $database;
  var $stats;
  var $logger;
  var $output;

  function __construct($database, $stats, $logger, $output) {
    $this->database = $database;
    $this->stats = $stats;
    $this->logger = $logger;
    $this->output = $output;
  }

  function generate() {
    if (!file_exists($this->output)) {
      mkdir($this->output);
    }
    if (!is_dir($this->output)) {
      throw new \Exception("'" . $this->output . "' is not a directory");
    }

    // TODO delete all files within it?

    $this->generateFile("index");
  }

  function generateFile($template) {
    $this->logger->log("Generating '$template.html'...");

    ob_start();

    // lets use PHP to make our lives easier!
    $stats = $this->stats;
    require(__DIR__ . "/../templates/header.php");
    require(__DIR__ . "/../templates/" . $template . ".php");
    require(__DIR__ . "/../templates/footer.php");

    $contents = ob_get_contents();
    ob_end_clean();

    $file = $this->output . $template . ".html";
    file_put_contents($file, $contents);

  }

}
