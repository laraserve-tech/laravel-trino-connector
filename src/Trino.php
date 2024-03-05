<?php

namespace LaraserveTech;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;

class Trino
{
    /**
     * @var string[] $headers The headers used when communicating with the Trino instance
     */
    private array $headers;

    /**
     * @var ?stdClass $result The json encoded results from executing a query
     */
    private ?stdClass $result;

    /**
     * @var ?array $columns The json encoded columns returned from the results of a query
     */
    private ?array $columns;

    /**
     * @var ?array $data The json encoded data returned from the results of a query
     */
    private ?array $data;

    /**
     * @var string|null $previousQuery The previous query executed on the instance
     */
    private ?string $previousQuery = null;

    /**
     * @var ?string The errors returned
     */
    private ?string $errors = null;

    /**
     * @var string The URL to send queries to
     */
    private string $statementUrl;

    public function __construct()
    {
        $this->statementUrl = config('trino-connector.base_url');
        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => "Basic " . config('trino-connector.auth_token'),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @param string $query
     * @return $this
     * @throws GuzzleException
     */
    public function execute(string $query): self
    {
        $this->resetObject();

        $this->previousQuery = $query;
        try {
            $results = (new Client())->post($this->statementUrl, [
                'headers' => $this->headers,
                'timeout' => config('trino-connector.timeout'),
                'debug' => config('trino-connector.debug'),
                'body' => $query,
            ]);
            $this->result = json_decode($results->getBody()->getContents());
        } catch (\Throwable $exception) {
            $this->errors = json_encode($exception->getMessage());
        }

        $this->process();

        return $this;
    }

    /**
     * Subsequent queries often return a cached response without resetting the result object
     *
     * @return void
     */
    public function resetObject(): void
    {
        $this->result = null;
        $this->columns = null;
        $this->data = null;
        $this->errors = null;
    }

    /**
     * @return bool Whether the previous query was successful
     */
    public function success(): bool
    {
        return empty($this->errors);
    }

    /**
     * Retrieves the errors from the object.
     *
     * @return ?string The errors stored in the object.
     */
    public function getErrors(): ?string
    {
        return $this->errors;
    }

    /**
     * Retrieves the columns from the object.
     *
     * @return array The columns stored in the object.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Retrieves the data.
     *
     * @return array The data stored in the object.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Retrieves the previous query.
     *
     * @return ?string The previous query that was executed
     */
    public function getQuery(): ?string
    {
        return $this->previousQuery;
    }

    /**
     * Process the data.
     *
     * @return self
     * @throws GuzzleException
     */
    protected function process(): self
    {
        while (isset($this->result->nextUri) && empty($this->data)) {
            $results = (new Client())->get($this->result->nextUri, [
                'headers' => $this->headers,
                'timeout' => config('trino-connector.timeout'),
                'debug' => config('trino-connector.debug'),
            ]);
            $this->result = json_decode($results->getBody()->getContents());

            if (isset($this->result->error)) {
                $this->errors = json_encode($this->result->error);

                return $this;
            }

            if (isset($this->result->data)) {
                $this->columns = $this->result->columns;
                $this->data = $this->result->data;
            }
        }

        return $this;
    }
}
