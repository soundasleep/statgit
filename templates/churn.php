<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Churn</h1>

<?php

$rows = array();
foreach ($stats['summary']['days'] as $date => $data) {
  $value = $data['changes'];
  $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
}

$this->renderBarChart($rows, "churn", "LOC", 800, 600);

?>
