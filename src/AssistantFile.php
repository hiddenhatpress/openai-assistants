<?php
namespace hiddenhatpress\openai\assistants;

class AssistantFile {
    public function __construct(private AsstComms $comms)
    {
    }

    function assignAssistantFile(string $asstid, string $fileid) {
        return $this->comms->doPost("https://api.openai.com/v1/assistants/{$asstid}/files", ["file_id" => $fileid]);
    }

    function createAndAssignAssistantFile(string $asstid, string $path) {
        $fileid = $this->comms->doFileUpload($path);
        return $this->comms->doPost("https://api.openai.com/v1/assistants/{$asstid}/files", ["file_id" => $fileid]);
    }

    function unassignAssistantFile(string $asstid, string $fileid) {
        return $this->comms->doDelete("https://api.openai.com/v1/assistants/{$asstid}/files/{$fileid}");
    }

    function unassignAndDeleteAssistantFile(string $asstid, string $fileid) {
        $this->comms->doDelete("https://api.openai.com/v1/assistants/{$asstid}/files/{$fileid}");
        $this->delFile($fileid);
    }

    function listAssistantFiles(
        string $asstid,
        int $limit = 20,
        string $order = "desc",
        ?string $after = null,
        ?string $before = null
    ) {
        $data = [
            "limit" => $limit,
            "order" => $order
        ];
        if (! is_null($after)) {
            $data['after'] = $after;
        }
        if (! is_null($before)) {
            $data['before'] = $before;
        }

        return $this->comms->doGet("https://api.openai.com/v1/assistants/{$asstid}/files");
    }

    function retrieveAssistantFile(string $asstid, string $fileid): array {
        return $this->comms->doGet("https://api.openai.com/v1/assistants/{$asstid}/files/{$fileid}");
    }

    function delFile(string $fileid): array {
        return $this->comms->doDelete("https://api.openai.com/v1/files/{$fileid}");
    }
    
    // only lists 'assistant' type files
    function listFiles(): array {
        return $this->comms->doGet("https://api.openai.com/v1/files", ["purpose" => "assistants"]);
    }
}
