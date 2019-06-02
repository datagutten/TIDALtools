<?Php
class tidalinfo
{
	public $token;
	public $countryCode='NO';
	public $ch;

    /**
     * Init cURL
     * @throws Exception
     */
	function init_curl()
	{
		$this->ch=curl_init();
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0');
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($this->ch,CURLOPT_ENCODING,'gzip');
		curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('X-Tidal-Token: '.$this->get_token()));
	}

    /**
     * Query TIDAL
     * @param $url
     * @param bool $postfields
     * @return bool|string
     * @throws Exception
     */
	function query($url,$postfields=false)
	{
		if(!is_resource($this->ch))
			$this->init_curl();
		if(empty($url))
			throw new Exception('Missing URL');
		if($postfields===false)
			curl_setopt($this->ch,CURLOPT_HTTPGET,true);
		else
			curl_setopt($this->ch,CURLOPT_POSTFIELDS,http_build_query($postfields));
		curl_setopt($this->ch,CURLOPT_URL,$url);
		$data=curl_exec($this->ch);
		if($data===false)
			throw new Exception('cURL error: '.curl_error($this->ch));
		else
			return $data;
	}

    /**
     * Parse the response from TIDAL
     * @param string $data JSON string with data from TIDAL
     * @return array Parsed JSON data
     * @throws Exception
     */
	function parse_response($data)
	{
		if(!is_string($data))
			throw new InvalidArgumentException('Data must be string');
		$info=json_decode($data,true);
		$info = $this->image($info);

		if(isset($info['userMessage']))
            throw new Exception($info['userMessage']);
		else
			return $info;
	}

    /**
     * Complete image URLs
     * @param array $info
     * @return array
     */
	function image($info)
	{
		if(isset($info['cover']))
			$info['cover']='http://resources.wimpmusic.com/images/'.str_replace('-','/',$info['cover']).'/640x640.jpg';
		elseif(isset($info['image']))
			$info['image']='http://resources.wimpmusic.com/images/'.str_replace('-','/',$info['image']).'/640x428.jpg';
		return $info;
	}

    /**
     * Get token
     * @return string Token
     * @throws Exception
     */
	function get_token()
	{
		if(!is_resource($this->ch))
			$this->init_curl();
		$data=$this->query('http://tidal.com/scripts/scripts.28290a4f.js');
		preg_match('/this.token="(.+)"/U',$data,$token);
		if(empty($token[1]))
			throw new Exception('Unable to get token');
		return $token[1];
	}

    /**
     * Get id from URL
     * @param $id_or_url
     * @param string $topic
     * @return string id
     */
	function get_id($id_or_url,$topic='')
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
     * @param $topic
     * @param $id
     * @param string $field
     * @param string $url_extra
     * @return array Response from TIDAL
     * @throws Exception
     */
	function api_request($topic,$id,$field='',$url_extra='')
	{
		//Topic can be: albums, tracks, playlists, artists
		//Field can be: tracks, contributors or empty

		//Can use sessionId or token

		$url=sprintf('http://api.tidalhifi.com/v1/%s/%s/%s?countryCode=%s%s',$topic,$id,$field,$this->countryCode,$url_extra);
		return $this->parse_response($this->query($url));	
	}

    /**
     * Get information about an album
     * @param string $album Album ID or URL
     * @param bool $tracks Get album tracks
     * @return array
     * @throws Exception
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
     * @return array
     * @throws Exception
     */
	function track($track)
	{
		$id=$this->get_id($track,'track');
		return $this->api_request('tracks',$id,'');
	}

    /**
     * Get information about a playlist
     * @param $id
     * @return array
     * @throws Exception
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
     * @param $album
     * @return array
     * @throws Exception
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