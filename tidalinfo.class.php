<?Php
require 'vendor/autoload.php';
class TidalInfo
{
    /**
     * @var string Token sent in header X-Tidal-Token
     */
	public $token;
    /**
     * @var string SessionId sent in header X-Tidal-SessionId
     */
    public $sessionId;
	public $countryCode='NO';
	public $ch;

    /**
     * Init cURL
     * @throws Exception
     * @deprecated
     */
	function init_curl()
	{
	}

    /**
     * Query TIDAL
     * @param $url
     * @param array $post_data POST data
     * @param array $headers Extra HTTP headers
     * @return string
     */
	function query($url, $post_data=null, $headers=null)
	{
        if(empty($url))
            throw new InvalidArgumentException('Missing URL');
        if(!empty($this->token))
            $headers['X-Tidal-Token'] = $this->token;
        if(!empty($this->sessionId))
            $headers['X-Tidal-SessionId'] = $this->sessionId;

        //$options = array('useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0');
        $options['useragent'] = 'TIDAL/1968 CFNetwork/978.0.7 Darwin/18.5.0';

		if(empty($post_data))
            $response = Requests::get($url, $headers, $options);
		else
			$response = Requests::post($url, $headers, $post_data, $options);

		return $response->body;
	}

    /**
     * Parse the response from TIDAL
     * @param string $data JSON string with data from TIDAL
     * @return array Parsed JSON data
     * @throws TidalError Error message from TIDAL
     */
	function parse_response($data)
	{
		if(!is_string($data))
			throw new InvalidArgumentException('Data must be string');
		$info=json_decode($data,true);
		$info = $this->image($info);

		if(isset($info['userMessage']))
            throw new TidalError($info['userMessage']);
		else
			return $info;
	}

    /**
     * Complete image URLs
     * @param array $info
     * @return array
     */
	public static function image($info)
	{
		if(isset($info['cover']))
			$info['cover']='http://resources.wimpmusic.com/images/'.str_replace('-','/',$info['cover']).'/640x640.jpg';
		elseif(isset($info['image']))
			$info['image']='http://resources.wimpmusic.com/images/'.str_replace('-','/',$info['image']).'/640x428.jpg';
		return $info;
	}

    /**
     * Get token
     * @param string $url
     * @return string Token
     * @throws TidalError Token not found in response string
     */
    function get_token($url='https://tidal.com/browse/')
    {
        echo "Get token from $url\n";
        $response=Requests::Get($url);
        preg_match('/api\.tidalhifi\.com.+token=([a-zA-Z0-9]+)/', $response->body,$token);
        if(empty($token[1]))
            throw new TidalError('Token not found in response string');
        return $token[1];
    }

    /**
     * Get id from URL
     * @param $id_or_url
     * @param string $topic
     * @return string id
     */
	public static function get_id($id_or_url,$topic='')
	{
		if(is_numeric($id_or_url))
			return $id_or_url;
		elseif(!preg_match(sprintf('#%s/([0-9]+)#',$topic),$id_or_url,$id))
			throw new InvalidArgumentException('Invalid %s URL: %s', $topic, $id_or_url);
		else
			return $id[1];
	}

    /**
     * Send request to the TIDAL API
     * @param string $topic Valid values: albums, tracks, playlists, artists
     * @param string $id ID of the object to get
     * @param string $field Valid values: tracks, contributors or empty
     * @param string $url_extra Value is appended to URL
     * @return array Response from TIDAL
     * @throws TidalError TIDAL returned error or unable to get token
     */
	function api_request($topic,$id,$field='',$url_extra='')
	{
		//Can use sessionId or token
        if(empty($this->token))
        {
            $web_url = sprintf('https://tidal.com/%s/%s', rtrim($topic, 's'), $id);
            $this->token = $this->get_token($web_url);
        }

        $headers = array(
            'Accept' => '*/*',
            'Accept-Encoding' => 'br, gzip, deflate',
            'Accept-Language' => 'en-us',
            'Connection' => 'keep-alive');

		$url=sprintf('https://api.tidal.com/v1/%s/%s/%s?countryCode=%s%s',$topic,$id,$field,$this->countryCode,$url_extra);
		return $this->parse_response($this->query($url, null, $headers));
	}

    /**
     * Get information about an album
     * @param string $album Album ID or URL
     * @param bool $tracks Get album tracks
     * @return array Information about the album
     * @throws TidalError API request failed
     */
	function album($album,$tracks=false)
	{
		if($tracks)
			$field='tracks';
		else
			$field='';
		$id=$this->get_id($album,'album');
		return $this->api_request('albums',$id,$field);
	}

    /**
     * Get information about a track
     * @param string $track Track ID or URL
     * @return array Information about the track
     * @throws TidalError API request failed
     */
	function track($track)
	{
		$id=$this->get_id($track,'track');
		return $this->api_request('tracks',$id,'');
	}

    /**
     * Get information about a playlist
     * @param string $id Playlist id
     * @return array Information about the playlist
     * @throws TidalError API request failed
     */
	function playlist($id)
	{
 		$playlist_info=$this->api_request('playlists',$id);
		$limit=ceil($playlist_info['numberOfTracks']/100)*100;
 		$playlist_tracks=$this->api_request('playlists',$id,'tracks',"&limit=$limit&orderDirection=ASC");
		return array_merge($playlist_info,$playlist_tracks);
	}

    /**
     * Get ISRCs for the tracks on an album
     * @param string $album Album ID or URL
     * @return array
     * @throws TidalError API request failed
     */
	function album_isrc($album)
	{
		$album_info=$this->album($album,true);
		$isrc_list=array();
		foreach($album_info['items'] as $track)
		{
			$track_number=$track['volumeNumber'].'-'.$track['trackNumber'];
			$isrc_list[$track_number]=$track['isrc'];
		}
		return $isrc_list;
	}
}