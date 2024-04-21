<?php

namespace hiddenhatpress\openai\assistants;

class Thread
{
    public function __construct(private AsstComms $comms)
    {
    }

    public function create(array $messages = [], array $metadata = []): array
    {
        // https://platform.openai.com/docs/api-reference/threads/createThread
        $data = [
            "messages" => $messages,
        ];
        if (! empty($metadata)) {
            $data["metadata"] = $metadata;
        }
        return $this->comms->doPost("https://api.openai.com/v1/threads", $data);
    }

    public function modify(string $threadid, array $messages = [], array $metadata = []): array
    {
        // https://platform.openai.com/docs/api-reference/threads/modifyThread
        $data = [
            "messages" => $messages,
        ];
        if (! empty($metadata)) {
            $data["metadata"] = $metadata;
        }

        return $this->comms->doPost("https://api.openai.com/v1/threads/{$threadid}", $data);
    }

    public function retrieve($id): array
    {
        // https://platform.openai.com/docs/api-reference/threads/getThread
        $url = "https://api.openai.com/v1/threads/{$id}";
        return $this->comms->doGet($url);
    }


    public function del(string $id): array
    {
        // https://platform.openai.com/docs/api-reference/threads/deleteThread
        $url = "https://api.openai.com/v1/threads/{$id}";
        return $this->comms->doDelete($url);
    }
}
