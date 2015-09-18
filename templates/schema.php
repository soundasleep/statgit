<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Schema Statistics</h1>

<?php
/* bail early if there are none */
if (!$database['schema']) {
  return;
}

$width = 400;
$height = 300;

$latest = $database['schema'][$stats['summary']['last_hash']];
?>

<dl>
<?php

$keys = array(
  "tables" => "Tables",
  "columns" => "Columns",
  "indexes" => "Indexes",
  "not_null" => "Not null",
);

foreach ($keys as $key => $title) {
  if ($latest[$key]) {
    echo "<dt>" . $title . "</dt>";
    echo "<dd>" . number_format($latest[$key]) . "</dd>";
  }
}

?>
</dl>

<?php

$graphs = array(
  "tables" => "Tables",
  "columns" => "Columns",
  "indexes" => "Indexes",
  "not_null" => "Not null",
);

?>

<?php foreach ($graphs as $key => $title) { ?>
  <h2><?php echo htmlspecialchars($title); ?></h2>

  <?php

  $rows = array();
  foreach ($database['commits'] as $commit) {
    $date = $commit['author_date'];

    if (!isset($database['schema'][$commit['hash']])) {
      // ignore PHP parse errors
      continue;
    }

    $value = $database['schema'][$commit['hash']][$key];
    $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
  }

  $this->renderLineChart($rows, "chart_" . $key, $title, $width, $height);

  ?>
<?php } ?>

<h2>Columns per Table</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['schema'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $columns = $database['schema'][$commit['hash']]['columns'];
  $tables = $database['schema'][$commit['hash']]['tables'];
  if ($tables == 0) {
    continue;
  }

  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%0.2f", $columns / $tables));
}

$this->renderLineChart($rows, "chart_columns_per_table", "Columns", $width, $height);

?>

<h2>Indexes per Column</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  if (!isset($database['schema'][$commit['hash']])) {
    // ignore PHP parse errors
    continue;
  }

  $indexes = $database['schema'][$commit['hash']]['indexes'];
  $columns = $database['schema'][$commit['hash']]['columns'];
  if ($columns == 0) {
    continue;
  }

  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%0.2f", $indexes / $columns));
}

$this->renderLineChart($rows, "chart_indexes_per_column", "Indexes", $width, $height);

?>
