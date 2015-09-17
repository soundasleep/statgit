<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Gemfile Statistics</h1>

<dl>
<?php if (isset($stats['summary']['gemfile']['dependencies'])) { ?>
  <dt>Declared dependencies</dt>
  <dd><?php echo number_format(count($stats['summary']['gemfile']['dependencies'])); ?></dd>
<?php } ?>

<?php if (isset($stats['summary']['gemfile']['specs'])) { ?>
  <dt>Discovered dependencies <small>(from .lock)</small></dt>
  <dd><?php echo number_format($stats['summary']['gemfile']['specs']); ?></dd>
<?php } ?>
</dl>

<h2>Declared Dependencies</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['gemfile'][$commit['hash']]['dependencies'])) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, count($database['gemfile'][$commit['hash']]['dependencies']));
}

$this->renderLineChart($rows, "chart_dependencies", "Dependencies");

?>

<h2>Discovered Dependencies</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['gemfile'][$commit['hash']]['specs'])) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, $database['gemfile'][$commit['hash']]['specs']);
}

$this->renderLineChart($rows, "chart_lock_dependencies", "Dependencies");

?>
