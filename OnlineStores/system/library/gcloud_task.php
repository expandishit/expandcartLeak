<?php
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Protobuf\Timestamp;

class GCloudTask
{

   Private $projectId = 'expandcart';
   Private $locationId = 'us-central1';
   Private $queueId = 'expandcart-tasks-staging';
   
   public function create($url,$payload, $seconds = 0)
   {
        // Instantiate the client and queue name.

        $client = new CloudTasksClient([
            'credentials'=>'expandcart-9c86f12a3b91.json'
        ]);
        $queueName = $client->queueName($this->projectId, $this->locationId, $this->queueId);

        // Create an Http Request Object.
        $httpRequest = new HttpRequest();
        // The full url path that the task request will be sent to.
        $httpRequest->setUrl($url);
        // POST is the default HTTP method, but any HTTP method can be used.
        $httpRequest->setHttpMethod(HttpMethod::POST);
        // Setting a body value is only compatible with HTTP POST and PUT requests.

        if (isset($payload)) {
            $httpRequest->setBody(json_encode($payload));

        }

        //Calculate Schedule time...
        $future_time_seconds = time() + $seconds;
        $schedule_time = new Timestamp();
        $schedule_time->setSeconds($future_time_seconds);
        $schedule_time->setNanos(0);

        // Create a Cloud Task object.
        $task = new Task();

        $task->setHttpRequest($httpRequest);
        $task->setScheduleTime($schedule_time);
        // Send request and print the task name.
        $response = $client->createTask($queueName, $task);
        return $response;
   }

}
