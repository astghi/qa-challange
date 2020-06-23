<?php

declare(strict_types=1);

require "./src/client/consumer.php";
use Src\Data\TestDataBuilder;
use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase {

    private $testData = [];
    private $recordsFromEvenStream = [];
    private $recordsFromOddStream = [];

    public function setUp() {
        $builder = new TestDataBuilder;
        $this->testData = $builder->buildData();
        $this->recordsFromEvenStream = readFromStreamByName('li-stream-even');
        $this->recordsFromOddStream = readFromStreamByName('li-stream-odd');
    }

    private function isRecordInAStream($transactionId, $seed,$records) {     
        foreach($records as $record) {
            if($record['uuid'] === $transactionId && $record['seed'] == $seed) {
                return true;
            }
        }
        return false;
    }

    public function testEvenSeedsAreInEvenStream() {
        $responce = $this->testData['Valid Seed Even'];

        self::assertTrue(strpos($responce->status, '200 OK') !== false);
        self::assertTrue($this->isRecordInAStream($responce->transactionId, $responce->seed, $this->recordsFromEvenStream));
        self::assertFalse($this->isRecordInAStream($responce->transactionId, $responce->seed, $this->recordsFromOddStream));
    }

    public function testOddSeedsAreInOddStream() {
        $responce = $this->testData['Valid Seed Odd'];

        self::assertTrue(strpos($responce->status, '200 OK') !== false);
        self::assertFalse($this->isRecordInAStream($responce->transactionId, $responce->seed, $this->recordsFromEvenStream));
        self::assertTrue($this->isRecordInAStream($responce->transactionId, $responce->seed, $this->recordsFromOddStream));
    }

    public function testNonNumericSeedsFailToProcess() {
        $responce = $this->testData['Non Numeric Seed'];

        self::assertTrue(strpos($responce->status, '400 Bad Request') !== false);
    }
}