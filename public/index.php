<?php 
  session_set_cookie_params(0);
  session_start();
  require('./connect_to_database.php');
?>

<html>
  <head>
    <title>Hermes</title>
    <link rel="stylesheet" href="./css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
      $(document).ready(function() {
        $(document).on('click', '.removeBtn', function(){
          var keyword_id = $(this).attr("id").replace("remove_", "");
					var keyword = $(this).attr("keyword");
          if (confirm("You're going to remove Keyword: " + keyword + ". Continue?")) {
						$.post('data_api.php', {mode: 'delete',
							collection: 'hermes_us_keyword',
							keyword_id: keyword_id}, function(data) {
							if (data.success === false) {
                alert("Failed to remove Keyword " + keyword);
              }
						});
            $("#keyword_" + keyword_id + "").remove();
          }
				});
                
        $(document).on('click', '.addBtn', function(){
					var keyword_raw = $('#add_keyword').val().trim();
					if (!keyword_raw) {
						alert("Please enter a valid keyword.");
						return;
					}
					var keyword = keyword_raw.replace(/ +/g, " ");
					var keyword_id = keyword_raw.replace(/ +/g, "_").toLowerCase();

          if (confirm("You're going to add a Keyword: " + keyword + ". Continue?")) {
						$.post('data_api.php', {mode:'add',
							collection: 'hermes_us_keyword',
							keyword_id: keyword_id}, function(data) {
              if (data.success === false) {
								alert("Failed to add Keyword " + keyword);
							} else {
								alert("Succeed! Refresh the page to display the keyword just added.")
							}
            });
          }
        });          
      });
		</script>
	</head>
  <body>
    <h1 align="center">Hermes</h1>
		<table>
			<tr>
				<td>
					<input type='text' id='add_keyword' placeholder='Keyword' required />
				</td>
				<td class='leftSpace4'>
					<button id='add_keyword_btn' class='addBtn' style='width: 150px;' name='add_keyword'>Add Keyword</button>
				</td>
			</tr>
		</table>
    <div id="hermes">
      <?php
			$keyword_query = $database->collection('hermes_us_keyword')->where('is_valid', '==', true);
			foreach ($keyword_query->documents() as $keyword_doc) {
				if (!$keyword_doc->exists()) {
					continue;
				}
				$keyword_id = $keyword_doc->id();
				$keyword = $keyword_doc->data();
				echo "<div class='hermes_block'>" .
					"<div><a class='font18 fontBold linkBlackNoUnderLine' href='product.php?keyword=" . $keyword_id . 
					"' target='_blank'>" . ucwords($keyword['keyword']) .
					"</a></div><div class='btnBlock'><button id='remove_" . $keyword_id . 
					"' class='removeBtn' name='remove_" . $keyword_id .
					"' keyword='" . ucwords($keyword['keyword']) . 
					"'>Remove</button></div>";

				$product_query = $database
					->collection('hermes_us_product')
					->where('keyword', '==', $keyword['keyword']);
				foreach ($product_query->documents() as $product_doc) {
					if (!$product_doc->exists()) {
						continue;
					}
					$product = $product_doc->data();
					$sku = $product['sku'];
					$name = ucwords($product['pattern']) . " - " . ucwords($product['color']);
          $available = $product['published'] ? 'Available' : 'Unavailable';
					echo "<div><a class='linkBlackNoUnderLine' href='product.php?sku=" . $sku .
						"' target='_blank'>" . $name .
						"</a> - " . $available .
						"</div>";
				}
				echo "</div>";
			}	
      ?>
    </div>
  </body>
</html>
