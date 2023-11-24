<?php
namespace hiddenhatpress\openai\assistants;

class Assistant {
    public function __construct(private AsstComms $comms)
    {
    }

    public function create (
         string $name,
         string $instructions,
         array $toolTypes = [],
         array $fileids = [],
         ?string $description = null,
         ?string $model = null,
     ): array {
        // https://platform.openai.com/docs/api-reference/assistants/createAssistant
        $model ??= $this->comms->getModel();
        $data = [
            "name" => $name,
            "instructions" => $instructions,
            "model" => $model,
            "file_ids" => $fileids
        ];

        $tools = array_map(function($type) {
            return ['type' => $type];
        }, $toolTypes);
        $data['tools'] = $tools;

        if (! is_null($description)) {
            $data['description'] = $description;
        }

        return $this->comms->doPost("https://api.openai.com/v1/assistants", $data);
    }

    function del(string $id): array
    {
        $url = "https://api.openai.com/v1/assistants/{$id}";
        return $this->comms->doDelete($url);
    }

    function list(
        int $limit = 20,
        string $order = "desc",
        ?string $after = null,
        ?string $before = null
    ): array {
        $data = [
            "order" => $order,
            "limit" => $limit
        ];
        if (! is_null($after)) {
            $data['after'] = $after;
        }
        if (! is_null($before)) {
            $data['before'] = $before;
        }
        $url = "https://api.openai.com/v1/assistants";
        return $this->comms->doGet($url, $data);
    }

    function retrieve($id): array
    {
        // https://platform.openai.com/docs/api-reference/assistants/getAssistant 
        $url = "https://api.openai.com/v1/assistants/{$id}";
        return $this->comms->doGet($url);
    }

    function modify(
        string $id,
        string $instructions,
        array $toolTypes = [],
        array $fileids = [],
        ?string $description = null,
        ?string $model = null,
    ): array {
        // https://platform.openai.com/docs/api-reference/assistants/updateAssistant
        $model ??= $this->comms->getModel();

        if (! is_null($description)) {
            $data['description'] = $description;
        }

        $url = "https://api.openai.com/v1/assistants/{$id}";
        $tools = array_map(function($type) {
            return ['type' => $type];
        }, $toolTypes);

        $data = [
            "instructions" => $instructions,
            "tools" => $tools,
            "model" => $model,
            "file_ids" => $fileids
        ];

        return $this->comms->doPost($url, $data);
    }
}