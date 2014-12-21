<div id="<?php echo $id; ?>"></div>

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
    var data = new google.visualization.DataTable();
    data.addColumn('date', 'Date');
    data.addColumn('number', 'LOC');

    data.addRows([
      <?php
      $first = true;
      foreach ($rows as $row) {
        if (!$first) {
          echo ",";
        }
        $first = false;
        $date = strtotime($row[0]);
        echo "[new Date(", date('Y', $date), ", ", date('m', $date), "-1, ", date('d', $date) . "), " . $row[1] . "]";
      }
      ?>
    ]);

    // Set chart options
    var options = {
      'width': 600,
      'height': 300,
      'chartArea': {'width': '80%', 'height': '70%', 'left': 50, 'top': 25}
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.LineChart(document.getElementById("<?php echo $id; ?>"));
    chart.draw(data, options);
  }
</script>
