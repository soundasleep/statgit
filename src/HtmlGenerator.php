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
    $this->generateFile("loc");
    $this->generateFile("files");
    $this->generateFile("languages");

    // copy over CSS
    copy(__DIR__ . "/../templates/default.css", $this->output . "default.css");
  }

  function generateFile($template) {
    $file = $this->output . $template . ".html";
    $this->logger->log("Generating '$file'...");

    switch ($template) {
      case "index":
        $title = "Statgit - " . $this->stats['summary']['name'];
        break;
      case "loc":
        $title = "Statgit - Lines of Code";
        break;
      case "files":
        $title = "Statgit - File Statistics";
        break;
      case "languages":
        $title = "Statgit - Language Statistics";
        break;
      default:
        $title = "Statgit";
    }

    ob_start();

    // lets use PHP to make our lives easier!
    $stats = $this->stats;
    $database = $this->database;
    require(__DIR__ . "/../templates/header.php");
    require(__DIR__ . "/../templates/" . $template . ".php");
    require(__DIR__ . "/../templates/footer.php");

    $contents = ob_get_contents();
    ob_end_clean();

    file_put_contents($file, $contents);

  }

  function isGithub() {
    return isset($this->database['remotes']['origin']) && strpos($this->database['remotes']['origin'], "github.com") !== false;
  }


  function linkCommit($r) {
    if ($this->isGithub()) {
      return $this->linkTo("https://github.com/" . $this->stats['summary']['name'] . "/commit/" . $r, $r, array('github'));
    } else {
      return $r;
    }
  }

  function linkTo($url, $title, $classes = array()) {
    return "<a href=\"" . htmlspecialchars($url) . "\" class=\"" . implode(" ", $classes) . "\">" . htmlspecialchars($title) . "</a>";
  }

  function getTotalLoc($stats) {
    $total = 0;
    foreach ($stats as $language) {
      $total += $language['code'];
    }
    return $total;
  }

  function getTotalFiles($stats) {
    $total = 0;
    foreach ($stats as $language) {
      $total += $language['files'];
    }
    return $total;
  }

  function renderLineChart($rows, $id, $title, $width = 600, $height = 400) {
    require(__DIR__ . "/../templates/_line_chart.php");
  }

  function renderPieChart($rows, $id, $title, $width = 400, $height = 300) {
    require(__DIR__ . "/../templates/_pie_chart.php");
  }

}
