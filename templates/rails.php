<div class="breadcrumb">
  <a href="index.html">Statgit</a> &gt;&gt;
</div>
<h1>Ruby Statistics</h1>

<?php
/* bail early if there are none */
if (!$database['rails']) {
  return;
}

$width = 400;
$height = 300;

$latest = $database['rails'][$stats['summary']['last_hash']];
?>

<dl>
<?php

$keys = array(
  "controllers" => "Controllers",
  "helpers" => "Helpers",
  "mailers" => "Mailers",
  "models" => "Models",
  "presenters" => "Presenters",
  "services" => "Services",
  "serializers" => "Serializers",
  "decorators" => "Decorators",
  "concepts" => "Concepts",
  "validators" => "Validators",
  "views" => "Views",
  "partials" => "Partials",
);

foreach ($keys as $key => $title) {
  if ($latest[$key]) {
    echo "<dt>" . $title . "</dt>";
    echo "<dd>" . number_format($latest[$key]) . "</dd>";
  }
}

?>
</dl>

<?php

$graphs = array(
  "controllers" => "Controllers",
  "models" => "Models",
  "views" => "Views",
  "partials" => "Partials",
);

?>

<?php foreach ($graphs as $key => $title) { ?>
  <h2><?php echo htmlspecialchars($title); ?></h2>

  <?php

  $rows = array();
  foreach ($database['commits'] as $commit) {
    $date = $commit['author_date'];

    if (!isset($database['rails'][$commit['hash']])) {
      // ignore PHP parse errors
      continue;
    }

    $value = $database['rails'][$commit['hash']][$key];
    $rows[date('Y-m-d', strtotime($date))] = array($date, $value);
  }

  $this->renderLineChart($rows, "chart_" . $key, $title, $width, $height);

  ?>
<?php } ?>
