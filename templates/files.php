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

  if ($this->getTotalFiles($database['stats'][$commit['hash']]) === 0) {
    // prevent div/0
    continue;
  }

  $value = $this->getTotalLoc($database['stats'][$commit['hash']]) / $this->getTotalFiles($database['stats'][$commit['hash']]);
  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%04.2f", $value));
}

$this->renderLineChart($rows, "chart_average", "LOC/File", 800, 600);

?>

<h2>Files with Most Revisions</h2>

<table class="statistics">
  <thead>
    <tr><th>File</th><th>Revisions</th><th>Size</th></tr>
  </thead>
  <tbody>
<?php

$sorted = $stats["file_revisions"];
uasort($sorted, function ($a, $b) {
  if ($a["revisions"] == $b["revisions"]) {
    return 0;
  }
  return $a["revisions"] > $b["revisions"] ? -1 : 1;
});
$sorted = array_splice($sorted, 0, 20);

foreach ($sorted as $filename => $file) {
  $size = isset($database["files"][$filename]) ? $database["files"][$filename] : 0;

  echo "<tr>";
  echo "<th class=\"filename\"><span class=\"file" . ($file['exists'] ? " exists" : " deleted") . "\">" . htmlspecialchars($filename) . "</span></th>";
  echo "<td class=\"number\">" . number_format($file['revisions']) . "</td>";
  echo "<td class=\"number\">" . number_format($size / 1024, 2) . " KB</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>

<h2>Existing Files with Most Revisions</h2>

<table class="statistics">
  <thead>
    <tr><th>File</th><th>Revisions</th><th>Size</th></tr>
  </thead>
  <tbody>
<?php

$sorted = array_filter($stats["file_revisions"], function ($a) {
  return $a["exists"];
});
uasort($sorted, function ($a, $b) {
  if ($a["revisions"] == $b["revisions"]) {
    return 0;
  }
  return $a["revisions"] > $b["revisions"] ? -1 : 1;
});
$sorted = array_splice($sorted, 0, 20);

foreach ($sorted as $filename => $file) {
  $size = isset($database["files"][$filename]) ? $database["files"][$filename] : 0;

  echo "<tr>";
  echo "<th class=\"filename\"><span class=\"file" . ($file['exists'] ? " exists" : " deleted") . "\">" . htmlspecialchars($filename) . "</span></th>";
  echo "<td class=\"number\">" . number_format($file['revisions']) . "</td>";
  echo "<td class=\"number\">" . number_format($size / 1024, 2) . " KB</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>

<h2>Largest files</h2>

<table class="statistics">
  <thead>
    <tr><th>File</th><th>Size</th><th>Revisions</th></tr>
  </thead>
  <tbody>
<?php

$sorted = $database["files"];
arsort($sorted);
$sorted = array_splice($sorted, 0, 20);

foreach ($sorted as $filename => $size) {
  $file = $stats["file_revisions"][$filename];

  echo "<tr>";
  echo "<th class=\"filename\"><span class=\"file" . ($file['exists'] ? " exists" : " deleted") . "\">" . htmlspecialchars($filename) . "</span></th>";
  echo "<td class=\"number\">" . number_format($size / 1024, 2) . " KB</td>";
  echo "<td class=\"number\">" . number_format($file["revisions"]) . "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>
