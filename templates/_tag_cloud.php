<ul class="tag_cloud">
<?php
$max = 1;
$min = 1;
foreach ($tags as $value) {
  $max = max($max, $value);
  $min = min($min, $value);
}

ksort($tags);

foreach ($tags as $word => $count) {
  echo "<li class=\"" . $this->generateMinMaxClass($min, $max, $count) . "\">" . htmlspecialchars($word) . "</li>";
}
?>
</ul>