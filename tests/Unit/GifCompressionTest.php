<?php

namespace Tests\Unit;

use App\Services\GifCompressionService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GifCompressionTest extends TestCase
{
    public GifCompressionService $gifCompressionService;
    private int $maxWidth;

    public function setUp(): void {
        $this->maxWidth = 480;
        $this->gifCompressionService = new GifCompressionService($this->maxWidth);
    }

    // Happy path
    public function test_returns_correct_target_fps_with_valid_params(): void {
        $frameCount = 120;
        $targetDurationSeconds = 60;
        $targetFps = $this->gifCompressionService->computeTargetFps($frameCount, $targetDurationSeconds);

        $shouldTargetFps = 2.0;
        $this->assertEquals($shouldTargetFps, $targetFps);
    }

    // computeTargetFps should throw an exception if target duration seconds is zero
    public function test_should_throw_error_with_zero_target_duration_seconds(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->gifCompressionService->computeTargetFps(120,0);
    }

    // computeTargetFps should throw an error if duration seconds is negative
    public function test_should_throw_error_with_negative_target_duration_seconds(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->gifCompressionService->computeTargetFps(120, -500);
    }

    // a zero frameCount will always result to a zero target fps, I think it's okay to expect an invalid argument exception if a framescoutn is zero
    public function test_should_throw_error_with_zero_frame_count(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->gifCompressionService->computeTargetFps(0,60);
    }


    // test happy paths
    public function test_returns_original_dimensions_when_source_width_is_lesser_than_max_width(): void {
        $sourceWidth = 210;
        $sourceHeight = 110;

        $result = $this->gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);

        $this->assertSame([
            "width" => $sourceWidth,
            "height" => $sourceHeight
        ], $result);
    }

    public function test_returns_original_dimensions_when_source_width_is_equals_max_width(): void {
        $sourceWidth = $this->maxWidth;
        $sourceHeight = 500;

        // ratio should be 0.5 = $maxWidth / $sourceWidth
        $result = $this->gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);
        
        $this->assertSame([
            "width" => $this->maxWidth,
            "height" => 500
        ], $result);
    }


    public function test_returns_scaled_dimensions_when_source_width_is_greater_than_max_width(): void {
        $sourceWidth = 960;
        $sourceHeight = 500;

        // ratio should be 0.5 = $maxWidth / $sourceWidth
        $result = $this->gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);
        
        $this->assertSame([
            "width" => $this->maxWidth,
            "height" => 250
        ], $result);
    }

    public static function nonPositiveDimensions(): array {
        // sourceWidth | sourceHeight | maxWidth
        return [
            "zero source width" =>  [0, 100, 200],
            "zero source height" => [50, 0, 200],
            "zero maxWidth" =>      [50, 100, 0],
        ];
    }

    // I think a non positive dimension makes no sense, including maxWidthPx, cause that would always generate an image that has nothing, unless a negative width has a special meaning
    #[DataProvider('nonPositiveDimensions')]
    public function test_should_throw_invalid_exception_when_dimensions_are_zero_or_lesser(int $sourceWidth, int $sourceHeight, $maxWidthPx): void {
        $this->expectException(InvalidArgumentException::class);
        $gifCompressionService = new GifCompressionService($maxWidthPx);
        $gifCompressionService->computeScaledDimensions($sourceWidth, $sourceHeight);
    }


    public function test_should_estimate_output_size_in_kilobytes_for_valid_dimensions_and_frame_count(): void {
        $expectedOutputKb = 0.2;
        $width = 30;
        $height = 20;
        $frameCount = 10;

        $outputKb = $this->gifCompressionService->estimateOutputSizeKb($width, $height, $frameCount);

        $this->assertSame($expectedOutputKb, $outputKb);
    }


    public static function nonPositiveParams(): array {
        return [
            "zero frame count"      => [0, 30, 20],
            "zero input width"      => [10, 0, 20],
            "zero input height"     => [10, 0, 20],
            "negative frame count"  => [-10, 30, 20],
            "negative input width"  => [10, -30, 20],
            "negative input height" => [10, 30, -20]
        ];
    }

    // It would also would not make sense if any of the inputs are zero, 
    // although the function accepts it, it still wouldn't make sense and something has gone wrong with the gif
    // if its framecount/width/height is zero
    #[DataProvider('nonPositiveParams')]
    public function test_should_throw_an_invalid_argument_exception_for_non_positive_framecount_and_dimensions(int $frameCount, int $width, int $height): void {
        $this->expectException(InvalidArgumentException::class);
        $this->gifCompressionService->estimateOutputSizeKb($frameCount, $width, $height);
    }
}
