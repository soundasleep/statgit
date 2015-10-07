<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Authors</h1>

<dl>
  <dt>Total authors</dt>
  <dd><?php echo number_format($stats['summary']['author_count']); ?></dd>
</dl>

<?php

$commit_email = false;
require(__DIR__ . "/_author_activity.php");

?>

<h2>Top Authors</h2>

<table class="statistics">
  <thead>
    <tr><th>Author</th><th>Commits</th><th>Changes</th><th>Last Commit</th></tr>
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
$sorted = array_splice($sorted, 0, 50);

foreach ($sorted as $email => $author) {
  if (!$author['email']) {
    continue;
  }
  echo "<tr>";
  echo "<th>" . $this->linkTo($this->authorLink($author), $author['email']) . "</th>";
  echo "<td class=\"number\">" . number_format($author['commits']) . "</td>";
  echo "<td class=\"number\">" . number_format($author['changed']) . "</td>";
  echo "<td class=\"date\">" . date("Y-m-d", strtotime($author['last_commit'])) . "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>

<h2>Ownership <small>(blame)</small></h2>

<?php

$sorted = $stats["summary"]["authors"];
uasort($sorted, function ($a, $b) {
  if ($a["blame"] == $b["blame"]) {
    return 0;
  }
  return $a["blame"] > $b["blame"] ? -1 : 1;
});
$rows = array();
foreach ($sorted as $author) {
  $rows[$author['email']] = $author['blame'];
}

$this->renderPieChart($rows, "chart_authors_pie_blame", "Lines");

?>
