<?php

namespace TeemoCell\SteamWebApi;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use stdClass;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use TeemoCell\SteamWebApi\Exceptions\ApiArgumentRequired;
use TeemoCell\SteamWebApi\Exceptions\ApiCallFailedException;
use TeemoCell\SteamWebApi\Exceptions\ClassNotFoundException;
use TeemoCell\SteamWebApi\Exceptions\InvalidApiKeyException;
use TeemoCell\SteamWebApi\Exceptions\UnrecognizedId;
use TeemoCell\SteamWebApi\Steam\App;
use TeemoCell\SteamWebApi\Steam\Group;
use TeemoCell\SteamWebApi\Steam\GameServers;
use TeemoCell\SteamWebApi\Steam\Item;
use TeemoCell\SteamWebApi\Steam\News;
use TeemoCell\SteamWebApi\Steam\Package;
use TeemoCell\SteamWebApi\Steam\Player;
use TeemoCell\SteamWebApi\Steam\Publisher;
use TeemoCell\SteamWebApi\Steam\User;
use TeemoCell\SteamWebApi\Steam\User\Stats;
use TeemoCell\SteamWebApi\Steam\WebApi;
use TeemoCell\SteamWebApi\Steam\Workshop;

/**
 * The explicit endpoint methods are the preferred API. The magic fallback in
 * __call() remains only for backwards compatibility with custom endpoints.
 */
class Client
{
    use SteamId;

    public array $validFormats = ['json', 'xml', 'vdf'];

    protected string $url = 'https://api.steampowered.com/';

    protected ClientInterface $client;

    protected ?string $interface = null;

    protected string $method;

    protected ?string $version = 'v2';

    protected string $apiKey;

    protected string $apiFormat = 'json';

    protected $steamId;

    protected bool $isService = false;

    /**
     * @throws InvalidApiKeyException
     */
    public function __construct(?string $apiKey = null, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new GuzzleClient();
        $this->apiKey = $apiKey ?? $this->getApiKey();

        if ($this->apiKey === '' || $this->apiKey === 'YOUR-API-KEY') {
            throw new InvalidApiKeyException();
        }

        // Set up the Ids
        $this->setUpFormatted();
    }

    public function get(): static
    {
        return $this;
    }

    public function getSteamId()
    {
        return $this->steamId;
    }

    public function news(): News
    {
        return new News($this->apiKey, $this->client);
    }

    public function player(int|string $steamId): Player
    {
        return new Player($this->normalizeSteamId($steamId), $this->apiKey, $this->client);
    }

    public function user(int|string|array $steamId): User
    {
        return new User($this->normalizeSteamId($steamId), $this->apiKey, $this->client);
    }

    public function userStats(int|string|null $steamId = null): Stats
    {
        return new Stats(
            $steamId === null ? null : $this->normalizeSteamId($steamId),
            $this->apiKey,
            $this->client,
        );
    }

    public function app(): App
    {
        return new App($this->apiKey, $this->client);
    }

    public function package(): Package
    {
        return new Package($this->apiKey, $this->client);
    }

    public function group(): Group
    {
        return new Group($this->apiKey, $this->client);
    }

    public function item(): Item
    {
        return new Item($this->apiKey, $this->client);
    }

    public function workshop(): Workshop
    {
        return new Workshop($this->apiKey, $this->client);
    }

    public function webApi(): WebApi
    {
        return new WebApi($this->apiKey, $this->client);
    }

    public function gameServers(): GameServers
    {
        return new GameServers($this->apiKey, $this->client);
    }

    public function publisher(): Publisher
    {
        return new Publisher($this->apiKey, $this->client);
    }

    /**
     * @param null $arguments
     *
     * @return stdClass
     *
     * @throws ApiArgumentRequired
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    protected function setUpService($arguments = null): stdClass
    {
        // Services have a different url syntax
        if ($arguments == null) {
            throw new ApiArgumentRequired;
        }

        $parameters = [
            'key' => $this->apiKey,
            'format' => $this->apiFormat,
            'input_json' => $arguments,
        ];

        $steamUrl = $this->buildUrl(true);

        // Build the query string
        $parameters = http_build_query($parameters);

        // Send the request and get the results
        $request = new Request('GET', "$steamUrl?$parameters");
        $response = $this->sendRequest($request);

        // Pass the results back
        return $response->body;
    }

    /**
     * @throws GuzzleException
     * @throws ApiCallFailedException
     */
    protected function setUpClient(array $arguments = [])
    {
        $versionFlag = ! is_null($this->version);
        $steamUrl = $this->buildUrl($versionFlag);

        $parameters = [
            'key' => $this->apiKey,
            'format' => $this->apiFormat,
        ];

        if (! empty($arguments)) {
            $parameters = array_merge($arguments, $parameters);
        }

        // Build the query string
        $parameters = http_build_query($parameters);

        $headers = [];
        if (array_key_exists('l', $arguments)) {
            $headers = [
                'Accept-Language' => $arguments['l'],
            ];
        }

        // Send the request and get the results
        $request = new Request('GET', $steamUrl.'?'.$parameters, $headers);
        $response = $this->sendRequest($request);

        // Pass the results back
        return $response->body;
    }

    protected function setUpXml(array $arguments = []): \SimpleXMLElement|null
    {
        $steamUrl = $this->buildUrl();

        // Build the query string
        $parameters = http_build_query($arguments);

        // Pass the results back
        libxml_use_internal_errors(true);
        $result = simplexml_load_file("$steamUrl?$parameters");

        if (! $result) {
            return null;
        }

        return $result;
    }

