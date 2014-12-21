<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Lines of Code</h1>

<dl>
  <dt>Total files</dt>
  <dd><?php echo number_format($stats['summary']['total_files']); ?></dd>

  <dt>Total lines of code</dt>
  <dd><?php echo number_format($stats['summary']['total_loc']); ?></dd>

  <dt>First commit</dt>
  <dd><?php echo date('r', strtotime($stats['summary']['first_commit'])); ?></dd>

  <dt>Last commit</dt>
  <dd><?php echo date('r', strtotime($stats['summary']['last_commit'])); ?></dd>
</dl>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $loc = $this->getTotalLoc($database['stats'][$commit['hash']]);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $loc);
}

$this->renderLineChart($rows, "chart_loc", "LOC", 800, 600);

?>
