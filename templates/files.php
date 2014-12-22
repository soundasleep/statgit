<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>File Statistics</h1>

<dl>
  <dt>Total files</dt>
  <dd><?php echo number_format($stats['summary']['total_files']); ?></dd>

  <dt>Average file size</dt>
  <dd><?php echo number_format($stats['summary']['total_loc'] / $stats['summary']['total_files']); ?> lines of code</dd>
</dl>

<h2>File Count</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $value = $this->getTotalFiles($database['stats'][$commit['hash']]);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderLineChart($rows, "chart_files", "Files", 800, 600);

?>

<h2>Average Lines of Code per File</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $value = $this->getTotalLoc($database['stats'][$commit['hash']]) / $this->getTotalFiles($database['stats'][$commit['hash']]);
  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%04.2f", $value));
}

$this->renderLineChart($rows, "chart_average", "LOC/File", 800, 600);

?>
