<?php
require_once 'TIDALtools/tidalinfo.class.php';
class tidalsearch extends tidalinfo
{
	function __construct()
	{
		parent::init_curl();
	}
	//Remove featured artists from title
	function clean_title($title)
	{
		return preg_replace('/(.+) \(?feat.+/','$1',$title,-1,$count); //Search with plain title
	}
	function clean_artist($artist)
	{
		$artist_plain=preg_replace('/(.+) feat.+/','$1',$artist,-1,$count);
		//var_dump($artist_plain);
		$artist_plain=preg_replace('/(.+?) &.+/','$1',$artist_plain,-1,$count);
		return $artist_plain;
	}
	function search_track($search)
	{
		return $this->api_request('search','tracks','','&limit=20&query='.urlencode($search));
	}
	function search_album($search)
	{
		return $this->api_request('search','albums','','&limit=20&query='.urlencode($search));
	}
	
	function verify_artist($search_result,$wanted_artist)
	{
		$wanted_artist=str_replace(', ',"\n",$wanted_artist); //Separate artists by line break instead of comma
		foreach($matches['items'] as $item)
		{
			//print_r($item);
			$artists=implode("\n",array_column($item['artists'],'name')); //Create a string from the search result artist array
			if(mb_stripos($artists,$wanted_artist)!==false) //Current search result has the wanted artist
			{
				return $item;
			}
		}
	}
}
