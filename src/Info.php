<?Php


namespace datagutten\Tidal;
use InvalidArgumentException;
use Requests;
use Requests_Exception;


class Info
{
    /**
     * @var string Token sent in header X-Tidal-Token
     */
	public $token;
    /**
     * @var string SessionId sent in header X-Tidal-SessionId
     */
    public $sessionId;
    /**
     * @var string User-agent
     */
    public $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36';

	public $countryCode='NO';
	public $ch;


    /**
     * Query TIDAL
     * @param $url
     * @param array $post_data POST data
     * @param array $headers Extra HTTP headers
     * @param array $options Options for Requests
     * @throws TidalError
     * @return string
     */
	function query($url, $post_data=array(), $headers=array(), $options = array())
	{
        if(empty($url))
            throw new InvalidArgumentException('Missing URL');
        if(!empty($this->token))
            $headers['X-Tidal-Token'] = $this->token;
        if(!empty($this->sessionId))
            $headers['X-Tidal-SessionId'] = $this->sessionId;

        $options['useragent'] = $this->useragent;

        if(empty($post_data))
            $response = Requests::get($url, $headers, $options);
        else
            $response = Requests::post($url, $headers, $post_data, $options);
        try {
            $response->throw_for_status();
            return $response->body;
        }
        catch (Requests_Exception $e)
        {
            throw new TidalError($e->getMessage(), 0, $e);
        }
	}

    /**
     * Parse the response from TIDAL
     * @param string $data JSON string with data from TIDAL
     * @return array Parsed JSON data
     * @throws TidalError Error message from TIDAL
     */
	public static function parse_response($data)
	{
		if(!is_string($data))
			throw new InvalidArgumentException('Data must be string');
		$info=json_decode($data,true);
		$info = self::resolve_image($info);

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
	public static function resolve_image($info)
	{
	    $pairs = array('cover'=>'640x640', 'picture'=>'320x320');
	    foreach ($pairs as $key=>$size)
        {
            if(isset($info[$key]))
            {
                $path = str_replace('-','/',$info[$key]);
                $info[$key] = sprintf('https://resources.wimpmusic.com/images/%s/%s.jpg', $path,$size);
            }
        }
	    return $info;
	}

    /**
     * Get token
     * @param string $url
     * @return string Token
     * @throws TidalError Token not found in response string
     */
    public static function get_token($url='https://tidal.com/browse/')
    {
        try {
            $response = Requests::Get($url);
            $response->throw_for_status();
        }
        catch (Requests_Exception $e)
        {
            throw new TidalError($e->getMessage(), 0, $e);
        }
        preg_match('/api\.tidal(?:hifi)?\.com.+token=([a-zA-Z0-9]+)/', $response->body,$token);
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
	    if(empty($id_or_url))
            throw new InvalidArgumentException('Empty argument');
		if(is_numeric($id_or_url))
			return $id_or_url;
		elseif(!preg_match(sprintf('#%s/([0-9]+)#',$topic),$id_or_url,$id))
			throw new InvalidArgumentException(sprintf('Invalid %s URL: %s', $topic, $id_or_url));
		else
			return $id[1];
	}

    /**
     * Send request to the TIDAL API
     * @param string $topic Valid values: albums, tracks, playlists, artists
     * @param string $id ID of the object to get
     * @param string $field Valid values: tracks, contributors, albums or empty
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
            try {
                $this->token = $this->get_token($web_url);
            }
            catch (TidalError $e)
            {
                throw new TidalError('Unable to get token from '.$web_url, 0, $e);
            }
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
     * Get artist info
     * @param string $artist Artist ID or URL
     * @return array Artist info
     * @throws TidalError
     */
	function artist($artist)
    {
        $id = self::get_id($artist);
        $url = sprintf('https://api.tidal.com/v1/pages/artist?countryCode=%s&locale=en_NO&deviceType=PHONE&artistId=%s',
            $this->countryCode, $id);
        return $this->parse_response($this->query($url));
    }

    /**
     * Get artist albums
     * @param string $artist Artist ID or URL
     * @return array Artist albums
     * @throws TidalError
     */
    function artist_albums($artist)
    {
        $artist = self::get_id($artist);
        return $this->api_request('artists', $artist, 'albums');
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