<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Gemfile Statistics</h1>

<dl>
<?php if (isset($stats['summary']['gemfile']['dependencies'])) { ?>
  <dt>Declared dependencies</dt>
  <dd><?php echo number_format(count($stats['summary']['gemfile']['dependencies'])); ?></dd>
<?php } ?>

<?php if (isset($stats['summary']['gemfile']['specs'])) { ?>
  <dt>Discovered dependencies <small>(from .lock)</small></dt>
  <dd><?php echo number_format($stats['summary']['gemfile']['specs']); ?></dd>
<?php } ?>
</dl>

<h2>Declared Dependencies</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['gemfile'][$commit['hash']]['dependencies'])) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, count($database['gemfile'][$commit['hash']]['dependencies']));
}

$this->renderLineChart($rows, "chart_dependencies", "Dependencies");

?>

<h2>Discovered Dependencies</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['gemfile'][$commit['hash']]['specs'])) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, $database['gemfile'][$commit['hash']]['specs']);
}

$this->renderLineChart($rows, "chart_lock_dependencies", "Dependencies");

?>

<h2>travis-ci.org status</h2>

<table class="statistics">
  <thead>
    <tr><th>Dependency</th><th>Status</th></tr>
  </thead>
  <tbody>
<?php

foreach ($stats['summary']['gemfile']['dependencies'] as $dependency) {
  $info = false;
  if (isset($database['rubygems'][$dependency])) {
    $info = $database['rubygems'][$dependency];
  }

  $source = false;
  $source = isset($info['source_code_uri']) ? $info['source_code_uri'] : $source;
  $source = isset($info['homepage_uri']) ? $info['homepage_uri'] : $source;

  echo "<tr>";
  echo "<th class=\"package\">";
  if ($source) {
    echo "<a href=\"" . htmlspecialchars($source) . "\">";
  }
  echo htmlspecialchars($dependency);
  if ($source) {
    echo "</a>";
  }
  echo "</th>";
  echo "<td>";
  if ($source) {
    echo $this->getTravisCiBadge($source);
  }
  echo "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>
