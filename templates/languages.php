<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Languages</h1>

<dl>
  <dt>Total languages</dt>
  <dd><?php echo number_format(count($database['stats'][$stats['summary']['last_hash']])); ?></dd>

  <dt>Lines</dt>
  <dd><?php echo number_format($stats['summary']['total_lines']); ?></dd>

  <dt>Lines of code</dt>
  <dd><?php echo number_format($stats['summary']['total_loc']); ?>
    (<?php echo sprintf("%0.2f%%", 100 * $stats['summary']['total_loc'] / $stats['summary']['total_lines']); ?>)</dd>

  <dt>Lines of comments</dt>
  <dd><?php echo number_format($stats['summary']['total_comments']); ?>
    (<?php echo sprintf("%0.2f%%", 100 * $stats['summary']['total_comments'] / $stats['summary']['total_lines']); ?>)</dd>

  <dt>Blank lines</dt>
  <dd><?php echo number_format($stats['summary']['total_blanks']); ?>
    (<?php echo sprintf("%0.2f%%", 100 * $stats['summary']['total_blanks'] / $stats['summary']['total_lines']); ?>)</dd>
</dl>

<?php

$rows = array();
$commit = $database['stats'][$stats['summary']['last_hash']];
foreach ($commit as $language => $value) {
  $rows[$language] = $value['code'];
}

$this->renderPieChart($rows, "chart_languages", "Lines of Code");

?>

<table class="statistics">
  <thead>
    <tr><th>Language</th><th>Files</th><th>Lines of Code</th><th>LOC per File</th><th>Comments</th><th>Comments per File</th><th>Blank Lines</th><th>Blank Lines per File</th></tr>
  </thead>
  <tbody>
<?php

foreach ($commit as $language => $value) {
  echo "<tr>";
  echo "<th class=\"language\">";
  echo $this->linkTo($this->languageLink($value), htmlspecialchars($language));
  echo "</th>";
  echo "<td>" . number_format($value['files']) . " (" . sprintf("%0.2f%%", 100 * $value['files'] / $stats['summary']['total_files']) . ")</td>";
  echo "<td>" . number_format($value['code']) . " (" . sprintf("%0.2f%%", 100 * $value['code'] / $stats['summary']['total_loc']) . ")</td>";
  echo "<td>" . number_format($value['code'] / $value['files'], 1) . "</td>";
  echo "<td>" . number_format($value['comment']) . " (" . sprintf("%0.2f%%", 100 * $value['comment'] / $stats['summary']['total_comments']) . ")</td>";
  echo "<td>" . number_format($value['comment'] / $value['files'], 1) . "</td>";
  echo "<td>" . number_format($value['blank']) . " (" . sprintf("%0.2f%%", 100 * $value['blank'] / $stats['summary']['total_blanks']) . ")</td>";
  echo "<td>" . number_format($value['blank'] / $value['files'], 1) . "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
  <tfoot>
    <tr>
      <th>Total</th>
      <td><?php echo number_format($stats['summary']['total_files']); ?></td>
      <td><?php echo number_format($stats['summary']['total_loc']); ?></td>
      <td><?php echo number_format($stats['summary']['total_loc'] / $stats['summary']['total_files'], 1); ?></td>
      <td><?php echo number_format($stats['summary']['total_comments']); ?></td>
      <td><?php echo number_format($stats['summary']['total_comments'] / $stats['summary']['total_files'], 1); ?></td>
      <td><?php echo number_format($stats['summary']['total_blanks']); ?></td>
      <td><?php echo number_format($stats['summary']['total_blanks'] / $stats['summary']['total_files'], 1); ?></td>
    </tr>
  </tfoot>
</table>

<h2>Language History</h2>

<?php

// first find all languages ever used
$languages = array();
foreach ($database['stats'] as $commit) {
  $languages = array_values(array_unique(array_merge(array_keys($commit), $languages)));
}

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];

  $row = array(date('Y-m-d', strtotime($date)));
  foreach ($languages as $i => $lang) {
    $row[] = 0;
  }

  foreach ($database['stats'][$commit['hash']] as $language => $value) {
    $row[array_search($language, $languages) + 1] = $value['code'];
  }

  $rows[] = $row;
}

$this->renderStackedAreaChart($rows, $languages, "chart_history", "Lines of Code", 800, 600);

?>

<h2>Comments</h2>

<?php

$rows = array();
foreach ($database['stats'] as $hash => $stats) {
  $commit = false;
  foreach ($database['commits'] as $c) {
    if ($c['hash'] === $hash) {
      $commit = $c;
      break;
    }
  }
  if (!$commit) {
    continue;
  }

  $date = $commit['author_date'];
  $lines = 0;
  $comments = 0;
  foreach ($stats as $language => $value) {
    $lines += $value['code'] + $value['comment'] + $value['blank'];
    $comments += $value['comment'];
  }
  if ($lines == 0) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, sprintf("%0.2f", 100 * ($comments / $lines)));
}

$this->renderLineChart($rows, "chart_comments", "Comments %");

?>
