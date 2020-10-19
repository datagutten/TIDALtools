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

		if($response->status_code>=400 && $response->status_code<=499)
			$this->parse_response($response->body);
		elseif($response->success===false)
			throw new TidalError('HTTP request unsuccessful: '.$response->body);

		return $response->body;
	}

    /**
     * Parse the response from TIDAL
     * @param string $data JSON string with data from TIDAL
     * @return array Parsed JSON data
     * @throws TidalError Error message from TIDAL
     */
	public static function parse_response(string $data)
	{
		if(!is_string($data))
			throw new InvalidArgumentException('Data must be string');
		$info=json_decode($data,true);
		$info = self::resolve_image($info);

		if(isset($info['userMessage']))
            throw new TidalError($info['userMessage']);
		if(isset($info['errors']))
			throw new TidalError($info['errors'][0]['message']);
		else
			return $info;
	}

    /**
     * Complete image URLs
     * @param array $info
     * @return array
     */
	public static function resolve_image(array $info)
	{
	    /*
	     * Cover: Album 640x640
	     * Picture: Artist 320x320
	     * Image: Playlist 640x428
	     */
	    $pairs = array('cover'=>'640x640', 'picture'=>'320x320', 'image'=>'640x428');
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
     * @return string Token
     * @throws TidalError Token not found in response string
     */
    public static function get_token()
    {
        try {
            $url = 'https://listen.tidal.com/app.5a3cbbd2c3c151b833cb.chunk.js';
            $response = Requests::Get($url);
            $response->throw_for_status();
        }
        catch (Requests_Exception $e)
        {
            throw new TidalError($e->getMessage(), 0, $e);
        }
        preg_match('/r=window\.TIDAL_CONFIG.+l\?.+:"(.+)"\)/U', $response->body, $token);
        if(empty($token[1]))
            throw new TidalError('Token not found in response string');
        return $token[1];
    }

    /**
     * Get id from URL
     * @param $id_or_url
     * @param string $topic
     * @return int|string id
     */
    public static function get_id($id_or_url, $topic = '')
    {
        if (empty($id_or_url))
            throw new InvalidArgumentException('Empty argument');
        if (is_numeric($id_or_url))
            return $id_or_url;
        elseif (preg_match('#playlist/([a-f0-9-]+)#', $id_or_url, $id))
            return $id[1];
        elseif (!empty($topic))
        {
            if (preg_match(sprintf('#%s/([0-9]+)#', $topic), $id_or_url, $id))
                return (int)$id[1];
            else
                throw new InvalidArgumentException(sprintf('Invalid %s URL: %s', $topic, $id_or_url));
        }
        elseif (preg_match(sprintf('#%s/([0-9]+)#', $topic), $id_or_url, $id))
            return (int)$id[1];
        else
            throw new InvalidArgumentException('Unable to find id from URL: ' . $id_or_url);
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
	function api_request(string $topic, string $id, $field='', $url_extra='')
	{
		//Can use sessionId or token
        if(empty($this->token))
        {
            $web_url = sprintf('https://tidal.com/browse/%s/%s', rtrim($topic, 's'), $id);
            try {
                $this->token = $this->get_token();
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
	function album(string $album,$tracks=false)
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
	function track(string $track)
	{
		$id=$this->get_id($track,'track');
		return $this->api_request('tracks',$id,'');
	}

    /**
     * Get information about a playlist
     * @param string $id_or_url Playlist id or URL
     * @return array Information about the playlist
     * @throws TidalError API request failed
     */
	function playlist(string $id_or_url)
	{
	    $id = self::get_id($id_or_url);
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
	function artist(string $artist)
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
    function artist_albums(string $artist)
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
	function album_isrc(string $album)
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

    /**
     * Prepare metadata from TIDAL to be passed to AudioMetadata methods
     * @param array $track Return value from Info::Track
     * @param array $album Return value from Info::Album or Info::playlist
     * @param bool $is_playlist Is playlist
     * @return array
     */
    public static function prepare_metadata(array $track, array $album, $is_playlist = false)
    {
        $track['artist'] = $track['artist']['name'];
        if (!$is_playlist)
        {
            $track['albumyear'] = date('Y', strtotime($album['releaseDate']));
            $track['album'] = $track['album']['title'];
            $track['albumartist'] = $album['artist']['name'];
            $track['tracknumber'] = $track['trackNumber'];
            $track['volumenumber'] = $track['volumeNumber'];
            $track['totaltracks'] = $album['numberOfTracks'];
            $track['totalvolumes'] = $album['numberOfVolumes'];
            $track['cover'] = $album['cover'];
            if ($album['artist']['id'] == 2935) //If album artist is "Various Artists" the album is a compilation
                $track['compilation'] = true;
            if (empty($track['year']) && preg_match('/([0-9]{4})/', $track['copyright'], $year))
                $track['year'] = $year[1];
        }
        else
        {
            $track['album'] = $album['title'];
            unset($track['tracknumber']);
            $track['totaltracks'] = $album['numberOfTracks'];
            $track['cover'] = $album['image'];
            $track['compilation'] = true; //Playlists are always compilations
        }
        return $track;
    }

    /**
     * Prepare metadata from TIDAL to be passed to AudioMetadata methods
     * @param string $track Track ID or URL
     * @param bool $playlist Part of a playlist
     * @return array Metadata
     * @throws TidalError
     */
    public function track_metadata(string $track, $playlist = false)
    {
        $track_info = $this->track($track);
        $album_info = $this->album($track_info['album']['id']);
        return self::prepare_metadata($track_info, $album_info, $playlist);
    }
}