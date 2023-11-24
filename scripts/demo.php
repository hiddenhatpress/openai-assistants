<?php
require_once(__DIR__ . "/../vendor/autoload.php");

use hiddenhatpress\openai\assistants\Assistants;
use hiddenhatpress\openai\assistants\AsstComms;


$token = getenv('OPENAI_API_KEY');
// this model needed for retrieval tools
$model = "gpt-4-1106-preview";

$asstcomms = new AsstComms($model, $token);
$assistants = new Assistants($asstcomms);

$asstservice = $assistants->getAssistantService();
$fileservice = $assistants->getAssistantFileService();
$threadservice = $assistants->getThreadService();
$runservice = $assistants->getRunService();
$messageservice = $assistants->getMessageService();

// will get 20 by default
$entities = $asstservice->list();
$assistantid = null;
$name = "pliny-assistant";

foreach ($entities['data'] as $asst) {
    if ($asst['name'] == $name) {
        $assistantid = $asst['id'];
    }
}

if (empty($assistantid)) {
    // create the assistant
    $asstresp = $asstservice->create(
         $name,
         "You are an ancient history assistant specialising in Pliny the Younger",
         ["retrieval"] 
    );
    $assistantid = $asstresp['id'];

    // upload file
    $fileresp = $fileservice->createAndAssignAssistantFile($assistantid, "pliny.txt" );
}

// create a thread
$threadresp = $threadservice->create();   
$threadid = $threadresp['id'];

// create a message and add to the thread
$content = "Discuss the ways that Pliny talks about fish in his letters.";
$msgresp = $messageservice->create($threadid, $content);

// run the message
$runresp = $runservice->create($threadid, $assistantid);
while($runresp['status'] != "completed") {
    sleep(1);
    print "# polling {$runresp['status']}\n";
    $runresp = $runservice->retrieve($threadid, $runresp['id']);
}

// access the response
$msgs = $messageservice->listMessages($threadid);
print $msgs['data'][0]['content'][0]['text']['value'];
print "\n";

// get the files from the assistant
$files = $fileservice->listAssistantFiles($assistantid);
foreach ($files['data'] as $finfo) {
    // unassign and delete
    $fileservice->unassignAndDeleteAssistantFile($assistantid, $finfo['id']);
}

// delete the assistant
$del = $asstservice->del($assistantid);
