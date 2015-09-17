<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Cucumber Statistics</h1>

<?php
/* bail early if there are none */
if (!$database['cucumber']) {
  return;
}

$width = 400;
$height = 300;

$latest = $database['cucumber'][$stats['summary']['last_hash']];
?>

<dl>
  <?php if ($latest['features']) { ?>
    <dt>Features</dt>
    <dd><?php echo number_format($latest['features']); ?></dd>
  <?php } ?>

  <?php if ($latest['scenarios']) { ?>
    <dt>Scenarios</dt>
    <dd><?php echo number_format($latest['scenarios']); ?></dd>
  <?php } ?>

  <dt>Steps</dt>
  <dd><?php echo number_format($latest['givens'] + $latest["whens"] + $latest["thens"] + $latest["ands"]); ?></dd>

  <?php if ($latest['annotations']) { ?>
    <dt>@annotations</dt>
    <dd><?php echo number_format($latest['annotations']); ?></dd>
  <?php } ?>
</dl>

<h2>Features</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['cucumber'][$commit['hash']])) {
    // ignore parse errors
    continue;
  }

  $value = $database['cucumber'][$commit['hash']]['features'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_features", "Features", $width, $height);

?>

<h2>Scenarios</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['cucumber'][$commit['hash']])) {
    // ignore parse errors
    continue;
  }

  $value = $database['cucumber'][$commit['hash']]['scenarios'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_scenarios", "Scenarios", $width, $height);

?>

<h2>Steps <small>(Given, When, And, Then)</small></h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['cucumber'][$commit['hash']])) {
    // ignore parse errors
    continue;
  }

  $value = $database['cucumber'][$commit['hash']]['givens'] +
      $database['cucumber'][$commit['hash']]['whens'] +
      $database['cucumber'][$commit['hash']]['ands'] +
      $database['cucumber'][$commit['hash']]['thens'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_steps", "Steps", $width, $height);

?>

<h2>Annotations</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['cucumber'][$commit['hash']])) {
    // ignore parse errors
    continue;
  }

  $value = $database['cucumber'][$commit['hash']]['annotations'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_annotations", "@annotations", $width, $height);

?>

<h2>Steps per Scenario</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['cucumber'][$commit['hash']])) {
    // ignore parse errors
    continue;
  }

  $steps = $database['cucumber'][$commit['hash']]['givens'] +
      $database['cucumber'][$commit['hash']]['whens'] +
      $database['cucumber'][$commit['hash']]['ands'] +
      $database['cucumber'][$commit['hash']]['thens'];
  $scenarios = $database['cucumber'][$commit['hash']]['scenarios'];
  if ($scenarios == 0) {
    continue;
  }

  $value = sprintf("%0.2f", $steps / $scenarios);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_steps_per_scenario", "Steps", $width, $height);

?>

<h2>Scenarios per Feature</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['cucumber'][$commit['hash']])) {
    // ignore parse errors
    continue;
  }

  $scenarios = $database['cucumber'][$commit['hash']]['scenarios'];
  $features = $database['cucumber'][$commit['hash']]['features'];
  if ($features == 0) {
    continue;
  }

  $value = sprintf("%0.2f", $scenarios / $features);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_scenarios_per_feature", "Scenarios", $width, $height);

?>

