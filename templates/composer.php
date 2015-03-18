<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Composer Statistics</h1>

<dl>
<?php if (isset($stats['summary']['composer']['require'])) { ?>
  <dt>Declared dependencies</dt>
  <dd><?php echo number_format(count($stats['summary']['composer']['require'])); ?></dd>
<?php } ?>

<?php if (isset($stats['summary']['composer']['requireDev'])) { ?>
  <dt>Declared developer dependencies</dt>
  <dd><?php echo number_format(count($stats['summary']['composer']['requireDev'])); ?></dd>
<?php } ?>

<?php if (isset($stats['summary']['composer']['lock_packages'])) { ?>
  <dt>Discovered dependencies <small>(from .lock)</small></dt>
  <dd><?php echo number_format(count($stats['summary']['composer']['lock_packages'])); ?></dd>
<?php } ?>
</dl>

<h2>Declared Dependencies</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['composer'][$commit['hash']]['require'])) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, count($database['composer'][$commit['hash']]['require']));
}

$this->renderLineChart($rows, "chart_dependencies", "Dependencies");

?>

<h2>Discovered Dependencies</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  $date = $commit['author_date'];
  if (!isset($database['composer'][$commit['hash']]['lock_packages'])) {
    continue;
  }
  $rows[date('Y-m-d', strtotime($date))] = array($date, count($database['composer'][$commit['hash']]['lock_packages']));
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

foreach ($stats['summary']['composer']['lock_packages'] as $package => $info) {
  echo "<tr>";
  echo "<th class=\"package " . (isset($stats['summary']['composer']['require'][$package]) ? " declared" : "") . "\">";
  if (isset($info['source'])) {
    echo "<a href=\"" . htmlspecialchars($info['source']) . "\">";
  }
  echo htmlspecialchars($package);
  if (isset($info['source'])) {
    echo "</a>";
  }
  echo "</th>";
  echo "<td>";
  if (isset($info['source'])) {
    $matches = array();
    if (preg_match("#https?://github.com/([^/]+)/([^/]+?)(|\\.git)$#i", $info['source'], $matches)) {
      $url = htmlspecialchars($matches[1]) . "/" . htmlspecialchars($matches[2]);
      echo "<a href=\"https://travis-ci.org/$url\">";
      echo "<img src=\"https://travis-ci.org/$url.svg?branch=master\">";
      echo "</a>";
    }
  }
  echo "</td>";
  echo "</tr>\n";
}

?>
  </tbody>
</table>
