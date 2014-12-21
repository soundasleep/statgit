<h1>Development statistics for <?php echo $stats['summary']['name']; ?></h1>

<dl>
  <dt>Generated</dt>
  <dd><?php echo date('r'); ?></dd>

  <dt>Latest revision</dt>
  <dd><?php echo $this->linkRevision($stats['summary']['last_hash']); ?></dd>

  <dt>Report period</dt>
  <dd><?php echo date('Y-m-d', strtotime($stats['summary']['first_commit'])); ?> to <?php echo date('Y-m-d', strtotime($stats['summary']['first_commit'])); ?></dd>

  <dt>Total files</dt>
  <dd><?php echo number_format($stats['summary']['total_files']); ?></dd>

  <dt>Total lines of code</dt>
  <dd><?php echo number_format($stats['summary']['total_loc']); ?></dd>

  <dt>Developers</dt>
  <dd><?php echo number_format($stats['summary']['author_count']); ?></dd>

</dl>

<h2>Lines of Code</h2>

<div id="chart_loc"></div>

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

      $rows = array();
      foreach ($database['commits'] as $commit) {
        $date = $commit['author_date'];
        $loc = $this->getTotalLoc($database['stats'][$commit['hash']]);
        $rows[date('Y-m-d', strtotime($date))] = array($date, $loc);
      }

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
      'chartArea': {'width': '80%', 'height': '70%', 'left': 0, 'top': 0}
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.LineChart(document.getElementById('chart_loc'));
    chart.draw(data, options);
  }
</script>
