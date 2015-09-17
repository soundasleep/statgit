<h2>Commit Activity</h2>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  if ($commit['author_email'] == $commit_email || $commit_email === false) {
    $date = $commit['author_date'];
    $x = date('Y-m-d', strtotime($date));
    $y = sprintf("%0.2f", date('H', strtotime($date)) + (date('i', strtotime($date)) * (1/60)));

    $rows[] = array($x, $y);
  }
}

$this->renderScatterChart($rows, "Hour", "chart_commits", "Commit Activity", 800, 300);

?>

<h3>Per Day</h3>

<?php

$rows = array(
  'Mon' => 0,
  'Tue' => 0,
  'Wed' => 0,
  'Thu' => 0,
  'Fri' => 0,
  'Sat' => 0,
  'Sun' => 0,
);
foreach ($database['commits'] as $commit) {
  if ($commit['author_email'] == $commit_email || $commit_email === false) {
    $date = $commit['author_date'];
    $key = date('D', strtotime($date));
    $rows[$key] += 1;
  }
}

$this->renderHistogramChart($rows, "Commits", "chart_commits_day", "Commit Activity per Day", 400, 300);

?>

<h3>Per Hour</h3>

<?php

$rows = array();
for ($i = 0; $i <= 23; $i++) {
  $rows[$i . "h"] = 0;
};
foreach ($database['commits'] as $commit) {
  if ($commit['author_email'] == $commit_email || $commit_email === false) {
    $date = $commit['author_date'];
    $key = date('G', strtotime($date)) . "h";
    $rows[$key] += 1;
  }
}

$this->renderHistogramChart($rows, "Commits", "chart_commits_hour", "Commit Activity per Hour", 800, 300);

?>

<h3>Per Week</h3>

<?php

$rows = array();
foreach ($database['commits'] as $commit) {
  if ($commit['author_email'] == $commit_email || $commit_email === false) {
    $date = $commit['author_date'];
    $key = date('Y-W', strtotime($date));
    $rows[$key] += 1;
  }
}

ksort($rows);

$this->renderHistogramChart($rows, "Commits", "chart_commits_week", "Commit Activity per Week", 600, 300);

?>
