<?php
$author = $argument;
?>

<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
  <a href="authors.html">Authors</a> &gt;&gt;
</div>
<h1><?php echo htmlspecialchars($author['email']); ?></h1>

<dl>
  <dt>Name</dt>
  <dd><?php echo htmlspecialchars($author['name']); ?></dd>

  <dt>First commit</dt>
  <dd>
    <?php echo $this->linkCommit($author['first_hash']); ?>
    <br>
    <i><?php echo htmlspecialchars($author['first_subject']); ?></i>
  </dd>

  <dt>Last commit</dt>
  <dd>
    <?php echo $this->linkCommit($author['last_hash']); ?>
    <br>
    <i><?php echo htmlspecialchars($author['last_subject']); ?></i>
  </dd>

  <dt>Commits</dt>
  <dd><?php echo number_format($author['commits']); ?></dd>

  <dt>Changes</dt>
  <dd>
    <?php echo $this->plural($author['added'], "addition"); ?>,
    <?php echo $this->plural($author['removed'], "deletion"); ?>
  </dd>
</dl>

<?php

$commit_email = $author['email'];
require(__DIR__ . "/_author_activity.php");

?>

<h2>Files with Most Revisions</h2>

<table class="statistics">
  <thead>
    <tr><th>File</th><th>Commits</th></tr>
  </thead>
  <tbody>
<?php

foreach ($author['files'] as $filename => $commits) {
  $exists = $stats['file_revisions'][$filename]['exists'];
  echo "<tr>";
  echo "<th class=\"filename\"><span class=\"file" . ($exists ? " exists" : " deleted") . "\">" . htmlspecialchars($filename) . "</span></th>";
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
