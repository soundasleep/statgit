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

$this->renderScatterChart($rows, "Hour", "chart_commits", "Commit Activity", 800, 300);

?>

<h3>Per Day</h3>

<?php

$rows = array(
  'Mon' => 0,
  'Tue' => 0,
  'Wed' => 0,
  'Thu' => 0,
  'Fri' => 0,
  'Sat' => 0,
  'Sun' => 0,
);
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $key = date('D', strtotime($date));
  $rows[$key] += 1;
}

$this->renderHistogramChart($rows, "Commits", "chart_commits_day", "Commit Activity per Day", 400, 300);

?>

<h3>Per Hour</h3>

<?php

$rows = array();
for ($i = 0; $i <= 23; $i++) {
  $rows[$i . "h"] = 0;
};
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $key = date('G', strtotime($date)) . "h";
  $rows[$key] += 1;
}

$this->renderHistogramChart($rows, "Commits", "chart_commits_hour", "Commit Activity per Hour", 400, 300);

?>

<h2>Top Developers</h2>

<table class="statistics">
  <thead>
    <tr><th>Developer</th><th>Commits</th><th>Changes</th><th>Last Commit</th></tr>
  </thead>
  <tbody>
<?php

$sorted = $stats["summary"]["authors"];
uasort($sorted, function ($a, $b) {
  if ($a["commits"] == $b["commits"]) {
    return 0;
  }
  return $a["commits"] > $b["commits"] ? -1 : 1;
});
$sorted = array_splice($sorted, 0, 20);

foreach ($sorted as $email => $author) {
  echo "<tr>";
  echo "<th>" . $this->linkTo($this->authorLink($author), $author['email']) . "</th>";
  echo "<td>" . number_format($author['commits']) . "</td>";
  echo "<td>" . number_format($author['changed']) . "</td>";
  echo "<td>" . date("Y-m-d", strtotime($author['last_commit'])) . "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>
