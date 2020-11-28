<?php
    session_set_cookie_params(0);
    session_start();
    require('./connect_to_database.php');
    
    $mode = filter_input(INPUT_POST, 'mode');
    $collection = filter_input(INPUT_POST, 'collection');
    $keyword_id = strval(filter_input(INPUT_POST, 'keyword_id'));

		$time = new DateTime();
		$time->setTimezone(new DateTimeZone('America/Los_Angeles'));
    if ("delete" == $mode) {
			$database->collection($collection)
						->document($keyword_id)
						->set([
							'is_valid' => false,
							'updated_at' => $time,
						], ['merge' => true]);
		}
		if ("add" == $mode) {
			$data = [
				'is_valid' => true,
				'updated_at' => $time,
			];
			$doc = $database->collection($collection)->document($keyword_id);
			if (!$doc->exists) {
				$keyword = str_replace('_', ' ', $keyword_id);
				$data['created_at'] = $time;
				$data['keyword'] = $keyword;
			}
			$doc->set($data);
		}
    
    header("Content-type: application/json; charset=utf-8");
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
