<?php
$author = $argument;
?>

<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
  <a href="developers.html">Developers</a> &gt;&gt;
</div>
<h1><?php echo htmlspecialchars($author['email']); ?></h1>

<dl>
  <dt>Name</dt>
  <dd><?php echo htmlspecialchars($author['name']); ?></dd>

  <dt>First commit</dt>
  <dd><?php echo date('r', strtotime($author['first_commit'])); ?></dd>

  <dt>Last commit</dt>
  <dd><?php echo date('r', strtotime($author['last_commit'])); ?></dd>

  <dt>Commits</dt>
  <dd><?php echo number_format($author['commits']); ?></dd>

  <dt>Changes</dt>
  <dd>
    <?php echo $this->plural($author['added'], "addition"); ?>,
    <?php echo $this->plural($author['removed'], "deletion"); ?>
  </dd>
</dl>

<h2>Commit Activity</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  if ($commit['author_email'] == $author['email']) {
    $date = $commit['author_date'];
    $x = date('Y-m-d', strtotime($date));
    $y = sprintf("%0.2f", date('H', strtotime($date)) + (date('m', strtotime($date)) * (1/60)));

    $rows[] = array($x, $y);
  }
}

$this->renderScatterChart($rows, "Hour", "chart_commits", "Commit Activity", 800, 300);

?>

<h2>Files with Most Revisions</h2>

<table class="statistics">
  <thead>
    <tr><th>File</th><th>Commits</th></tr>
  </thead>
  <tbody>
<?php

foreach ($author['files'] as $file => $commits) {
  echo "<tr>";
  echo "<th>" . htmlspecialchars($file) . "</th>";
  echo "<td>" . number_format($commits) . "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>

<h2>Tag Cloud of Words in Commit Log Messages</h2>

<?php
$tags = $stats['tagcloud'][$author['email']];
require(__DIR__ . "/_tag_cloud.php");
?>
