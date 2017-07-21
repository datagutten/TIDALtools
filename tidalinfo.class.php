<?Php
class tidalinfo
{
	public $token;
	public $countryCode='NO';
	public $ch;
	function init_curl()
	{
		$this->ch=curl_init();
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0');
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($this->ch,CURLOPT_ENCODING,'gzip');
		curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('X-Tidal-Token: '.$this->get_token()));
	}
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
		{
			$this->error='cURL error: '.curl_error($this->ch);
			return false;
		}
		else
			return $data;
	}
	function parse_response($data)
	{
		if($data===false)
			return false;
		$info=json_decode($data,true);
		if(isset($info['cover']))
			$info['cover']='http://resources.wimpmusic.com/images/'.str_replace('-','/',$info['cover']).'/640x640.jpg';
		elseif(isset($info['image']))
			$info['image']='http://resources.wimpmusic.com/images/'.str_replace('-','/',$info['image']).'/640x428.jpg';

		if(isset($info['userMessage']))
		{
			$this->error=$info['userMessage'];
			return false;
		}
		else
			return $info;
	}
	function get_token()
	{
		if(!is_resource($this->ch))
			$this->init_curl();
		$data=$this->query('http://tidal.com/scripts/scripts.28290a4f.js');
		preg_match('/this.token="(.+)"/U',$data,$token);
		if(empty($token[1]))
		{
			$this->error='Unable to get token';
			return false;
		}
		return $token[1];
	}
	function get_id($id_or_url,$topic='')
	{
		if(is_numeric($id_or_url))
			return $id_or_url;
		elseif(!preg_match(sprintf('#%s/([0-9]+)#',$topic),$id_or_url,$id))
		{
			$this->error=sprintf('Invalid %s URL',$topic);
			return false;
		}
		else
			return $id[1];
	}
	function api_request($topic,$id,$field='',$url_extra='')
	{
		//Topic can be: albums, tracks, playlists
		//Field can be: tracks, contributors or empty

		//Can use sessionId or token

		$url=sprintf('http://api.tidalhifi.com/v1/%s/%s/%s?countryCode=%s%s',$topic,$id,$field,$this->countryCode,$url_extra);
		return $this->parse_response($this->query($url));	
	}

	function album($album,$tracks=false)
	{
		if($tracks)
			$field='tracks';
		else
			$field='';
		$id=$this->get_id($album,'album');
		if($id===false)
			return false;
		return $this->api_request('albums',$id,$field);
	}
	function track($track)
	{
		$id=$this->get_id($track,'track');
		if($id===false)
			return false;
		return $this->api_request('tracks',$id,'');
	}
	function playlist($id)
	{
 		$playlist_info=$this->api_request('playlists',$id);
		if($playlist_info===false)
			return false;
		$limit=ceil($playlist_info['numberOfTracks']/100)*100;
 		$playlist_tracks=$this->api_request('playlists',$id,'tracks',"&limit=$limit&orderDirection=ASC");
		return array_merge($playlist_info,$playlist_tracks);
	}
	function album_isrc($album)
	{
		$album_id=$this->get_id($album,'album');
		if($album_id===false)
			return false;
		$albuminfo=$this->album($album_id,true);
		if($albuminfo===false)
			return false;
		$tracks=array_column($albuminfo['items'],'trackNumber');
		$isrc=array_column($albuminfo['items'],'isrc');
		return array_combine($tracks,$isrc);
	}
}