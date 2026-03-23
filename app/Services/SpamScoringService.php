<?php

namespace App\Services;

use Illuminate\Support\Str;

class SpamScoringService
{
    protected const INTERNAL_SCORE_CAP = 30;

    /**
     * Calculate spam score for email content.
     * 
     * @param string $subject Email subject
     * @param string $htmlContent Email HTML content
     * @param string $textContent Email plain text content
     * @param array $options Additional options (from email, reply-to, etc.)
     * @return array Score details with recommendations
     */
    public function calculateSpamScore(string $subject, string $htmlContent, string $textContent, array $options = []): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        // Combine all content for analysis
        $allText = $subject . ' ' . strip_tags($htmlContent) . ' ' . $textContent;

        // 1. Subject line checks (high impact)
        $subjectScore = $this->scoreSubject($subject);
        $score += $subjectScore['score'];
        $issues = array_merge($issues, $subjectScore['issues']);
        $warnings = array_merge($warnings, $subjectScore['warnings']);
        $recommendations = array_merge($recommendations, $subjectScore['recommendations']);

        // 2. Content checks (medium impact)
        $contentScore = $this->scoreContent($htmlContent, $textContent);
        $score += $contentScore['score'];
        $issues = array_merge($issues, $contentScore['issues']);
        $warnings = array_merge($warnings, $contentScore['warnings']);
        $recommendations = array_merge($recommendations, $contentScore['recommendations']);

        // 3. HTML structure checks (medium impact)
        $htmlScore = $this->scoreHtmlStructure($htmlContent);
        $score += $htmlScore['score'];
        $issues = array_merge($issues, $htmlScore['issues']);
        $warnings = array_merge($warnings, $htmlScore['warnings']);
        $recommendations = array_merge($recommendations, $htmlScore['recommendations']);

        // 4. Text analysis (low-medium impact)
        $textScore = $this->scoreTextContent($allText);
        $score += $textScore['score'];
        $issues = array_merge($issues, $textScore['issues']);
        $warnings = array_merge($warnings, $textScore['warnings']);
        $recommendations = array_merge($recommendations, $textScore['recommendations']);

        // 5. Sender information checks
        $senderScore = $this->scoreSenderInfo($options);
        $score += $senderScore['score'];
        $issues = array_merge($issues, $senderScore['issues']);
        $warnings = array_merge($warnings, $senderScore['warnings']);
        $recommendations = array_merge($recommendations, $senderScore['recommendations']);

        // Determine overall assessment
        $assessment = $this->getAssessment($score);
        $shouldBlock = $score >= $this->getBlockingThreshold();

        $scorePercent = $this->toPercent($score);

