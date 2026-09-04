<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Day 3 seed — GifCompressionService
 *
 * Seeded into the training repo on Day 1 (non-scored training material — see the plan's seed-Day-1
 * convention). Untested on purpose. Day 3's task is to write a PHPUnit suite for this class.
 *
 * Modeled on Chris's own real GIF-generation pipeline (Cherrington Media engagement) — same shape,
 * fresh code, so the exercise is grounded in work he's actually done rather than a generic textbook
 * example.
 */
class GifCompressionService
{
    public function __construct(private int $maxWidthPx = 480) {}

    /**
     * Given a raw frame count and a target duration in seconds, compute the frames-per-second
     * needed to fit the animation into that duration.
     */
    public function computeTargetFps(int $frameCount, float $targetDurationSeconds): float
    {
        if ($targetDurationSeconds <= 0) {
            throw new InvalidArgumentException('targetDurationSeconds must be positive');
        }

        return round($frameCount / $targetDurationSeconds, 2);
    }

    /**
     * Given a source width/height, compute the output height that preserves aspect ratio when
     * scaled down to maxWidthPx. Returns the original dimensions unchanged if already narrower
     * than maxWidthPx.
     *
     * @return array{width: int, height: int}
     */
    public function computeScaledDimensions(int $sourceWidth, int $sourceHeight): array
    {
        if ($sourceWidth <= $this->maxWidthPx) {
            return ['width' => $sourceWidth, 'height' => $sourceHeight];
        }

        $ratio = $this->maxWidthPx / $sourceWidth;

        return [
            'width' => $this->maxWidthPx,
            'height' => (int) round($sourceHeight * $ratio),
        ];
    }

    /**
     * Estimate output file size in KB given frame count, width, and height. Used to warn the user
     * before processing starts if the output is likely to exceed the app's 25MB upload limit.
     */
    public function estimateOutputSizeKb(int $frameCount, int $width, int $height): float
    {
        $bytesPerFrame = $width * $height * 0.03; // rough GIF compression heuristic

        return round(($frameCount * $bytesPerFrame) / 1024, 1);
    }
}
