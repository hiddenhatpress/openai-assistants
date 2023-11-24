# openai-assistants
#
A quick and dirty client for OpenAI's assistants API.

[OpenAI API documentation](https://platform.openai.com/docs/api-reference/assistants)
[OpenAI Assistants overview](https://platform.openai.com/docs/assistants/overview)

## Overview

```php
use hiddenhatpress\openai\assistants\Assistants;
use hiddenhatpress\openai\assistants\AsstComms;

$token = getenv('OPENAI_API_KEY');
// this model needed for retrieval tools
$model = "gpt-4-1106-preview";

$asstcomms = new AsstComms($model, $token);
$assistants = new Assistants($asstcomms);
```

The `Assistants` object is really only a factory for objects which provide thin client access to the [assistant](https://platform.openai.com/docs/api-reference/assistants), [thread](https://platform.openai.com/docs/api-reference/threads). [Messages](https://platform.openai.com/docs/api-reference/messages) and [runs](https://platform.openai.com/docs/api-reference/runs) APIs.

```php
$asstservice = $assistants->getAssistantService();
$fileservice  = $assistants->getAssistantFileService();
$threadservice = $assistants->getThreadService();
$runservice = $assistants->getRunService();
$messageservice = $assistants->getMessageService();
```

## Quick start
The basic workflow for the Assistants API is:

1. Create an assistant
2. Optionally add files to the assistant
3. Create a thread (so that different users can use the assistant through your interface)
4. Create a message representing a user's message and add it to thread
5. Run the thread
6. Poll the status of the the run until its status is completed
7. Get the latest message (the response) from the thread and return to the user
8. Repeat from step 4 as needed

We're going to create an assistant to help us read the [letters of Pliny the Younger](https://www.gutenberg.org/ebooks/2811).

### Access assistants

First, let's check that we haven't already created an assistant named `pliny-assistant`:

```php
// will get 20 by default
$entities = $asstservice->list();
$assistantid = null;
$name = "pliny-assistant";

foreach ($entities['data'] as $asst) {
    if ($asst['name'] == $name) {
        $assistantid = $asst['id'];
    }
}
```

> **NOTE** because the list endpoint returns 20 elements by default, this appraoch would not scale if you had more than 20 asssistants. In a robust system you'd likely have stored an assistant id. If you wanted to create a reliable version of this dynamic name-based system you'd need page through the data. `list()` supports `limit` -- up to 100 -- as well as `before` and `after` fields.

### Create an assistant and upload a file

For a first run we'll need to actually create the assistant, and upload a source file (the [text version](https://www.gutenberg.org/ebooks/2811.txt.utf-8) of the letters saved as `pliny.txt`).

```php
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
```

The arguments to `create()` are a name, a set of instructions, and a list of tool types. These can be `code_interpreter`, `retrieval`, or `function`. We are creating a retrieval assistant -- that is, an assistant specialised in working with texts we give it. We're giving it a historical text -- but the assistant would likely come into its own interpreting files that the model has not already been trained on -- a novel-in-progress perhaps, or corporate documents.

The `AssistantFile` class accesses the file aspect of the assistants API and the [File API](https://platform.openai.com/docs/api-reference/files). So `createAndAssignAssistantFile()` uploads a given file and then associates it with an assistant.

Now we have an assistant with access to the text we are interested in. Let's try asking it a question.

### Setting up a message for running
In order to send a message we need to create a thread and add a message to it.

```php
// create a thread
$threadresp = $threadservice->create();   
$threadid = $threadresp['id'];

// create a message and add to the thread
$content = "Discuss the ways that Pliny talks about fish in his letters.";
$msgresp = $messageservice->create($threadid, $content);
```

### Running the thread to send the message 
Next, we need a tell the API to run the thread. We use the [runs API](https://platform.openai.com/docs/api-reference/runs) for this.

```php
$runresp = $runservice->create($threadid, $assistantid);
while($runresp['status'] != "completed") {
    sleep(1);
    print "# polling {$runresp['status']}\n";
    $runresp = $runservice->retrieve($runresp['id'], $threadid);
}
```

Because the service does not block, we need to poll it until it the run status is `completed`.

### Accessing the latest message from the thread
We can list the messages using the [messages API](https://platform.openai.com/docs/api-reference/messages).

```php
// access the response
$msgs = $messageservice->listMessages($threadid);
print $msgs['data'][0]['content'][0]['text']['value'];
print "\n";
```

By default, messages are ordered in descending order, so the first element will be the latest.

### Some output

Let's run the code and get some ancient fish news.

```
# polling queued
# polling in_progress
# polling in_progress
...

Pliny the Younger mentioned fish in the context of his letters to highlight certain aspects or qualities of his environment or surroundings:

1. He discusses the offerings of his local sea and expresses a somewhat limited pride in its bounty. He says, "I cannot boast that our sea is plentiful in choice fish" but then goes on to recognize that it does provide for "capital soles and prawns." This indicates a modest abundance of certain kinds of fish, and he contrasts this with the abundant provisions of other types, such as milk, which he proudly notes his villa's ability to excel in even when compared to inland places【7†source】.

...
```

### Tidying up: unassign and delete assistant files
In real world code we would not usually build an assistant only to tear it down again at the end of our process. We'd be more likely to establish an assistant and use it over time, creating new threads for new users. These threads might also persist for some time.

Here, however, we want to leave things as we found them. First, let's delete the file we uploaded.

We can get an assistant's file ids from the [assistants API](https://platform.openai.com/docs/api-reference/assistants). In this example, we have access to this data already, but let's assume we only have an assistant id to hand.

```php
// get the files from the assistant
$files = $fileservice->listAssistantFiles($assistantid);
foreach ($files['data'] as $finfo) {
    print $finfo['id'] . "\n";
    // unassign and delete
    $fileservice->unassignAndDeleteAssistantFile($assistantid, $finfo['id']);
}
```
`AssistantFiles::listAssistantFiles()` gives us an array of associated files. We can use the `id` field of each with `unassignAndDeleteAssistantFile()` to remove the association between assistant and file and then delete the file from the repository.

### Tidying up: delete the assistant
Finally, we delete the assistant altogether.

```php
// delete the assistant
$del = $asstservice->del($assistantid);
```
