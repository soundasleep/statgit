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
    (<?php echo sprintf("%0.1f%%", 100 * $stats['summary']['total_loc'] / $stats['summary']['total_lines']); ?>)</dd>

  <dt>Lines of comments</dt>
  <dd><?php echo number_format($stats['summary']['total_comments']); ?>
    (<?php echo sprintf("%0.1f%%", 100 * $stats['summary']['total_comments'] / $stats['summary']['total_lines']); ?>)</dd>

  <dt>Blank lines</dt>
  <dd><?php echo number_format($stats['summary']['total_blanks']); ?>
    (<?php echo sprintf("%0.1f%%", 100 * $stats['summary']['total_blanks'] / $stats['summary']['total_lines']); ?>)</dd>
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
  echo "<th>" . htmlspecialchars($language) . "</th>";
  echo "<td>" . number_format($value['files']) . " (" . sprintf("%0.1f%%", 100 * $value['files'] / $stats['summary']['total_files']) . ")</td>";
  echo "<td>" . number_format($value['code']) . " (" . sprintf("%0.1f%%", 100 * $value['code'] / $stats['summary']['total_loc']) . ")</td>";
  echo "<td>" . number_format($value['code'] / $value['files'], 1) . "</td>";
  echo "<td>" . number_format($value['comment']) . " (" . sprintf("%0.1f%%", 100 * $value['comment'] / $stats['summary']['total_comments']) . ")</td>";
  echo "<td>" . number_format($value['comment'] / $value['files'], 1) . "</td>";
  echo "<td>" . number_format($value['blank']) . " (" . sprintf("%0.1f%%", 100 * $value['blank'] / $stats['summary']['total_blanks']) . ")</td>";
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
