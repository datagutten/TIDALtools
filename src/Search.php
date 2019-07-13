<?php


namespace datagutten\Tidal;
use InvalidArgumentException;


class Search extends Info
{
    /**
     * @var bool Show debug output
     */
    public $debug = false;

    /**
     * Remove featured artists from title
     * @param string $title "Kem Kan Eg Ringe (feat. Store P & Lars Vaular)"
     * @return string "Kem Kan Eg Ringe"
     */
	public static function remove_featured($title)
	{
        $title=preg_replace('/(.+) \(?feat.+/i','$1', $title);
        $title=preg_replace('/(.+) \(?with.+/i','$1',$title);
        return $title;
	}

    /**
     * @param string $search Search string
     * @param int $limit Limit for first query
     * @return array Search results
     * @throws TidalError
     */
	function search_track($search, $limit = 60)
	{
        $matches=$this->api_request('search','tracks','',sprintf('&limit=%d&query=%s',$limit ,urlencode($search)));
        if($matches['totalNumberOfItems']>60)
        {
            $matches=$this->api_request('search','tracks','',sprintf('&limit=%d&query=%s',$matches['totalNumberOfItems'],urlencode($search)));
        }
        return $matches;
	}

    /**
     * @param $search
     * @return array
     * @throws TidalError
     */
	function search_album($search)
	{
		return $this->api_request('search','albums','','&limit=20&query='.urlencode($search));
	}

    /**
     * Try to find the correct search result
     * @param array $match Return value from search_track()
     * @param string $title Requested title
     * @param array $artists Requested artists
     * @param string $requested_artists_string Requested artists as string
     * @return bool|array Return false if track is not found, else return value of argument $match
     */
	function verify_search($match, $title, $artists, $requested_artists_string=null)
    {
        if(!is_array($match))
            throw new InvalidArgumentException();
        if(isset($match['items']))
            throw new InvalidArgumentException('Argument should be single track, not multiple search results');

        //A remix is not a match if the original track is requested
        if((stripos($title,'remix')===false) && (stripos($match['title'],'remix')===false))
            return false;

        $tidal_artists_string=implode("\n",array_column($match['artists'],'name')); //Create a string from the search result artist array

        if(empty($requested_artists_string))
            $requested_artists_string = implode("\n",$artists); //Create a string from the VG artist array


        $tidal_artists_lower = array_map('strtolower', array_column($match['artists'],'name'));
        $requested_artists_lower = array_map('strtolower', $artists);

        $diff = array_diff($tidal_artists_lower, $requested_artists_lower);
        if($this->debug && count($diff)>=1)
            printf("Array diff left %d artist(s):\n%s\n\n", count($diff), implode("\n", $diff));

        if(empty($diff))
        {
            if($this->debug)
                echo "Matched by array_diff\n";
            return $match;
        }
        //Check if the missing artist matches partially
        elseif(count($diff)===1 && mb_stripos($requested_artists_string, array_pop($diff))!==false)
        {
            if($this->debug)
                echo "Matched by stripos single diff\n";
            return $match;
        }

        if(mb_stripos($tidal_artists_string,$requested_artists_string)!==false)
        {
            return $match;
        }
        elseif(levenshtein($tidal_artists_string,$requested_artists_string)<=2)
        {
            if($this->debug)
                echo "Matched by levenshtein distance\n";
            return $match;
        }
        if(preg_match('/(.+)\sfeat\.\s(.+)(?:\s&|,)\s(.+)/', $requested_artists_string, $artists_feat))
        {
            unset($artists_feat[0]);
            $artists_feat_lower = array_map('strtolower', $artists_feat);
            $diff = array_diff($tidal_artists_lower, $artists_feat_lower);
            if(empty($diff))
            {
                if($this->debug)
                    echo "Matched by array_diff feat\n";
                return $match;
            }
            elseif(count($diff)===1)
            {
                $artist = array_pop($diff);
                if(mb_stripos($requested_artists_string, $artist)!==false)
                {
                    if($this->debug)
                        echo "Missing featured artist matched by stripos\n";
                    return $match;
                }
            }
        }
        return false;
    }

    /**
     * @param array $results Return from search_track()
     * @param string $title Expected title
     * @param array $artists Expected artists
     * @param string $requested_artists_string Expected artists as string
     * @return array|bool Return array with track info if found, else return false
     */
    function find_search_result($results, $title, $artists, $requested_artists_string = null)
    {
        if(!isset($results['items']))
            throw new InvalidArgumentException('Invalid search results');

        foreach ($results['items'] as $result)
        {
            $result = $this->verify_search($result, $title, $artists, $requested_artists_string);
            if($result!==false)
                return $result;
        }
        return false;
    }

    /**
     * @param string $title Title to search
     * @param array $artists Expected artists
     * @param string $requested_artists_string Expected artists as string
     * @throws TidalError
     * @return array|bool Return array with track info if found, else return false
     */
    function search_track_verify($title, $artists, $requested_artists_string = null)
    {
        $search = $this->search_track($title);
        return $this->find_search_result($search, $title, $artists, $requested_artists_string);
    }
}
