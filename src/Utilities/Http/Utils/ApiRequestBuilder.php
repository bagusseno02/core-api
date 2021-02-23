<?php


namespace coreapi\Utilities\Http\Utils;


class ApiRequestBuilder
{
    //headers array
    public $headers;
    //data to be a query param or post data
    public $data;
    //accessToken
    public $token;
    //files to be uploaded
    public $files;

    public function __construct()
    {

    }

    public function withAccessToken(string $token)
    {
        $this->token = $token;

        $this->headers['Authorization'] = $this->token;
    }

    public function withHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function withData(array $data)
    {
        $this->data = $data;
    }

    public function withFiles(array $data)
    {
        $this->files = $data;
    }

    public function withFile($data)
    {
        $this->files = $data;
    }

    public function get()
    {
        return ['query' => $this->data, 'headers' => $this->headers];
    }

    public function post()
    {
        if ($this->files == null) {
            return ['form_params' => $this->data, 'headers' => $this->headers];
        } else {
            $output = [];
            foreach ($this->files as $key => $value) {
                if (!is_array($value)) {
                    $output[] = [
                        'name' => 'files[]',
                        'contents' => fopen($value->getPathname(), 'r'),
                        'filename' => $value->getClientOriginalName()
                    ];
                    continue;
                }
            }

            foreach ($this->data as $key => $value) {
                $output [] =
                    [
                        'name' => $key,
                        'contents' => $value
                    ];
            }
            return ['multipart' => $output, 'headers' => $this->headers];

        }
    }

    public function postJson()
    {
        return ['json' => $this->data, 'headers' => $this->headers];
    }

    public function put()
    {
        return ['body' => $this->data, 'headers' => $this->headers];
    }

    public function delete()
    {
        return ['query' => $this->data, 'headers' => $this->headers];
    }

    //Ignore SSL certificate verification
    public function postVerifyFalse(){
        return ['form_params' => $this->data, 'headers' => $this->headers, 'verify' => false];
    }
}