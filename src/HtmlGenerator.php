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

    // copy over CSS
    copy(__DIR__ . "/../templates/default.css", $this->output . "default.css");
  }

  function generateFile($template) {
    $this->logger->log("Generating '$template.html'...");

    switch ($template) {
      case "index":
        $title = "Statgit - " . $this->stats['summary']['name'];
        break;
      default:
        $title = "Statgit";
    }

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

  function isGithub() {
    return isset($this->database['remotes']['origin']) && strpos($this->database['remotes']['origin'], "github.com") !== false;
  }


  function linkRevision($r) {
    if ($this->isGithub()) {
      return $this->linkTo("https://github.com/" . $this->stats['summary']['name'] . "/commit/" . $r, $r, array('github'));
    } else {
      return $r;
    }
  }

  function linkTo($url, $title, $classes = array()) {
    return "<a href=\"" . htmlspecialchars($url) . "\" class=\"" . implode(" ", $classes) . "\">" . htmlspecialchars($title) . "</a>";
  }

}
