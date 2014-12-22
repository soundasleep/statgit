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
    var data = data = google.visualization.arrayToDataTable([
      [<?php echo json_encode($title); ?>, "Value"]
      <?php
      $first = true;
      foreach ($rows as $key => $row) {
        $first = false;
        echo ", [" . json_encode($key) . ", " . $row . "]";
      }
      ?>
    ]);

    // Set chart options
    var options = {
      title: <?php echo json_encode($title); ?>,
      width: <?php echo $width; ?>,
      height: <?php echo $height; ?>,
      chartArea: {width: '80%', height: '80%', left: 25, top: 25}
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById("<?php echo $id; ?>"));
    chart.draw(data, options);
  }
</script>
