<?php

namespace App\Tests\Unit\Service;

use App\Entity\Quiz;
use App\Entity\Response;
use App\Entity\StressThreshold;
use App\Repository\ResponseRepository;
use App\Repository\StressThresholdRepository;
use App\Service\DiagnosticService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DiagnosticServiceTest extends TestCase
{
    private DiagnosticService $diagnosticService;
    private $responseRepositoryMock;
    private $thresholdRepositoryMock;
    private $emMock;

    protected function setUp(): void
    {
        $this->responseRepositoryMock = $this->createMock(ResponseRepository::class);
        $this->thresholdRepositoryMock = $this->createMock(StressThresholdRepository::class);
        $this->emMock = $this->createMock(EntityManagerInterface::class);

        $this->diagnosticService = new DiagnosticService(
            $this->responseRepositoryMock,
            $this->thresholdRepositoryMock,
            $this->emMock
        );
    }

    public function testCalculateScoreWithEmptyArray(): void
    {
        $score = $this->diagnosticService->calculateScore([]);
        $this->assertEquals(0, $score);
    }

    public function testCalculateScoreWithResponses(): void
    {
        $response1 = new Response();
        $response1->setPoints(10);
        $response2 = new Response();
        $response2->setPoints(20);

        $this->responseRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['id' => ['id1', 'id2']])
            ->willReturn([$response1, $response2]);

        $score = $this->diagnosticService->calculateScore(['id1', 'id2']);
        $this->assertEquals(30, $score);
    }

    public function testGetThresholdForScore(): void
    {
        $quiz = new Quiz();

        $threshold1 = new StressThreshold();
        $threshold1->setMinScore(0);
        $threshold1->setMaxScore(150);

        $threshold2 = new StressThreshold();
        $threshold2->setMinScore(151);
        $threshold2->setMaxScore(299);

        $threshold3 = new StressThreshold();
        $threshold3->setMinScore(300);
        $threshold3->setMaxScore(null);

        $quiz->addStressThreshold($threshold1);
        $quiz->addStressThreshold($threshold2);
        $quiz->addStressThreshold($threshold3);

        $this->assertSame($threshold1, $this->diagnosticService->getThresholdForScore(100, $quiz));
        $this->assertSame($threshold2, $this->diagnosticService->getThresholdForScore(200, $quiz));
        $this->assertSame($threshold3, $this->diagnosticService->getThresholdForScore(350, $quiz));
    }
}
