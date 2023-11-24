<?php
namespace hiddenhatpress\openai\assistants;

class Thread {
    public function __construct(private AsstComms $comms)
    {
    }


    public function create(array $messages = [], array $metadata=[])
    {
         $data = [
             "messages" => $messages,
             "metadata" => $metadata,
         ];
         return $this->comms->doPost("https://api.openai.com/v1/threads", $data);
    }

    public function modify(string $threadid, array $messages = [], array $metadata=[])
    {
        // https://platform.openai.com/docs/api-reference/threads/modifyThread 
        $data = [
            "messages" => $messages,
            "metadata" => $metadata,
        ];
        return $this->comms->doPost("https://api.openai.com/v1/threads/{$threadid}", $data);
    }

    function retrieve($id)
    {
        $url = "https://api.openai.com/v1/threads/{$id}";
        return $this->comms->doGet($url);
    }


    function del(string $id)
    {
        $url = "https://api.openai.com/v1/threads/{$id}";
        return $this->comms->doDelete($url);
    }
}
