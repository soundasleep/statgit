<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Churn</h1>

<h2>Lines of Code</h2>

<?php

$rows = array();
foreach ($stats['summary']['days'] as $date => $data) {
  $value = $data['changed'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderBarChart($rows, "churn", "LOC", 800, 600);

?>
