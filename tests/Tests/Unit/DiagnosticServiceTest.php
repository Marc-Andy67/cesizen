<?php

namespace App\Tests\Unit;

use App\Entity\Response;
use App\Entity\StressThreshold;
use App\Repository\ResponseRepository;
use App\Repository\StressThresholdRepository;
use App\Service\DiagnosticService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiagnosticService::class)]
class DiagnosticServiceTest extends TestCase
{
    private DiagnosticService $service;
    private $responseRepository;
    private $thresholdRepository;
    private $em;

    protected function setUp(): void
    {
        $this->responseRepository = $this->createMock(ResponseRepository::class);
        $this->thresholdRepository = $this->createMock(StressThresholdRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new DiagnosticService(
            $this->responseRepository,
            $this->thresholdRepository,
            $this->em
        );
    }

    public function testCalculateScoreWithEmptyResponses(): void
    {
        $score = $this->service->calculateScore([]);
        $this->assertSame(0, $score);
    }

    public function testCalculateScoreWithValidResponses(): void
    {
        $response1 = new Response();
        $response1->setPoints(100);
        $response2 = new Response();
        $response2->setPoints(45);

        $this->responseRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['id' => [1, 2]])
            ->willReturn([$response1, $response2]);

        $score = $this->service->calculateScore([1, 2]);
        $this->assertSame(145, $score);
    }

    public function testGetThresholdForScoreLowStress(): void
    {
        $quiz = new \App\Entity\Quiz();
        $thresholdLow = new StressThreshold();
        $thresholdLow->setMinScore(0);
        $thresholdLow->setMaxScore(149);
        $quiz->addStressThreshold($thresholdLow);

        $thresholdHigh = new StressThreshold();
        $thresholdHigh->setMinScore(300);
        $thresholdHigh->setMaxScore(null);
        $quiz->addStressThreshold($thresholdHigh);

        $result = $this->service->getThresholdForScore(100, $quiz);
        $this->assertSame($thresholdLow, $result);
    }

    public function testGetThresholdForScoreHighStress(): void
    {
        $quiz = new \App\Entity\Quiz();
        $thresholdLow = new StressThreshold();
        $thresholdLow->setMinScore(0);
        $thresholdLow->setMaxScore(149);
        $quiz->addStressThreshold($thresholdLow);

        $thresholdHigh = new StressThreshold();
        $thresholdHigh->setMinScore(300);
        $thresholdHigh->setMaxScore(null);
        $quiz->addStressThreshold($thresholdHigh);

        $result = $this->service->getThresholdForScore(350, $quiz);
        $this->assertSame($thresholdHigh, $result);
    }

    public function testGetThresholdForScoreNoMatch(): void
    {
        $quiz = new \App\Entity\Quiz();
        $result = $this->service->getThresholdForScore(100, $quiz);
        $this->assertNull($result);
    }
}