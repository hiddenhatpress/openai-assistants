<?php
namespace hiddenhatpress\openai\assistants;

class Assistants {
    private AsstComms $comms;
    private Assistant $assistant;
    private AssistantFile $assistantfile;
    private Message $message;
    private Run $run;
    private Thread $thread;

    public function __construct(AsstComms $comms)
    {
        $this->comms = $comms;
    }

    function getAssistantService() {
        $this->assistant ??= new Assistant($this->comms);
        return $this->assistant;
    }

    function getAssistantFileService() {
        $this->assistantfile ??= new AssistantFile($this->comms);
        return $this->assistantfile;
    }

    function getMessageService() {
        $this->message ??= new Message($this->comms);
        return $this->message;
    }

    function getRunService() {
        $this->run ??= new Run($this->comms);
        return $this->run;
    }

    function getThreadService() {
        $this->thread ??= new Thread($this->comms);
        return $this->thread;
    }
}

