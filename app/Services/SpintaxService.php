<?php

namespace App\Services;

use Illuminate\Support\Str;

class SpintaxService
{
    /**
     * Parse spintax content and return a randomized version.
     * 
     * Spintax format:
     * - {option1|option2|option3} - randomly selects one option
     * - [text1|text2|text3] - randomly selects one option (alternative syntax)
     * - Supports nested spinning: {Hello|Hi|Hey} {World|There|Universe}
     * 
     * @param string $content The content with spintax
     * @return string The spun content
     */
    public function spin(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        // Handle both {} and [] syntax for spinning
        $content = $this->parseSpintax($content);
        
        return $content;
    }

    /**
     * Parse spintax recursively.
     */
    protected function parseSpintax(string $content): string
    {
        // Handle {} syntax first
        while (preg_match('/\{([^{}]*)\}/', $content, $matches)) {
            $fullMatch = $matches[0];
            $options = explode('|', $matches[1]);
            $selectedOption = $this->selectRandomOption($options);
            $content = str_replace($fullMatch, $selectedOption, $content);
        }

        // Handle [] syntax (alternative)
        while (preg_match('/\[([^\[\]]*)\]/', $content, $matches)) {
            $fullMatch = $matches[0];
            $options = explode('|', $matches[1]);
            $selectedOption = $this->selectRandomOption($options);
            $content = str_replace($fullMatch, $selectedOption, $content);
        }

        // Recursively handle nested spintax
        if (preg_match('/[\{\[]/', $content)) {
            $content = $this->parseSpintax($content);
        }

        return $content;
    }

    /**
     * Select a random option from the available choices.
     */
    protected function selectRandomOption(array $options): string
    {
        if (empty($options)) {
            return '';
        }

        // Remove empty options and trim whitespace
        $options = array_filter($options, function($option) {
            return trim($option) !== '';
        });

        if (empty($options)) {
            return '';
        }

        $randomIndex = array_rand($options);
        return trim($options[$randomIndex]);
    }

    /**
     * Check if content contains spintax.
     */
    public function hasSpintax(string $content): bool
    {
        return preg_match('/[\{\[].*[\|\]]/', $content) > 0;
    }

    /**
     * Count the number of spintax patterns in content.
     */
    public function countSpintaxPatterns(string $content): int
    {
        $braceCount = preg_match_all('/\{[^{}]*\}/', $content);
        $bracketCount = preg_match_all('/\[[^\[\]]*\]/', $content);
        
        return $braceCount + $bracketCount;
    }

    /**
     * Generate multiple unique spun versions of the same content.
     * Useful for testing or generating variations.
     */
    public function generateMultipleSpins(string $content, int $count): array
    {
        $spins = [];
        $attempts = 0;
        $maxAttempts = $count * 10; // Prevent infinite loops

        while (count($spins) < $count && $attempts < $maxAttempts) {
            $spun = $this->spin($content);
            
            // Only add if it's unique (or if we don't have any spins yet)
            if (empty($spins) || !in_array($spun, $spins)) {
                $spins[] = $spun;
            }
            
            $attempts++;
        }

        return $spins;
    }

    /**
     * Validate spintax syntax.
     * Returns array of errors found, empty array if valid.
     */
    public function validateSpintax(string $content): array
    {
        $errors = [];
        
        // Check for unmatched braces
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        if ($openBraces !== $closeBraces) {
            $errors[] = 'Unmatched braces: ' . ($openBraces > $closeBraces ? 'missing closing braces' : 'missing opening braces');
        }

        // Check for unmatched brackets
        $openBrackets = substr_count($content, '[');
        $closeBrackets = substr_count($content, ']');
        if ($openBrackets !== $closeBrackets) {
            $errors[] = 'Unmatched brackets: ' . ($openBrackets > $closeBrackets ? 'missing closing brackets' : 'missing opening brackets');
        }

        // Check for empty spintax patterns
        if (preg_match('/\{\s*\|\s*\}/', $content) || preg_match('/\{\s*\}/', $content)) {
            $errors[] = 'Empty spintax patterns found with braces';
        }
        
        if (preg_match('/\[\s*\|\s*\]/', $content) || preg_match('/\[\s*\]/', $content)) {
            $errors[] = 'Empty spintax patterns found with brackets';
        }

        return $errors;
    }

    /**
     * Extract all spintax patterns from content.
     */
    public function extractPatterns(string $content): array
    {
        $patterns = [];
        
        // Extract {} patterns
        if (preg_match_all('/\{([^{}]*)\}/', $content, $matches)) {
            foreach ($matches[1] as $options) {
                $patterns[] = [
                    'type' => 'brace',
                    'options' => explode('|', $options),
                    'raw' => '{' . $options . '}'
                ];
            }
        }

        // Extract [] patterns
        if (preg_match_all('/\[([^\[\]]*)\]/', $content, $matches)) {
            foreach ($matches[1] as $options) {
                $patterns[] = [
                    'type' => 'bracket',
                    'options' => explode('|', $options),
                    'raw' => '[' . $options . ']'
                ];
            }
        }

        return $patterns;
    }
}
