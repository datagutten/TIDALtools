<?php

namespace datagutten\Tidal\elements;

use datagutten\Tidal\Search;
use datagutten\Tidal\TidalError;
use InvalidArgumentException;

class SearchResult extends SimpleArrayAccess
{
    protected Search $search;
    /**
     * @var Album[] Album search results
     */
    public array $albums = [];
    /**
     * @var Track[] Track search results
     */
    public array $tracks = [];

    /**
     * @var int Search result limit
     */
    public int $limit;
    /**
     * @var int Search result offset
     */
    public int $offset = 0;
    /**
     * @var int Total number of items in search result
     */
    public int $totalNumberOfItems;
    /**
     * @var string Search query
     */
    public string $query;
    /**
     * @var string Object type to search for
     */
    public string $searchType;


    /**
     * @throws TidalError
     */
    public function __construct(Search $search, $searchType, $query, $limit = 20)
    {
        $this->search = $search;
        $this->searchType = $searchType;
        $this->query = $query;
        $this->limit = $limit;
        $this->execute();
    }

    /**
     * Execute the search
     * @throws TidalError
     */
    protected function execute()
    {
        $query = http_build_query(['limit' => $this->limit, 'query' => $this->query, 'offset' => $this->offset]);
        $result = $this->search->api_request('search', $this->searchType, '', '&' . $query);
        $this->processResult($result);
    }

    /**
     * Get next results
     * @param int $limit
     * @throws TidalError
     */
    public function next(int $limit = 20)
    {
        $offset = $this->offset + $this->limit;
        if ($this->totalNumberOfItems < $offset)
            throw new InvalidArgumentException('All results already fetched');

        $this->offset = $offset;
        $this->limit = $limit;
        $this->execute();
    }

    /**
     * Process search result
     * @param array $result Search result array
     * @throws TidalError
     */
    protected function processResult(array $result)
    {
        $this->limit = $result['limit'];
        $this->offset = $result['offset'];
        $this->totalNumberOfItems = $result['totalNumberOfItems'];

        foreach ($result['items'] as $item)
        {
            if ($this->searchType == 'albums')
                $this->albums[] = new Album($item, null, $this->search);
            elseif ($this->searchType == 'tracks')
                $this->tracks[] = new Track($item, $this->search);
            else
                trigger_error(sprintf('Search type %s is not supported', $this->searchType));
        }
    }
}