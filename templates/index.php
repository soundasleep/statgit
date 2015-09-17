<h1>Development statistics for <?php echo $stats['summary']['name']; ?></h1>

<dl>
  <dt>Generated</dt>
  <dd><?php echo date('r'); ?></dd>

  <dt>Commits</dt>
  <dd><?php echo number_format(count($database['commits'])); ?></dd>

  <dt>Latest commit</dt>
  <dd>
    <?php echo $this->linkCommit($stats['summary']['last_hash']); ?>
    <br>
    <i><?php echo htmlspecialchars($stats['summary']['last_subject']); ?></i>
  </dd>

  <dt>Report period</dt>
  <dd>
    <?php echo date('Y-m-d', strtotime($stats['summary']['first_commit'])); ?> to <?php echo date('Y-m-d', strtotime($stats['summary']['last_commit'])); ?>
    (<?php echo $this->plural((strtotime($stats['summary']['last_commit']) - strtotime($stats['summary']['first_commit'])) / (60 * 60 * 24), "day"); ?>)
  </dd>

  <dt>Total files</dt>
  <dd><?php echo number_format($stats['summary']['total_files']); ?></dd>

  <dt>Total lines of code</dt>
  <dd><?php echo number_format($stats['summary']['total_loc']); ?></dd>

  <dt>Authors</dt>
  <dd><?php echo number_format($stats['summary']['author_count']); ?></dd>

</dl>

<ul class="navigation">
  <li><a href="authors.html">Authors</a></li>
  <li><a href="loc.html">Lines of code</a></li>
  <li><a href="languages.html">Language statistics</a></li>
  <li><a href="files.html">File statistics</a></li>
  <?php if ($database['phpstats']) { ?>
    <li><a href="php.html">PHP statistics</a></li>
  <?php } ?>
  <?php if ($database['rubystats']) { ?>
    <li><a href="ruby.html">Ruby statistics</a></li>
  <?php } ?>
  <?php if ($database['rails']) { ?>
    <li><a href="rails.html">Ruby on Rails statistics</a></li>
  <?php } ?>
  <?php if ($database['rspec']) { ?>
    <li><a href="rspec.html">Rspec statistics</a></li>
  <?php } ?>
  <?php if ($stats['summary']['composer']) { ?>
    <li><a href="composer.html">Composer statistics</a></li>
  <?php } ?>
  <?php if ($stats['summary']['gemfile']) { ?>
    <li><a href="gemfile.html">Gemfile statistics</a></li>
  <?php } ?>
  <li><a href="churn.html">Churn</a></li>
</ul>

<h2>Lines of Code</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  $loc = $this->getTotalLoc($database['stats'][$commit['hash']]);
  $rows[date('Y-m-d', strtotime($date))] = array($date, $loc);
}

$this->renderLineChart($rows, "chart_loc", "LOC");

?>

<h2>Languages</h2>

<?php

$rows = array();
$commit = $database['stats'][$stats['summary']['last_hash']];
foreach ($commit as $language => $value) {
  $rows[$language] = $value['code'];
}

$this->renderPieChart($rows, "chart_languages", "Lines of Code");

?>

<h2>Tag Cloud of Words in Commit Log Messages</h2>

<?php
$tags = $stats['tagcloud']['all'];
require(__DIR__ . "/_tag_cloud.php");
?>

