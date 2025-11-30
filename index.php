<?php
	include "vendor/autoload.php";
	include "session.php";

	// returns the raw statistics from web server
	function fetch_data(){
		if (!function_exists('curl_init')) {
    	die('cURL is not installed on your server.');
		}

		$env = parse_ini_file(".env");

		// create a new cURL handle
		$ch = curl_init();

		// set the URL of the resource you want to retrieve
		curl_setopt($ch, CURLOPT_URL, $env["URL"]);

		// set the request method to GET
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		// return the response as a string instead of outputting it directly
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: ' . $env["AUTHORIZATION_HEADER"]
		]);

		// execute the request
		$response = curl_exec($ch);

		// close the cURL handle
		curl_close($ch);
		return json_decode($response);
	}

	function compute_statistics($data){
		$statistics = array();
		$average_game_duration = 0;
		foreach ($data->data as $stats){
			$device_id = $stats->device->device_ident;
			if ($device_id == ""){
				$device_id = "Appareil inconnu";
			}
			if (!array_key_exists($device_id , $statistics)){
				$session = new Session($stats->session_id);
				$statistics[$device_id] = array(
					'highest_level' => 0,
					'highscore' => 0,
					'session' => $session
					);
			}
			$user_stats =& $statistics[$device_id];

			if ($user_stats['highest_level'] < $stats->level){
				$user_stats['highest_level'] = $stats->level;
			}
			if ($user_stats['highscore'] < $stats->highscore){
				$user_stats['highscore'] = $stats->highscore;
			}
			$session->set_duration($stats->session_duration);
			$session->set_highest_level($stats->level);
		}
		return $statistics;
	}

	function render($statistics){
		$loader = new Twig\Loader\FilesystemLoader("templates");
		$twig = new Twig\Environment($loader);
		$template = $twig->load("index.html");
		echo $template->render(array(
			"stats"=> $statistics,
		));
	}

	$data = fetch_data();
	$statistics = compute_statistics($data);
	render($statistics);

?>