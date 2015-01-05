<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Developers</h1>

<dl>
  <dt>Total committers</dt>
  <dd><?php echo number_format($stats['summary']['author_count']); ?></dd>
</dl>

<h2>Commit Activity</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $x = date('Y-m-d', strtotime($date));
  $y = sprintf("%0.2f", date('H', strtotime($date)) + (date('m', strtotime($date)) * (1/60)));

  $rows[] = array($x, $y);
}

$this->renderScatterChart($rows, "Hour", "chart_commits", "Commit Activity", 800, 150);

?>
