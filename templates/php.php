<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>PHP Statistics</h1>

<?php
/* bail early if there are none */
if (!$database['phpstats']) {
  return;
}

$width = 400;
$height = 300;
?>

<dl>
  <dt>Statements</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['statements']); ?></dd>

  <dt>Classes</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['classes']); ?></dd>

  <dt>Interfaces</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['interfaces']); ?></dd>

  <dt>Class methods</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['class_methods']); ?></dd>

  <dt>Functions</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['functions']); ?></dd>

  <dt>Includes</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['includes']); ?></dd>

  <dt>Inline HTML blocks</dt>
  <dd><?php echo number_format($database['phpstats'][$stats['summary']['last_hash']]['inline_html']); ?></dd>
</dl>

<h2>Statements</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $value = $database['phpstats'][$commit['hash']]['statements'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_statements", "Statements", $width, $height);

?>

<h2>Statements per File</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['stats'][$commit['hash']]['PHP']) || $database['stats'][$commit['hash']]['PHP']['files'] == 0) {
    continue;
  }
  $value = sprintf("%0.1f", $database['phpstats'][$commit['hash']]['statements'] / $database['stats'][$commit['hash']]['PHP']['files']);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_statements_file", "Statements per File", $width, $height);

?>

<h2>Classes</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $value = $database['phpstats'][$commit['hash']]['classes'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_classes", "Classes", $width, $height);

?>

<h2>Methods per Class</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if ($database['phpstats'][$commit['hash']]['classes'] == 0) {
    continue;
  }
  $value = sprintf("%0.1f", $database['phpstats'][$commit['hash']]['class_methods'] / $database['phpstats'][$commit['hash']]['classes']);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_class_methods_avg", "Methods", $width, $height);

?>

<h2>Includes</h2>

<?php

$always_zero = true;
$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $value = $database['phpstats'][$commit['hash']]['includes'];
  if ($value) {
    $always_zero = false;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

if ($always_zero) {
  echo "(none)";
} else {
  $this->renderLineChart($rows, "chart_includes", "Includes", $width, $height);
}

?>

<h2>Inline HTML blocks</h2>

<?php

$always_zero = true;
$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $value = $database['phpstats'][$commit['hash']]['inline_html'];
  if ($value) {
    $always_zero = false;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

if ($always_zero) {
  echo "(none)";
} else {
  $this->renderLineChart($rows, "chart_inline_html", "Blocks", $width, $height);
}

?>

<h2>Lines of Code per Method/Function</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['phpstats'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }
  $functions = $database['phpstats'][$commit['hash']]['functions'] + $database['phpstats'][$commit['hash']]['class_methods'];

  if ($functions == 0) {
    continue;
  }

  $value = sprintf("%0.1f", $database['stats'][$commit['hash']]['PHP']['code'] / $functions);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_loc_method", "LOC", $width, $height);

?>