        return [
            'score' => $score,
            'score_percent' => $scorePercent,
            'assessment' => $assessment,
            'risk_tone' => $this->getRiskTone($score),
            'should_block' => $shouldBlock,
            'blocking_threshold' => $this->getBlockingThreshold(),
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations,
            'remarks' => $this->buildRemarks($issues, $warnings, $recommendations),
            'breakdown' => [
                'subject' => $subjectScore,
                'content' => $contentScore,
                'html_structure' => $htmlScore,
                'text_analysis' => $textScore,
                'sender_info' => $senderScore,
            ],
            'checks' => [
                $this->buildCheck('Subject line', $subjectScore),
                $this->buildCheck('Email content', $contentScore),
                $this->buildCheck('HTML structure', $htmlScore),
                $this->buildCheck('Text analysis', $textScore),
                $this->buildCheck('Sender details', $senderScore),
            ],
        ];
    }

    /**
     * Score subject line for spam indicators.
     */
    protected function scoreSubject(string $subject): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $trimmedSubject = trim($subject);
        if ($trimmedSubject === '') {
            return [
                'score' => 0,
                'issues' => [],
                'warnings' => ['Subject is empty'],
                'recommendations' => ['Add a clear subject line before sending to improve engagement and trust.'],
            ];
        }

        $subjectLower = strtolower($subject);

        // High-impact spam triggers
        $spammyWords = ['free', 'winner', 'congratulations', 'urgent', 'act now', 'limited time', 'special promotion', 'exclusive deal', 'risk free', 'guarantee', '100% free', 'no cost', 'click here', 'order now'];
        foreach ($spammyWords as $word) {
            if (strpos($subjectLower, $word) !== false) {
                $score += 3;
                $issues[] = "Subject contains spammy word: '{$word}'";
            }
        }

        // All caps subject
        if (strtoupper($subject) === $subject && strlen($subject) > 3) {
            $score += 2;
            $issues[] = 'Subject is in all caps';
            $recommendations[] = 'Use normal capitalization in subject';
        }

        // Excessive punctuation
        $exclamationCount = substr_count($subject, '!') + substr_count($subject, '?');
        if ($exclamationCount > 2) {
            $score += 2;
            $issues[] = 'Subject has excessive punctuation (' . $exclamationCount . ' punctuation marks)';
            $recommendations[] = 'Limit punctuation to 1-2 marks in subject';
        }

        // Subject length issues
        if (strlen($subject) < 5) {
            $score += 1;
            $warnings[] = 'Subject is very short';
            $recommendations[] = 'Use a more descriptive subject (5-50 characters)';
        } elseif (strlen($subject) > 100) {
            $score += 1;
            $warnings[] = 'Subject is very long';
            $recommendations[] = 'Keep subject under 100 characters';
        }

        // Numbers and special characters
        if (preg_match('/\$\d+/', $subject)) {
            $score += 2;
            $issues[] = 'Subject contains dollar amounts';
        }

        if (preg_match('/\d+%/', $subject)) {
            $score += 1;
            $warnings[] = 'Subject contains percentages';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score email content for spam indicators.
     */
    protected function scoreContent(string $htmlContent, string $textContent): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $plainFromHtml = trim(preg_replace('/\s+/', ' ', strip_tags($htmlContent)));
        $plainText = trim(preg_replace('/\s+/', ' ', $plainFromHtml . ' ' . $textContent));
        $textLower = strtolower($plainText);

        if ($plainText === '') {
            $score += 5;
            $issues[] = 'Email content is empty';
            $recommendations[] = 'Add meaningful email body text in the builder before sending.';
        } elseif (strlen($plainText) < 40) {
            $score += 3;
            $warnings[] = 'Email content is very short';
            $recommendations[] = 'Add more body copy so subscribers have clear context and intent.';
        }

        $placeholderPhrases = [
            'lorem ipsum',
            'your content here',
            'start writing',
            'drag and drop',
            'double click to edit',
        ];

        foreach ($placeholderPhrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                $score += 2;
                $warnings[] = 'Email content appears to include placeholder text';
                $recommendations[] = 'Replace placeholder text with final campaign copy.';
                break;
            }
        }

        // Check for spammy phrases
        $spammyPhrases = [
            'click here', 'order now', 'buy now', 'limited time', 'act now', 'don\'t delete',
            'free money', 'cash bonus', 'risk free', 'no obligation', 'special promotion',
            'exclusive offer', 'once in a lifetime', 'urgent', 'immediate action required'
        ];

        foreach ($spammyPhrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                $score += 2;
                $issues[] = "Content contains spammy phrase: '{$phrase}'";
            }
        }

        // Text-to-image ratio (if HTML has images)
        $imageCount = substr_count($htmlContent, '<img');
        $textLength = strlen($plainFromHtml);
        
        if ($imageCount > 0 && $textLength < 200) {
            $score += 3;
            $issues[] = 'Low text-to-image ratio';
            $recommendations[] = 'Add more text content or reduce images';
        }

        // Hidden text or tiny text
        if (preg_match('/style="[^"]*font-size:\s*(1px|2px|3px|0px)/', $htmlContent) ||
            preg_match('/style="[^"]*color:\s*(white|#ffffff|#fff)\s*.*background-color:\s*(white|#ffffff|#fff)/', $htmlContent)) {
            $score += 3;
            $issues[] = 'Contains hidden or tiny text';
            $recommendations[] = 'Remove hidden text and ensure readable font sizes';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score HTML structure for spam indicators.
     */
    protected function scoreHtmlStructure(string $htmlContent): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        // Check for proper HTML structure
        if (!preg_match('/<!DOCTYPE[^>]*>/', $htmlContent)) {
            $score += 1;
            $warnings[] = 'Missing DOCTYPE declaration';
            $recommendations[] = 'Add proper DOCTYPE declaration';
        }

        if (strpos($htmlContent, '<html') === false) {
            $score += 1;
            $warnings[] = 'Missing HTML tag';
        }

        if (strpos($htmlContent, '<head>') === false) {
            $score += 1;
            $warnings[] = 'Missing HEAD section';
        }

        if (strpos($htmlContent, '<body>') === false) {
            $score += 1;
            $warnings[] = 'Missing BODY tag';
        }

        // Check for inline styles (can be spam indicator)
        $styleCount = substr_count($htmlContent, 'style=');
        if ($styleCount > 20) {
            $score += 1;
            $warnings[] = 'Heavy use of inline styles (' . $styleCount . ' instances)';
            $recommendations[] = 'Consider using CSS in head section';
        }

        // Check for table-based layouts (common in spam)
        $tableCount = substr_count($htmlContent, '<table');
        if ($tableCount > 10) {
            $score += 1;
            $warnings[] = 'Complex table-based layout';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score text content for spam indicators.
     */
    protected function scoreTextContent(string $text): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        // Character frequency analysis
        $totalChars = strlen($text);
        $upperCount = preg_match_all('/[A-Z]/', $text);
        $upperPercentage = $totalChars > 0 ? ($upperCount / $totalChars) * 100 : 0;

        if ($upperPercentage > 30) {
            $score += 2;
            $issues[] = 'High percentage of uppercase letters (' . round($upperPercentage, 1) . '%)';
            $recommendations[] = 'Reduce use of uppercase letters';
        }

        // Excessive punctuation
        $punctuationCount = preg_match_all('/[!?.,;:]/', $text);
        $punctuationPercentage = $totalChars > 0 ? ($punctuationCount / $totalChars) * 100 : 0;

        if ($punctuationPercentage > 10) {
            $score += 1;
            $warnings[] = 'High punctuation density';
        }

        // Repetitive characters
        if (preg_match('/(.)\1{4,}/', $text)) {
            $score += 2;
            $issues[] = 'Contains repetitive characters';
        }

        // Suspicious URLs
        if (preg_match('/(bit\.ly|tinyurl\.com|short\.link)/', $text)) {
            $score += 1;
            $warnings[] = 'Contains URL shorteners';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score sender information for spam indicators.
     */
    protected function scoreSenderInfo(array $options): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $fromName = trim((string) ($options['from_name'] ?? ''));
        $fromEmail = $options['from_email'] ?? '';
        $replyToEmail = $options['reply_to'] ?? '';
        $deliveryServerType = strtolower(trim((string) ($options['delivery_server_type'] ?? '')));
        $deliveryServerFromEmail = trim((string) ($options['delivery_server_from_email'] ?? ''));
        $deliveryServerId = $options['delivery_server_id'] ?? null;
        $replyServerId = $options['reply_server_id'] ?? null;

        if ($fromName === '') {
            $score += 1;
            $warnings[] = 'From name is empty';
            $recommendations[] = 'Set a recognizable sender name to improve trust.';
        }

        // Check from email domain
        if (!empty($fromEmail)) {
            $domain = substr(strrchr($fromEmail, '@'), 1);
            
            // Suspicious domains
            $suspiciousDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
            if (in_array($domain, $suspiciousDomains)) {
                $score += 1;
                $warnings[] = 'Using free email provider as sender';
                $recommendations[] = 'Use a business domain for sending';
            }

            // Numeric domains or suspicious patterns
            if (preg_match('/\d{3,}/', $domain)) {
                $score += 2;
                $issues[] = 'Domain contains multiple numbers';
            }
        }

        // Mismatch between from and reply-to
        if (!empty($fromEmail) && !empty($replyToEmail) && $fromEmail !== $replyToEmail) {
            $fromDomain = substr(strrchr($fromEmail, '@'), 1);
            $replyDomain = substr(strrchr($replyToEmail, '@'), 1);
            
            if ($fromDomain !== $replyDomain) {
                $score += 1;
                $warnings[] = 'From and Reply-To domains differ';
            }
        }

        // Delivery/reply server context checks
        if (empty($deliveryServerId)) {
            $warnings[] = 'No specific delivery server selected';
            $recommendations[] = 'Use a stable, warmed-up delivery server when possible.';
        }

        if ($deliveryServerType === 'amazon-ses' && !empty($deliveryServerFromEmail) && !empty($fromEmail)) {
            if (strcasecmp($deliveryServerFromEmail, $fromEmail) !== 0) {
                $score += 1;
                $warnings[] = 'From email differs from SES server sender identity';
                $recommendations[] = 'Use the SES-verified sender identity configured on your delivery server.';
            }
        }

        if (!empty($replyToEmail) && empty($replyServerId)) {
            $warnings[] = 'Reply-To is set but no reply server selected';
            $recommendations[] = 'Attach a reply server to improve reply handling consistency.';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Get assessment based on score.
     */
    protected function getAssessment(int $score): string
    {
        if ($score <= 5) {
            return 'Low Risk';
        } elseif ($score <= 10) {
            return 'Medium Risk';
        } elseif ($score <= 15) {
            return 'High Risk';
        } else {
            return 'Very High Risk';
        }
    }

    /**
     * Get the threshold for blocking emails.
     */
    protected function getBlockingThreshold(): int
    {
        return (int) config('mailzen.spam_scoring.blocking_threshold', 15);
    }

    /**
     * Convert internal score to a 0-100 percentage.
     */
    protected function toPercent(int $score): int
    {
        return (int) round(min(100, max(0, ($score / self::INTERNAL_SCORE_CAP) * 100)));
    }

    /**
     * Get a UI tone for the risk summary.
     */
    protected function getRiskTone(int $score): string
    {
        if ($score <= 5) {
            return 'positive';
        }

        if ($score <= 10) {
            return 'warning';
        }

        return 'danger';
    }

    /**
     * Build a normalized checks payload for UI rendering.
     */
    protected function buildCheck(string $label, array $data): array
    {
        $issues = $data['issues'] ?? [];
        $warnings = $data['warnings'] ?? [];
        $recommendations = $data['recommendations'] ?? [];

        if (!empty($issues)) {
            $tone = 'danger';
        } elseif (!empty($warnings)) {
            $tone = 'warning';
        } else {
            $tone = 'positive';
        }

        $remarks = array_merge(
            array_map(fn ($text) => ['tone' => 'danger', 'text' => $text], $issues),
            array_map(fn ($text) => ['tone' => 'warning', 'text' => $text], $warnings),
            array_map(fn ($text) => ['tone' => 'positive', 'text' => $text], $recommendations)
        );

        if (empty($remarks)) {
            $remarks[] = ['tone' => 'positive', 'text' => 'No major spam trigger detected in this section.'];
        }

        return [
            'label' => $label,
            'score' => (int) ($data['score'] ?? 0),
            'tone' => $tone,
            'remarks' => $remarks,
        ];
    }

    /**
     * Build an aggregated remarks list.
     */
    protected function buildRemarks(array $issues, array $warnings, array $recommendations): array
    {
        return array_merge(
            array_map(fn ($text) => ['tone' => 'danger', 'text' => $text], $issues),
            array_map(fn ($text) => ['tone' => 'warning', 'text' => $text], $warnings),
            array_map(fn ($text) => ['tone' => 'positive', 'text' => $text], $recommendations)
        );
    }

    /**
     * Check if email should be blocked based on spam score.
     */
    public function shouldBlockEmail(string $subject, string $htmlContent, string $textContent, array $options = []): bool
    {
        $result = $this->calculateSpamScore($subject, $htmlContent, $textContent, $options);
        return $result['should_block'];
    }

    /**
     * Get quick spam score (0-100 scale).
     */
    public function getQuickSpamScore(string $subject, string $htmlContent, string $textContent): int
    {
        $result = $this->calculateSpamScore($subject, $htmlContent, $textContent);

        return $this->toPercent($result['score']);
    }
}
