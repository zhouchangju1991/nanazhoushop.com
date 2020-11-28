<?php 
	require __DIR__.'/vendor/autoload.php';
	use Google\Cloud\Firestore\FirestoreClient;

	$database = new FirestoreClient([
		'projectId' => 'nanazhou-shop',
	]);
?>
