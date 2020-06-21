<?php

// this is to make different requests to create test data 

include "TestDataEntry.php";

class TestDataBuilder {
    private $testData = [];

    private function getResponceHeaders($seed) {
    $transactionId = '';
    // echo "Calling for : $seed ...";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:9000/route/" . $seed);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $response = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);

    $headers = [];
    $response = rtrim($response);
    $data = explode("\n",$response);
    $headers['status'] = $data[0];
    array_shift($data);

    foreach($data as $part){

    //some headers will contain ":" character (Location for example), and the part after ":" will be lost
    $middle = explode(":",$part,2);

    //Supress warning message if $middle[1] does not exist
    if ( !isset($middle[1]) ) { $middle[1] = null; }

    $headers[trim($middle[0])] = trim($middle[1]);
}
    return $headers;
}

    private function makeRequestForValidSeed($seed) {
        $data = new TestDataEntry;
        $type = '';
        $headers = $this->getResponceHeaders($seed);
        $data->seed = $seed;
        $data->status = $headers['status'];

        if($seed % 2 === 0) {
            $data->stream = 'li-stream-even';
            $type = 'Valid Seed Even';
        } else {
            $data->stream = 'li-stream-odd';
            $type = 'Valid Seed Odd';
        }
    
        $data->transactionId = $headers['X-Transaction-Id'];
        $this->testData[$type] = $data;
    }

    private function makeRequestForInvalidSeed($seed) {
        $data = new TestDataEntry;
        $type = '';
        $headers = $this->getResponceHeaders($seed);

        $data->seed = $seed;
        $data->status = $headers['status'];
        $data->stream = '';
        $data->transactionId = '';

        $this->testData['Non Numeric Seed'] = $data;
    }

    public function buildData() {
        $this->makeRequestForValidSeed('1');
        $this->makeRequestForValidSeed('2');
        $this->makeRequestForInvalidSeed('A');

        return $this->testData;
    }
}