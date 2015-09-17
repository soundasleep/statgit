<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Ruby Statistics</h1>

<?php
/* bail early if there are none */
if (!$database['rubystats']) {
  return;
}

$width = 400;
$height = 300;

$latest = $database['rubystats'][$stats['summary']['last_hash']];
?>

<dl>
  <?php if ($latest['classes']) { ?>
    <dt>Classes</dt>
    <dd><?php echo number_format($latest['classes']); ?></dd>
  <?php } ?>

  <?php if ($latest['defs']) { ?>
    <dt>Defs</dt>
    <dd><?php echo number_format($latest['defs']); ?></dd>
  <?php } ?>

  <?php if ($latest['requires']) { ?>
    <dt>Requires</dt>
    <dd><?php echo number_format($latest['requires']); ?></dd>
  <?php } ?>

  <?php if ($latest['validates']) { ?>
    <dt>Validates</dt>
    <dd><?php echo number_format($latest['validates']); ?></dd>
  <?php } ?>

  <?php if ($latest['filters']) { ?>
    <dt>Filters</dt>
    <dd><?php echo number_format($latest['filters']); ?></dd>
  <?php } ?>

  <?php if ($latest['comments']) { ?>
    <dt>Comments</dt>
    <dd><?php echo number_format($latest['comments']); ?></dd>
  <?php } ?>
</dl>

<h2>Defs</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rubystats'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $value = $database['rubystats'][$commit['hash']]['defs'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_defs", "Defs", $width, $height);

?>

<h2>Defs per File</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['stats'][$commit['hash']]['Ruby']) || $database['stats'][$commit['hash']]['Ruby']['files'] == 0) {
    continue;
  }

  $value = sprintf("%0.2f", $database['rubystats'][$commit['hash']]['defs'] / $database['stats'][$commit['hash']]['Ruby']['files']);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_defs_file", "Defs per File", $width, $height);

?>

<h2>Classes</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rubystats'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $value = $database['rubystats'][$commit['hash']]['classes'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_classes", "Classes", $width, $height);

?>

<h2>Defs per Class</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rubystats'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  if ($database['rubystats'][$commit['hash']]['classes'] == 0) {
    continue;
  }
  $value = sprintf("%0.2f", $database['rubystats'][$commit['hash']]['defs'] / $database['rubystats'][$commit['hash']]['classes']);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_class_methods_avg", "Defs", $width, $height);

?>

<h2>Includes</h2>

<?php

$always_zero = true;
$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rubystats'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $value = $database['rubystats'][$commit['hash']]['includes'];
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

<h2>Requires</h2>

<?php

$always_zero = true;
$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rubystats'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $value = $database['rubystats'][$commit['hash']]['requires'];
  if ($value) {
    $always_zero = false;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

if ($always_zero) {
  echo "(none)";
} else {
  $this->renderLineChart($rows, "chart_requires", "Requires", $width, $height);
}

?>

<h2>Lines of Code per Class</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rubystats'][$commit['hash']])) {
    // ignore Ruby parse errors
    continue;
  }
  $classes = $database['rubystats'][$commit['hash']]['classes'];

  if ($classes == 0) {
    continue;
  }

  $value = sprintf("%0.2f", $database['stats'][$commit['hash']]['Ruby']['code'] / $classes);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_loc_method", "LOC", $width, $height);

?>
