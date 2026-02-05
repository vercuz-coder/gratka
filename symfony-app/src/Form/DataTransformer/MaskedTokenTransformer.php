<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Masks token for display, preserves original on submit unless changed.
 */
class MaskedTokenTransformer implements DataTransformerInterface
{
    private const MASK_PATTERN = '%s...%s';
    private const VISIBLE_CHARS = 4;

    private ?string $originalToken = null;

    /**
     * Transform token to masked version for display.
     */
    public function transform(mixed $value): mixed
    {
        if (!$value || !is_string($value)) {
            return '';
        }

        $this->originalToken = $value;

        if (strlen($value) <= self::VISIBLE_CHARS * 2) {
            return str_repeat('*', strlen($value));
        }

        return sprintf(
            self::MASK_PATTERN,
            substr($value, 0, self::VISIBLE_CHARS),
            substr($value, -self::VISIBLE_CHARS)
        );
    }

    /**
     * Reverse transform: use new value or keep original if masked value submitted.
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!$value || !is_string($value)) {
            return null;
        }

        // If user submitted the masked version, keep original token
        if ($this->originalToken && $value === $this->transform($this->originalToken)) {
            return $this->originalToken;
        }

        // New token entered
        return $value;
    }
}
