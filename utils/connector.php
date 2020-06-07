<?php
/**
 * Php Web Connector
 *
 * @author : Martazza;
 * @version : 1.0;
 */
class Connector {
    //Local TOR
    private $ip = '127.0.0.1';
    // $Port     | https://www.torproject.org/docs/faq.html.en#TBBSocksPort
    private $Port = '9050';
    // $AuthPass | tor --hash-password PASSWORD
    private $AuthPass = '';
    // Limits the maximum execution time
    private $Timeout = 60;
    private $url;
    private $debug;
    public function __construct($url, $debug = null) {
        $this->url = $url;
        $this->debug = $debug;
    }
    public function connect($useTor = null) {
        echo "Fetching: " . $this->url . "\n";
        $request = curl_init();
        if ($useTor) {
            $this->switchIdentity();
            curl_setopt($request, CURLOPT_PROXY, $this->ip . ':' . $this->Port);
            curl_setopt($request, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        curl_setopt($request, CURLOPT_URL, $this->url);
        curl_setopt($request, CURLOPT_USERAGENT, $this->UserAgent());
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($request, CURLOPT_TIMEOUT, $this->Timeout);
        $response = curl_exec($request);
        $error = curl_error($request);
        if ($response === false) {
            echo "[ERROR]$error";
        }
        curl_close($request);
        return $response;
    }
    /**
     Get a random user agent
     */
    private function UserAgent() {
        $agentBrowser = array(
            'Firefox',
            'Edge',
            'UC Browser',
            'Konqueror',
            'Opera',
            'Flock',
            'Internet Explorer',
            'Seamonkey',
            'Safari',
            'GoogleBot'
        );
        //list of operating systems
        $agentOS = array(
            'Windows NT',
            'Windows XP',
            'Windows Vista',
            'Redhat Linux',
            'Ubuntu',
            'Fedora',
            'AmigaOS',
            'OS 10.5'
        );
        //generate UserAgent
        return $agentBrowser[rand(0, sizeof($agentBrowser) - 1) ] . '/' . rand(1, 8) . '.' . rand(0, 9) . ' (' . $agentOS[rand(0, sizeof($agentOS) - 1) ] . ' ' . rand(1, 7) . '.' . rand(0, 9) . '; en-US;)';
    }
    private function switchIdentity() {
        if ($this->debug) {
            echo "[DEBUG]Switching TOR Identity\n";
        }
        $command = 'signal NEWNYM';
        $fd = fsockopen($this->ip, $this->Port, $ErrorNum, $ErrorStr, 10);
        if ($fd) {
            fwrite($fd, "AUTHENTICATE \"" . $this->AuthPass . "\"\n");
            fwrite($fd, $command . '\n');
        } else {
            echo "Error while switching identity:\n$ErrorNum : $ErrorStr\n";
            return false;
        }
    }
}
?>