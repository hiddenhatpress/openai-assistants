<?php

namespace hiddenhatpress\openai\assistants;

class AsstComms
{
    public function __construct(private string $model, private string $secretKey)
    {
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function doGet(string $url, array $args = []): array
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->secretKey,
            'OpenAI-Beta: assistants=v1'
        ];
        if (count($args)) {
            $qs = http_build_query($args);
            $url .= "?{$qs}";
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $optarray[CURLOPT_RETURNTRANSFER ] = 1;
        $optarray[CURLOPT_URL] = $url;
        curl_setopt_array($curl, $optarray);
        $result = curl_exec($curl);
        $resp = json_decode($result, true);
        return $resp;
    }

    public function doPost(string $url, array $data): array
    {
        return $this->doPutOrPost("POST", $url, $data);
    }

    public function doPut(string $url, array $data): array
    {
        return $this->doPutOrPost("PUT", $url, $data);
    }

    private function doPutOrPost(string $which, string $url, array $data): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $which);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function doDelete(string $url): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function doFileUpload(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new \Exception("no file at '$filePath'");
        }
        // https://platform.openai.com/docs/api-reference/files/create
        $url = "https://api.openai.com/v1/files";
        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
        ];

        $pathinfo = pathinfo($filePath);
        $fullpath = realpath($filePath);
        if ($pathinfo['extension'] == "php") {
            // for some reason openai does not like files which lead with a php opening tag
            $contents = file_get_contents($filePath);
            $contents = preg_replace("/^\s*(<\?php)/s", "", $contents);
            $contents = preg_replace("/\n\s*(\?" . ">)\s*$/s", "", $contents);
            $cfile = new \CURLStringFile($contents, basename($filePath));
        } else {
            $cfile = new \CURLFile($fullpath, null, basename($fullpath));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $postfields = array(
            "purpose" => "assistants",
            "file" => $cfile
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode < 200 || $httpcode >= 300) {
            print_r($response);
            throw new \Exception("File upload failed with status code: $httpcode");
        }

        $responseArray = json_decode($response, true);
        if (!isset($responseArray['id'])) {
            throw new \Exception("Missing 'id' in response");
        }

        return $responseArray['id'];
    }
}
