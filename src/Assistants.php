<?php

namespace hiddenhatpress\openai\assistants;

class Assistants
{
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

    public function getAssistantService(): Assistant
    {
        $this->assistant ??= new Assistant($this->comms);
        return $this->assistant;
    }

    public function getAssistantFileService(): AssistantFile
    {
        $this->assistantfile ??= new AssistantFile($this->comms);
        return $this->assistantfile;
    }

    public function getMessageService(): Message
    {
        $this->message ??= new Message($this->comms);
        return $this->message;
    }

    public function getRunService(): Run
    {
        $this->run ??= new Run($this->comms);
        return $this->run;
    }

    public function getThreadService(): Thread
    {
        $this->thread ??= new Thread($this->comms);
        return $this->thread;
    }
}
