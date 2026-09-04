<?php

namespace Tests\Unit;

use App\Services\GifCompressionService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GifCompressionServiceTest extends TestCase
{
    public GifCompressionService $gifCompressionService;

    private int $maxWidth;

    protected function setUp(): void
    {
        $this->maxWidth = 480;
        $this->gifCompressionService = new GifCompressionService($this->maxWidth);
    }

    // Happy path
    public function test_returns_correct_target_fps_with_valid_params(): void
    {
        $frameCount = 120;
        $targetDurationSeconds = 60;
        $targetFps = $this->gifCompressionService->computeTargetFps($frameCount, $targetDurationSeconds);

        $shouldTargetFps = 2.0;
        $this->assertSame($shouldTargetFps, $targetFps);
    }

    // computeTargetFps should throw an exception if target duration seconds is zero
    public function test_should_throw_error_with_zero_target_duration_seconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->gifCompressionService->computeTargetFps(120, 0);
    }

    // computeTargetFps should throw an error if duration seconds is negative
    public function test_should_throw_error_with_negative_target_duration_seconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->gifCompressionService->computeTargetFps(120, -500);
    }

    public function test_returns_zero_fps_when_frame_count_is_zero(): void
    {
        $result = $this->gifCompressionService->computeTargetFps(0, 60);

        $this->assertSame(0.0, $result);
    }

    // test happy paths
    public function test_returns_original_dimensions_when_source_width_is_lesser_than_max_width(): void
    {
        $sourceWidth = 210;
        $sourceHeight = 110;

        $result = $this->gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);

        $this->assertSame([
            'width' => $sourceWidth,
            'height' => $sourceHeight,
        ], $result);
    }

    public function test_returns_original_dimensions_when_source_width_is_equals_max_width(): void
    {
        $sourceWidth = $this->maxWidth;
        $sourceHeight = 500;

        $result = $this->gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);

        $this->assertSame([
            'width' => $this->maxWidth,
            'height' => 500,
        ], $result);
    }

    public function test_returns_scaled_dimensions_when_source_width_is_greater_than_max_width(): void
    {
        $sourceWidth = 960;
        $sourceHeight = 500;

        // ratio should be 0.5 = $maxWidth / $sourceWidth
        $result = $this->gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);

        $this->assertSame([
            'width' => $this->maxWidth,
            'height' => 250,
        ], $result);
    }

    public function test_should_estimate_output_size_in_kilobytes_for_valid_dimensions_and_frame_count(): void
    {
        $expectedOutputKb = 0.2;
        $width = 30;
        $height = 20;
        $frameCount = 10;

        $outputKb = $this->gifCompressionService->estimateOutputSizeKb($frameCount, $width, $height);

        $this->assertSame($expectedOutputKb, $outputKb);
    }

    public function test_estimates_zero_size_when_frame_count_is_zero(): void
    {
        $result = $this->gifCompressionService->estimateOutputSizeKb(
            0,
            30,
            20,
        );

        $this->assertSame(0.0, $result);
    }
}
