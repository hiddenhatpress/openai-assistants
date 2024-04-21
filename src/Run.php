<?php

namespace hiddenhatpress\openai\assistants;

class Run
{
    public function __construct(private AsstComms $comms)
    {
    }

    public function create(
        string $threadid,
        string $assistantid,
        ?array $toolTypes = null,
        ?string $model = null,
        ?string $instructions = null,
        array $metadata = []
    ): array {
         // https://platform.openai.com/docs/api-reference/runs/createRun

         $data = [
             "assistant_id" => $assistantid,
         ];

         if (! empty($metadata)) {
             $data["metadata"] = $metadata;
         }
         if (! is_null($toolTypes)) {
             $tools = array_map(function ($type) {
                 return ['type' => $type];
             }, $toolTypes);
             $data['tools'] = $tools;
         }

         if (! is_null($model)) {
             $data['model'] = $model;
         }

         if (! is_null($instructions)) {
             $data['instructions'] = $instructions;
         }

         $url = "https://api.openai.com/v1/threads/{$threadid}/runs";
         return $this->comms->doPost($url, $data);
    }

    public function createThreadAndRun(
        string $assistantid,
        ?array $threaddata = null,
        ?array $toolTypes = null,
        ?string $model = null,
        ?string $instructions = null,
        array $metadata = [],
    ): array {
        // https://platform.openai.com/docs/api-reference/runs/createThreadAndRun
        $url = "https://api.openai.com/v1/threads/runs";
         $data = [
             "assistant_id" => $assistantid,
         ];
         if (! empty($metadata)) {
             $data["metadata"] = $metadata;
         }

         if (! is_null($toolTypes)) {
             $tools = array_map(function ($type) {
                 return ['type' => $type];
             }, $toolTypes);
             $data['tools'] = $tools;
         }

         if (! is_null($threaddata)) {
             $data['threaddata'] = $threaddata;
         }

         if (! is_null($model)) {
             $data['model'] = $model;
         }

         if (! is_null($instructions)) {
             $data['instructions'] = $instructions;
         }

         $url = "https://api.openai.com/v1/threads/runs";
         return $this->comms->doPost($url, $data);
    }

    public function retrieve(string $threadid, string $runid): array
    {
        // https://platform.openai.com/docs/api-reference/runs/getRun
        $url = "https://api.openai.com/v1/threads/{$threadid}/runs/{$runid}";
        return $this->comms->doGet($url);
    }

    public function modify(
        string $threadid,
        string $runid,
        array $metadata = [],
    ): array {

         $data = [];
         if (! empty($metadata)) {
             $data["metadata"] = $metadata;
         }
         // https://platform.openai.com/docs/api-reference/runs/modifyRun
         $url = "https://api.openai.com/v1/threads/{$threadid}/runs/{$runid}";
         return $this->comms->doPost($url, $data);
    }

    public function listRuns(
        string $threadid,
        int $limit = 20,
        string $order = "desc",
        ?string $after = null,
        ?string $before = null,
    ): array {
        // https://platform.openai.com/docs/api-reference/runs/listRuns
        $url = "https://api.openai.com/v1/threads/{$threadid}/runs";
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

    public function submitToolOutputs(
        string $threadid,
        string $runid,
        array $tooloutputs,
    ): array {
        //https://platform.openai.com/docs/api-reference/runs/submitToolOutputs
        $url = "https://api.openai.com/v1/threads/{$threadid}/runs/{$runid}/submit_tool_outputs";
        $data = [
            "tool_outputs" => $tooloutputs
        ];
        return $this->comms->doPost($url, $data);
    }

    public function cancel(
        string $threadid,
        string $runid,
    ): array {
        // https://platform.openai.com/docs/api-reference/runs/cancelRun
        $url = "https://api.openai.com/v1/threads/{$threadid}/runs/{$runid}/cancel";
        $data = [];
        return $this->comms->doPost($url, $data);
    }

    public function retrieveRunStep(
        string $threadid,
        string $runid,
        string $stepid,
    ): array {
        // https://platform.openai.com/docs/api-reference/runs/getRunStep
        $url = "https://api.openai.com/v1/threads/{$threadid}/runs/{$runid}/steps/{$stepid}";
        $data = [];
        return $this->comms->doGet($url, $data);
    }

    public function listRunSteps(
        string $threadid,
        string $runid,
        int $limit = 20,
        string $order = "desc",
        ?string $after = null,
        ?string $before = null,
    ): array {
        // https://platform.openai.com/docs/api-reference/runs/listRunSteps
        $url = "https://api.openai.com/v1/threads/{$threadid}/runs/{$runid}/steps";
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
}
