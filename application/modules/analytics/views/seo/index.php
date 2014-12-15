<?php

$has_records	= isset($GA_data) && is_array($GA_data) && count($GA_data);

	if ($has_records) :
		$GA_users = $GA_data['GA_users'];
		$GA_new_visitor = $GA_users->rows[0][1];
		$GA_returning_visitor = $GA_users->rows[1][1];

		$GA_visitor_total = $GA_new_visitor + $GA_returning_visitor;
?>
      <div class="row">
        <div class="span3" id="pie_chart_div"></div>
        <div class="span8" id="line_chart_div"></div>
      </div>
      <div class="row">
      <table class="table table-striped">
            <thead>
              <tr>
                <th>Browser</th>
                <th>Visitors</th>
              </tr>
            </thead>
            <tbody>
              <?php
            foreach ($GA_data['GA_browsers']['rows'] as $browser) {
              echo '<tr>';
              echo '<td>' . $browser[0] . '</td>';
              echo '<td>' . $browser[1] .'</td>';
              echo '</tr>';
            }

              ?>
            </tbody>
            </table>
      </div>

<!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(draw_Visitor_PieChart);
      google.setOnLoadCallback(draw_Page_Session_lineChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function draw_Visitor_PieChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Visitor');
        data.addColumn('number', 'Number');
        data.addRows([
          ['New Visitor', <?php echo number_format($GA_new_visitor); ?>],
          ['Returning Visitor', <?php echo number_format($GA_returning_visitor); ?>],
        ]);

        // Set chart options
        var options = {
        				'legend':'bottom',
                       'width':350,
                       'height':400};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('pie_chart_div'));
        chart.draw(data, options);
      }

      function draw_Page_Session_lineChart() {
        var data = google.visualization.arrayToDataTable([
          ['Day', 'Pageviews', 'Sessions'],
          <?php

            foreach ($GA_data['GA_visitors_day']['rows'] as $visitor_session) {
              echo "['".$visitor_session[0]."', ".$visitor_session[1].", ".$visitor_session[2]."],\r\n";
            }

          ?>
        ]);

        var options = {
          title: 'Metrics: Sessions/Pageviews',
          hAxis: {title: 'Day',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0},
          pointSize: 5,
          width: '1250',
          height: '350',
          legend: 'bottom'
        };

        var chart = new google.visualization.AreaChart(document.getElementById('line_chart_div'));
        chart.draw(data, options);
      }

    </script>
<?php
	else:
?>
No data to display.
<?php endif; ?>