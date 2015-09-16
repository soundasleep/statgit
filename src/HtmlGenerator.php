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
    $this->generateFile("php");
    $this->generateFile("developers");
    $this->generateFile("composer");
    $this->generateFile("churn");

    // generate related files
    foreach ($this->stats['summary']['authors'] as $email => $author) {
      $this->generateFile("developer",
        $this->output . $this->authorLink($author),
        $author);
    }

    // copy over CSS
    copy(__DIR__ . "/../templates/default.css", $this->output . "default.css");
  }

  function authorLink($author) {
    return $this->safe("developer_" . $author['email'] . ".html");
  }

  function generateFile($template, $_file = false, $argument = array()) {
    if ($_file === false) {
      $_file = $this->output . $template . ".html";
    }
    $this->logger->log("Generating '$_file'...");

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
      case "php":
        $title = "Statgit - PHP Statistics";
        break;
      case "developers":
        $title = "Statgit - Developer Statistics";
        break;
      case "developer":
        $title = "Statgit - Developer Statistics - $argument[email]";
        break;
      case "composer":
        $title = "Statgit - Composer Statistics";
        break;
      case "churn":
        $title = "Statgit - Churn Statistics";
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

    file_put_contents($_file, $contents);

  }

  function safe($s) {
    return preg_replace("/[^A-Za-z0-9_\-@]/", "_", $s);
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

  function renderBarChart($rows, $id, $heading, $width = 600, $height = 400) {
    require(__DIR__ . "/../templates/_bar_chart.php");
  }

  function renderPieChart($rows, $id, $title, $width = 400, $height = 300) {
    require(__DIR__ . "/../templates/_pie_chart.php");
  }

  function renderStackedAreaChart($rows, $headings, $id, $title, $width = 600, $height = 400) {
    require(__DIR__ . "/../templates/_stacked_chart.php");
  }

  function renderScatterChart($rows, $heading, $id, $title, $width = 600, $height = 400) {
    require(__DIR__ . "/../templates/_scatter_chart.php");
  }

  function plural($n, $one, $many = false) {
    if ($many === false) {
      $many = $one . "s";
    }
    $n = (int) $n;
    if ($n == 1) {
      return number_format($n) . " " . $one;
    } else {
      return number_format($n) . " " . $many;
    }
  }

}
