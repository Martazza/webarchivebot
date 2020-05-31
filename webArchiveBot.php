<?php

	if(count($argv) < 2){
		echo '--help for help';
		return;
	}
	$opt = [
		"blacklist:"
		"tor"
		"help"
	];
	
	$options = getopt(null, $opts);
	$BLACKLIST = $opt["blacklist"] ?? false;

	
	$alreadyVisited = [];
	$blacklist = [];

	if( $BLACKLIST ){
		$filename = $BLACKLIST;
		// Open the file
		$fp = fopen($filename, 'r'); 
		$blacklist = [];

		if ($fp) {
		   $blacklist = explode("\n", fread($fp, filesize($filename)));
		}
	}
	
	$URI_START = $argv[1];
	matchAllThese($URI_START);
	
	function matchAllThese($uri){
		global $alreadyVisited;
		$regex = '/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/=]*)/m';

		if($BLACKLIST && isBlacklisted($uri)){
			echo "\033[1;33m[Blacklisted] $uri\n\033[0m";
			return;	
		}
		if(in_array($uri, $alreadyVisited)){
			echo "\033[1;33mAlready Visited: $uri\n\033[0m";
			return;
		}
		$alreadyVisited[] = $uri;
 
		$str = file_get_contents("https://web.archive.org/save/$uri");
		preg_match_all($regex, $str, $matches, PREG_SET_ORDER, 0);

		foreach($matches as $match){
			$newUrl = $match[0];
			matchAllThese($newUrl);
		}
	}
	
	function isBlacklisted($url){
		global $blacklist;
		$url_info = parse_url($url);
		$host = $url_info['host'];
		// If the ip is matched, return true
		if(in_array($host, $blacklist)) {
			return true;
		}
		return false;
	}
		
?>