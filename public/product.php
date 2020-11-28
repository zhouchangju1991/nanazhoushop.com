<?php 
  session_set_cookie_params(0);
  session_start();
  require('./connect_to_database.php');
	include('./util.php');
    
  $sku = filter_input(INPUT_GET, 'sku');
	$product = $database->collection('hermes_us_product')->document($sku)->snapshot();
	if (!$product->exists()) {
		echo "Product not found";
		exit("Error: product not found.");
	}

  $pattern = Util::title($product['pattern']);
  $color = Util::title($product['color']);
	$name = $pattern . ' - ' . $color;
  $price = $product['price'];
  $desc = $product['description_html'];
  $dimension = $product['dimension'];
  $url = $product['url'];
  $imgLink = sizeof($product['images']) == 0 ? "" : $product['images'][0];
  $published = $product['published'];
	$historical_publish = $product['historical_publish'];
?>

<html>
  <head>
    <meta charset="UTF-8">
    <title><?php echo $pattern; ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
      function render_chart(id, dataPoints) {
        console.log(JSON.stringify((dataPoints, null, 2)));
        var chart = new CanvasJS.Chart(id, {
          animationEnabled: true,
          title: {
						text : "Publish Status By Time"
          },
          axisX: {
            title: "Time",
            gridThickness : 0.5,
						interval : 1, 
            intervalType : "day",
            labelAngle: -20
          }, 
          axisY: {
						title: "0: Unpublish      1: Publish",
            gridThickness : 2, 
            interval : 1,
            maximum : 1
          },
					data: [{
            type: "line",
						markerSize: 10,
						xValueFormatString: "YYYY-MM-DD HH:mm:ss",
            yValueFormatString: "0",
            xValueType: "dateTime",
            dataPoints: dataPoints
            }
					]
        });
                
        chart.render();
        window.setInterval(5);
			}
    </script>
  </head>
  <body>
    <h1 align="center">
			<a class='linkBlackNoUnderLine fontBold' href='<?php echo $url; ?>' target='_blank'>
				<?php echo $name; ?>
			</a>
			(<?php if ($published == 1) { echo "Available"; } else { echo "Unavailable"; } ?>)
    </h1>    
    <table class="hermes_block" width='100%'>
      <tr>
        <td width='28%'></td>
				<td width='20%'>
          <a href='<?php echo $url; ?>' target='_blank'>
						<img src='<?php echo $imgLink; ?>' alt='<?php echo $name; ?>' />
					</a>
        </td>
        <td width='2%'></td>
        <td width='50%'>
          Current Price: $<?php echo $price; ?><br/>
					Color: <?php echo $color; ?><br/>
					Description: <?php echo str_replace('<br />', '. ', $desc); ?><br/>
					<?php echo $dimension; ?><br/>
        </td>
      </tr>
    </table>
    <div id='chart_<?php echo $sku; ?>'  width='90%' height='auto'></div>
    <?php
			$dataPoints = array();
			foreach ($historical_publish as $epoch) {
				$x = $epoch['timestamp']->get()->getTimestamp() * 1000;
				$y = intval($epoch['publish']);
				array_push($dataPoints, [
					'x' => $x,
					'y' => $y,
				]);
			}
			function sort_by_timestamp($a, $b) {
				return $a['x'] < $b['x'] ? -1 : 1;
			}
			usort($dataPoints, "sort_by_timestamp");
			echo "<script>render_chart('chart_" . $sku .
					 "', " . json_encode($dataPoints, JSON_NUMERIC_CHECK) .
					 "); </script>";
		?>
  </body>
</html>
