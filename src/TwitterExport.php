<?php

require __DIR__."/../../abraham/twitteroauth/twitteroauth/twitteroauth.php";

class TwitterExport
{

    private $screen_name;
    private $local_file;
    private $file_created = false;
    private $api_url;
    private $connection;

    public $count = 10;
    public $tweets;

    const CACHE_DURATION = 600;

    public function __construct()
    {
        $this->api_url = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$this->screen_name."&count=".$this->count;
    }

    public function setScreenName($screen_name)
    {
        $this->screen_name = $screen_name;
    }

    public function setLocalFile($local_file)
    {
        $this->local_file = $local_file;
    }

    public function connect($consumer, $consumer_secret, $token, $token_secret)
    {
    	$this->connection = new TwitterOAuth($consumer, $consumer_secret, $token, $token_secret);
    }

    public function export()
    {
    	if (!file_exists($this->local_file)) {
            touch($this->local_file);
            $this->file_created = true;
        }

        $cache_duration = strtotime('now') - filemtime($this->local_file);

        if ($cache_duration > self::CACHE_DURATION || $this->file_created === true) {
            $this->tweets = json_encode($this->connection->get($this->api_url));

            if ($this->tweets !== "") {
                file_put_contents($this->local_file, $this->tweets);
            } else {
                $this->tweets = file_get_contents($this->local_file);
            }
        } else {
            $this->tweets = file_get_contents($this->local_file);
        }
    }

    public function tweets($json = true)
    {
    	if ($this->tweets == "") {
    		$this->tweets = file_get_contents($this->local_file);
    	}

    	return $json === true ? $this->tweets : json_decode($this->tweets);
    }
}
