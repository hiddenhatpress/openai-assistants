<?php
namespace hiddenhatpress\openai\assistants;

class Message {
    public function __construct(private AsstComms $comms)
    {
    }

    public function create(
        string $thread_id,
        string $content,
        array $file_ids=[],
        array $metadata=[],
        string $role = "user"
    ): array {
         $data = [
             "content" => $content,
             "role" => $role,
             "file_ids" => $file_ids,
             "metadata" => $metadata,
         ];
         return $this->comms->doPost("https://api.openai.com/v1/threads/{$thread_id}/messages", $data);
    }

    public function modify(
        string $msgid,
        string $threadid,
        string $content,
        array $file_ids=[],
        array $metadata=[],
        string $role = "user"
    ): array {
        // https://platform.openai.com/docs/api-reference/messages/modifyMessage
         $data = [
             "content" => $content,
             "role" => $role,
             "file_ids" => $file_ids,
             "metadata" => $metadata,
         ];
         return $this->comms->doPost("https://api.openai.com/v1/threads/{$threadid}/messages/{$msgid}", $data);
    }

    public function retrieve(string $msgid, string $threadid): array
    {
        // https://platform.openai.com/docs/api-reference/messages/getMessage
        $url = "https://api.openai.com/v1/threads/{$threadid}/messages/{$msgid}";
        return $this->comms->doGet($url);
    }

    public function listMessages(
        string $threadid,
        int $limit = 20,
        string $order = "desc",
        ?string $after = null,
        ?string $before = null,
    ): array {
        // https://platform.openai.com/docs/api-reference/messages/listMessages
        $url = "https://api.openai.com/v1/threads/{$threadid}/messages";
        $data = [
           "limit" =>  $limit,
           "order" =>  $order,
        ];
        if (! is_null($after)) {
            $data['after'] = $after;
        }
        if (! is_null($before)) {
            $data['before'] = $before;
        }
        return $this->comms->doGet($url, $data);
    }

    public function retrieveMessageFile (
        string $threadid,
        string $msgid,
        string $fileid
    ): array {
        // https://platform.openai.com/docs/api-reference/messages/getMessageFile
        $url = "https://api.openai.com/v1/threads/{$threadid}/messages/{$msgid}/files/{$fileid}";
        $data = [];
        return $this->comms->doGet($url, $data);
    }

    public function listMessageFiles (
        string $threadid,
        string $msgid,
    ): array {
        // https://platform.openai.com/docs/api-reference/messages/listMessageFiles
        $url = "https://api.openai.com/v1/threads/{$threadid}/messages/{$msgid}/files";
        $data = [];
        return $this->comms->doGet($url, $data);
    }
}

