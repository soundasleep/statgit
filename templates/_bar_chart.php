<div id="<?php echo $id; ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;"></div>

<script type="text/javascript">
  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart() {

    // Create the data table.
    var data = google.visualization.arrayToDataTable([
      ['Date', <?php echo json_encode($heading); ?>]
      <?php foreach ($rows as $row) {
        $date = strtotime($row[0]);
        array_shift($row);
        # echo ",\n[new Date(", date('Y', $date), ", ", date('m', $date), "-1, ", date('d', $date) . "), " . implode(", ", $row) . "]";
        echo ",\n[" . json_encode(date('Y-m-d', $date)) . ", " . implode(", ", $row) . "]";
      } ?>
    ]);

    // Set chart options
    var options = {
      width: <?php echo $width; ?>,
      height: <?php echo $height; ?>,
      hAxis: {title: 'Date'},
      vAxis: {title: <?php echo json_encode($heading); ?>, minValue: 0},
      chartArea: {width: '70%', height: '70%', left: 100, top: 25}
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.ColumnChart(document.getElementById("<?php echo $id; ?>"));
    chart.draw(data, options);
  }
</script>
