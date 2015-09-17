<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Ruby Statistics</h1>

<?php
/* bail early if there are none */
if (!$database['rspec']) {
  return;
}

$width = 400;
$height = 300;

$latest = $database['rspec'][$stats['summary']['last_hash']];
?>

<dl>
  <?php if ($latest['describes']) { ?>
    <dt>Describes</dt>
    <dd><?php echo number_format($latest['describes']); ?></dd>
  <?php } ?>

  <?php if ($latest['contexts']) { ?>
    <dt>Contexts</dt>
    <dd><?php echo number_format($latest['contexts']); ?></dd>
  <?php } ?>

  <?php if ($latest['its']) { ?>
    <dt>Its</dt>
    <dd><?php echo number_format($latest['its']); ?></dd>
  <?php } ?>
</dl>

<h2>Describes</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rspec'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $value = $database['rspec'][$commit['hash']]['describes'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_describes", "Describes", $width, $height);

?>

<h2>Its</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rspec'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $value = $database['rspec'][$commit['hash']]['its'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_its", "Its", $width, $height);

?>

<h2>Its per Describe</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['rspec'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  if ($database['rspec'][$commit['hash']]['describes'] == 0) {
    continue;
  }
  $value = sprintf("%0.2f", $database['rspec'][$commit['hash']]['its'] / $database['rspec'][$commit['hash']]['describes']);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_its_per_describe", "Its", $width, $height);

?>
