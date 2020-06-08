#!/usr/bin/php
<?php
	require 'utils/connector.php';
	
	$SCRIPT_FILENAME = $argv[0];
	if(count($argv) < 2){
		echo '--help for help';
		return;
	}
	
	$opts = [
		"url:",
		"blacklist:",
		"tor",
		"help"
	];
	
	$options = getopt(implode("", $opts), $opts);
	$HELP_OPT = isset($options["help"]) ?? null;

	if( $HELP_OPT ) {
		echo "Usage:\t$SCRIPT_FILENAME [OPTIONS] [URL]\n";
		echo "Options:\n";
		echo "\t\t--blacklist\n\t\t\tFile with a list of website to ignore, each line must contain only one URL!\n";
		echo "\t\t--help\n\t\t\tShows this message\n";
		echo "\t\t--tor\n\t\t\tEvery request uses a TOR Proxy to retrieve data.\n";
		echo "\t\t--url\n\t\t\tOverride the url given\n";
		return;
	}
	
	$BLACKLIST_OPT = $options["blacklist"] ?? null;
	$TOR_OPT = isset($options["tor"]) ?? null;
	$REGEX_URLS = '/https?:\/\/((www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/=]*))/m';
	$URI_START = $options["url"] ?? $argv[$argc - 1];
	$ALREADY_VISITED_URLS = [];
	$blacklist = [];

	if( $BLACKLIST_OPT ){
		echo "BLACKLIST $BLACKLIST_OPT";

		$filename = $BLACKLIST_OPT;
		// Open the file
		$fp = fopen($filename, 'r'); 
		$blacklist = [];

		if ($fp) {
		   $blacklist = explode("\n", fread($fp, filesize($filename)));
		}
	}

	matchAllThese($URI_START);

	function matchAllThese($uri){
		global $TOR_OPT;
		global $ALREADY_VISITED_URLS;
		global $REGEX_URLS;
		global $BLACKLIST_OPT;

		if( $BLACKLIST_OPT && isBlacklisted( $uri ) ){
			echo "\033[1;33m[Blacklisted] $uri\n\033[0m";
			return;	
		}
		$isUriVisited = in_array( $uri, $ALREADY_VISITED_URLS);
		if( $isUriVisited ){
			echo "\033[1;33mAlready Visited: $uri\n\033[0m";
			return;
		}
		
		//Add to alreadyVisited
		$ALREADY_VISITED_URLS[] = $uri;

		$connector = new Connector("https://web.archive.org/save/$uri", null);
		$str = $connector->connect( $TOR_OPT );
		preg_match_all( $REGEX_URLS, $str, $matches, PREG_SET_ORDER, 0);
		
		

		//Follow all links
		foreach($matches as $match){
			$newUrl = $match[1];
			matchAllThese($newUrl);
		}
	}
	
	function isBlacklisted( $url ){
		global $blacklist;
		$url_info = parse_url( $url );
		$host = $url_info['host'];
		// If the ip is matched, return true
		return in_array( $host, $blacklist );
	}
		
?>
