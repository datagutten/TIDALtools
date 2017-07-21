<?php
require 'tidalinfo.class.php';
class tidalsearch extends tidalinfo
{
	function track($artist,$title)
	{
		echo "Tittel: ".$title."\n";
		$search=preg_replace('/(.+) \(?feat.+/','$1',$title,-1,$count); //Search with plain title
		echo "Search: ".$search."\n";
		$artist_plain=preg_replace('/(.+) feat.+/','$1',$artist,-1,$count);
		//var_dump($artist_plain);
		$artist_plain=preg_replace('/(.+?) &.+/','$1',$artist_plain,-1,$count);
		//var_dump($artist_plain);
		$matches=$this->api_request('search','tracks','','&limit=20&query='.urlencode($search));
		if($matches===false)
			return false;
		echo sprintf("Artist_plain: %s\n",$artist_plain);
		$artist_plain=str_replace(', ',"\n",$artist_plain); //Separate artists by line break instead of comma
		foreach($matches['items'] as $item)
		{
			//print_r($item);
			$artists=implode("\n",array_column($item['artists'],'name')); //Create a string from the search result artist array
			if(mb_stripos($artists,$artist_plain)!==false)
			{
				$match=$item;
				break;
			}
			else
				unset($match);
		}
	}
}