    public function getRedirectUrl(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        $this->url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
    }

    /**
     *
     * @param Request $request
     * @return stdClass
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    protected function sendRequest(Request $request): stdClass
    {
        // Try to get the result.  Handle the possible exceptions that can arise
        try {
            $response = $this->client->send($request);

            $result = new stdClass();
            $result->code = $response->getStatusCode();
            $result->body = json_decode((string) $response->getBody(), null, 512, JSON_THROW_ON_ERROR);
        } catch (ClientException $e) {
            throw new ApiCallFailedException($e->getMessage(), $e->getResponse()->getStatusCode(), $e);
        } catch (ServerException $e) {
            throw new ApiCallFailedException('Api call failed to complete due to a server error.', $e->getResponse()->getStatusCode(), $e);
        } catch (Exception $e) {
            throw new ApiCallFailedException($e->getMessage(), $e->getCode(), $e);
        }

        // For some resources, the Steam API responds with an empty response
        if (empty($result->body)) {
            throw new ApiCallFailedException('API call failed due to empty response', $result->code);
        }

        if (empty((array) $result->body)) {
            throw new ApiCallFailedException('Api call failed to complete due to an empty response.', $result->code);
        }

        // If all worked out, return the result
        return $result;
    }

    private function buildUrl($version = false): string
    {
        // Set up the basic url
        $url = $this->url.($this->interface ?? '').'/'.$this->method.'/';

        // If we have a version, add it
        if ($version) {
            return "{$url}{$this->version}/";
        }

        return $url;
    }

    /**
     * @throws ClassNotFoundException
     * @throws UnrecognizedId
     */
    public function __call($name, $arguments)
    {
        // Handle a steamId being passed
        if (! empty($arguments) && count($arguments) == 1) {
            $this->steamId = $arguments[0];

            $this->convertSteamIdTo64();
        }

        // Inside the root steam directory
        $class = ucfirst((string) $name);
        $steamClass = '\TeemoCell\SteamWebApi\Steam\\'.$class;

        if (class_exists($steamClass)) {
            return new $steamClass($this->steamId);
        }

        // Inside a nested directory
        $class = implode('\\', preg_split('/(?=[A-Z])/', $class, -1, PREG_SPLIT_NO_EMPTY));
        $steamClass = '\TeemoCell\SteamWebApi\Steam\\'.$class;

        if (class_exists($steamClass)) {
            return new $steamClass($this->steamId);
        }

        // Nothing found
        throw new ClassNotFoundException($name);
    }

    /**
     * @param Collection $objects
     *
     * @return Collection
     */
    protected function sortObjects(Collection $objects): Collection
    {
        return $objects->sortBy(fn ($object) => $object->name);
    }

    /**
     * @param string $method
     * @param string $version
     */
    protected function setApiDetails(string $method, string $version): void
    {
        $this->method = $method;
        $this->version = $version;
    }

    /**
     * @throws ApiArgumentRequired
     * @throws ApiCallFailedException
     * @throws GuzzleException
     * @throws \JsonException
     */
    protected function getServiceResponse($arguments)
    {
        $arguments = json_encode($arguments, JSON_THROW_ON_ERROR);

        // Get the client
        $body = $this->setUpService($arguments);

        if (! isset($body->response) || empty((array) $body->response)) {
            throw new ApiCallFailedException('Api call failed to complete due to an empty response.', 500);
        }

        return $body->response;
    }

    /**
     * @throws ApiCallFailedException
     * @throws GuzzleException
     */
    protected function getClientResponse($arguments)
    {
        // Get the client
        $body = $this->setUpClient($arguments);

        if (! isset($body->response) || empty((array) $body->response)) {
            throw new ApiCallFailedException('Api call failed to complete due to an empty response.', 500);
        }

        return $body->response;
    }

    /**
     * @return string
     * @throws Exceptions\InvalidApiKeyException
     */
    protected function getApiKey(): string
    {
        $apiKey = null;

        if (Config::getFacadeRoot() !== null) {
            $apiKey = Config::get('steam-api.steamApiKey');
        }

        if (! is_string($apiKey) || $apiKey === '') {
            $apiKey = getenv('STEAM_API_KEY');
        }

        if (! is_string($apiKey) || $apiKey === '') {
            $apiKey = getenv('apiKey');
        }

        if (! is_string($apiKey) || $apiKey === '') {
            throw new InvalidApiKeyException();
        }

        return $apiKey;
    }

    /**
     * @throws UnrecognizedId
     */
    private function convertSteamIdTo64(): void
    {
        if (is_array($this->steamId)) {
            array_walk(
                /**
                 * @throws UnrecognizedId
                 */
                $this->steamId, function (&$id) {
                    // Convert the id to all types and grab the 64 bit version
                    $id = $this->convertToAll($id)->id64;
                });
        } else {
            // Convert the id to all types and grab the 64 bit version
            $this->steamId = $this->convertToAll($this->steamId)->id64;
        }
    }

    private function normalizeSteamId(int|string|array $steamId): string|array
    {
        $converter = new SteamIdConverter();

        if (is_array($steamId)) {
            return array_map(
                fn (int|string $id): string => $converter->convertId($id, 'id64'),
                $steamId,
            );
        }

        return $converter->convertId($steamId, 'id64');
    }
}
