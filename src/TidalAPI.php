<?php

namespace datagutten\Tidal;

use datagutten\Tidal\exceptions\TidalHTTPError;
use InvalidArgumentException;
use WpOrg\Requests;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

class TidalAPI
{
    public string $countryCode = 'NO';
    /**
     * @var string Device type DESKTOP or PHONE
     */
    public string $deviceType = 'DESKTOP';
    public string $locale = 'nb_NO';
    public Session $session;

    public function __construct()
    {
        $this->session = new Session();
        $this->session->headers = [
            'accept' => '*/*',
            //'authorization' => $this->auth,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) TIDAL/2.35.0 Chrome/108.0.5359.215 Electron/22.3.27 Safari/537.36',
            'Connection' => 'keep-alive',
            'Origin' => 'https://desktop.tidal.com',
            'Referer' => 'https://desktop.tidal.com/',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'en-us',
        ];
    }

    public function setAuth(string $token)
    {
        $this->session->headers['authorization'] = 'Bearer ' . $token;
    }

    /**
     * HTTP GET request
     * @param string $url URL to GET
     * @param array $headers Headers
     * @param array $options Options
     * @return Response Requests response object
     * @throws TidalError
     * @throws TidalHTTPError Generic HTTP error with no message
     */
    public function get(string $url, array $headers = [], array $options = []): Requests\Response
    {
        $headers += array();
        try
        {
            $response = $this->session->get($url, $headers, $options);
            if (!$response->success)
            {
                if (empty($response->body))
                    throw new TidalHTTPError($response->status_code);
                elseif ($response->headers['content-type'] == 'application/json')
                {
                    $error = $response->decode_body();
                    throw new TidalError($error['description'] ?? $error['userMessage'], $response->status_code);
                }
                else
                    throw new TidalError('HTTP request unsuccessful: ' . $response->body, $response->status_code);
            }
        }
        catch (Requests\Exception $e)
        {
            throw new TidalError($e->getMessage(), $e->getCode(), $e);
        }
        return $response;
    }

    /**
     * HTTP GET request
     * @param string $url URL to GET
     * @param array $headers Headers
     * @param array $options Options
     * @return array Decoded JSON response
     * @throws TidalError
     * @throws TidalHTTPError Generic HTTP error with no message
     */
    public function get_json(string $url, array $headers = [], array $options = []): array
    {
        $headers['accept'] = 'application/json';
        try
        {
            return $this->get($url, $headers, $options)->decode_body();
        }
        catch (Requests\Exception $e)
        {
            throw new TidalError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Send a request to the TIDAL API
     * @param string $query API query string
     * @param string $prefix First part of hostname, usually desktop or api
     * @param string $version API version, v1 or v2
     * @param array $get Extra GET parameters to add to the query
     * @param ?string $order Track order (ALBUM, NAME, ARTIST, LENGTH or DATE)
     * @param ?string $orderDirection Order direction (ASC or DESC)
     * @param ?int $offset Track offset
     * @param ?int $limit Track count limit
     * @return array Response from TIDAL
     * @throws TidalError API request failed
     * @throws TidalHTTPError HTTP error without error message
     */
    public function api_request(string $query, string $prefix = 'desktop', string $version = 'v2', array $get = [], int $offset = null, int $limit = null, string $order = null, string $orderDirection = null): array
    {
        if (!empty($orderDirection) && !in_array($orderDirection, ['ASC', 'DESC']))
            throw new InvalidArgumentException('Invalid order direction');

        $get_fields = $get + [
                'offset' => $offset,
                'limit' => $limit,
                'order' => $order,
                'orderDirection' => $orderDirection,
                'countryCode' => $this->countryCode,
                'locale' => $this->locale,
                'deviceType' => $this->deviceType,
            ];
        $get_fields = array_filter($get_fields, fn($value) => $value !== null);
        $get_string = http_build_query($get_fields);
        $url = sprintf('https://%s.tidal.com/%s/%s?%s', $prefix, $version, $query, $get_string);

        return $this->get_json($url);
    }
}