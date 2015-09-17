<?php
$language = $argument;
?>

<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
  <a href="languages.html">Languages</a> &gt;&gt;
</div>
<h1><?php echo htmlspecialchars($language['language']); ?></h1>

<dl>
  <dt>Files</dt>
  <dd><?php echo number_format($language['files']); ?></dd>

  <dt>Lines of code</dt>
  <dd><?php echo number_format($language['code']); ?></dd>

  <dt>Lines of comments</dt>
  <dd><?php echo number_format($language['comment']); ?>
    (<?php echo sprintf("%0.2f%%", 100 * $language['comment'] / ($language['comment'] + $language['code'] + $language['blank'])); ?>)</dd>

  <dt>Blank lines</dt>
  <dd><?php echo number_format($language['blank']); ?></dd>
</dl>

<h2>Lines of Code</h2>

<?php

$rows = array();
foreach ($database['stats'] as $hash => $stats) {
  $commit = false;
  foreach ($database['commits'] as $c) {
    if ($c['hash'] === $hash) {
      $commit = $c;
      break;
    }
  }
  if (!$commit) {
    continue;
  }

  $date = $commit['author_date'];
  $lines = 0;
  foreach ($stats as $x => $value) {
    if ($value['language'] == $language['language']) {
      $lines += $value['code'];
    }
  }
  if ($lines == 0) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, $lines);
}

$this->renderLineChart($rows, "chart_loc", $language['language']);

?>

<h2>Lines of Code per File</h2>

<?php

$rows = array();
foreach ($database['stats'] as $hash => $stats) {
  $commit = false;
  foreach ($database['commits'] as $c) {
    if ($c['hash'] === $hash) {
      $commit = $c;
      break;
    }
  }
  if (!$commit) {
    continue;
  }

  $date = $commit['author_date'];
  $lines = 0;
  $files = 0;
  foreach ($stats as $x => $value) {
    if ($value['language'] == $language['language']) {
      $lines += $value['code'];
      $files += $value['files'];
    }
  }
  if ($files == 0) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%0.2f", $lines / $files));
}

$this->renderLineChart($rows, "chart_loc_file", "LOC/File");

?>

<h2>Comments</h2>

<?php

$rows = array();
foreach ($database['stats'] as $hash => $stats) {
  $commit = false;
  foreach ($database['commits'] as $c) {
    if ($c['hash'] === $hash) {
      $commit = $c;
      break;
    }
  }
  if (!$commit) {
    continue;
  }

  $date = $commit['author_date'];
  $lines = 0;
  $comments = 0;
  foreach ($stats as $x => $value) {
    if ($value['language'] == $language['language']) {
      $lines += $value['code'] + $value['comment'] + $value['blank'];
      $comments += $value['comment'];
    }
  }
  if ($lines == 0) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%0.2f", 100 * ($comments / $lines)));
}

$this->renderLineChart($rows, "chart_comments", "Comments %");

?>


