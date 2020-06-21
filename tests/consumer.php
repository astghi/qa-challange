<?php

require './vendor/autoload.php';

// $streamName = 'li-stream-even';
function consumeFromStream($streamName) {
$numberOfRecordsPerBatch = 100;
$records = [];

$sharedConfig = [
    'endpoint' => 'http://localhost:4568',
    'region'  => 'eu-west-1',
    'version' => 'latest'
];

$kinesisClient = Aws\Kinesis\KinesisClient::factory($sharedConfig);

// get all shard ids
$res = $kinesisClient->describeStream([ 'StreamName' => $streamName ]);
$shardIds = $res->search('StreamDescription.Shards[].ShardId');

$count = 0;
$startTime = microtime(true);
 foreach ($shardIds as $shardId) {
    // echo "ShardId: $shardId\n";

    // get initial shard iterator
    $res = $kinesisClient->getShardIterator([
        'ShardId' => $shardId,
        'ShardIteratorType' => 'TRIM_HORIZON',
        'StreamName' => $streamName,
    ]);
    $shardIterator = $res->get('ShardIterator');

    do {
        // echo "Get Records\n";
        $res = $kinesisClient->getRecords([
            'Limit' => $numberOfRecordsPerBatch,
            'ShardIterator' => $shardIterator
        ]);
        $shardIterator = $res->get('NextShardIterator');
        $localCount = 0;
        foreach ($res->search('Records[].[SequenceNumber, Data]') as $data) {
            list($sequenceNumber, $item) = $data;
            $record = json_decode($item, true);
            $records[] = $record;
            $count++;
            $localCount++;
        }
        sleep(1);
    } while ($localCount > 0);
  }
  return $records;
}