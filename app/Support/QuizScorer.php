<?php

namespace App\Support;

/**
 * Pure (framework-free) server-side quiz validation and scoring.
 *
 * Questions follow the `quizzes.contens` contract. Legacy questions
 * ({question, choices:[{text,is_correct}]}) are treated as single_choice,
 * 1 point, required.
 */
class QuizScorer
{
    public const CHOICE_TYPES = ['single_choice', 'dropdown', 'true_false'];
    public const TYPES = [
        'single_choice', 'multiple_select', 'dropdown', 'true_false',
        'short_text', 'number', 'long_answer',
    ];

    /**
     * Apply defaults: missing/unknown type => single_choice, points => 1, required => true.
     */
    public static function normalizeQuestion($question): array
    {
        $q = is_array($question) ? $question : [];
        $type = $q['type'] ?? null;
        $q['type'] = in_array($type, self::TYPES, true) ? $type : 'single_choice';

        $points = $q['points'] ?? 1;
        $q['points'] = is_numeric($points) && (int) $points >= 1 ? (int) $points : 1;

        $required = $q['required'] ?? true;
        if (!is_bool($required)) {
            $required = filter_var($required, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        }
        $q['required'] = $required;

        $q['choices'] = isset($q['choices']) && is_array($q['choices']) ? array_values($q['choices']) : [];
        $q['validation'] = isset($q['validation']) && is_array($q['validation']) ? $q['validation'] : [];

        return $q;
    }

    /**
     * Validate a submission. Returns [errors, normalizedValuesByIndex].
     * errors is a map "responses.N" => message; empty when valid.
     * Normalized values: int|null (choice types), int[] (multiple_select),
     * string|null (text types), int|float|null (number).
     */
    public static function validate(array $questions, $responses): array
    {
        $questions = array_values($questions);
        $count = count($questions);
        $errors = [];
        $values = [];

        if (!is_array($responses) || count($responses) !== $count) {
            return [['responses' => "Expected exactly {$count} responses, one per question."], []];
        }

        $byIndex = [];
        foreach (array_values($responses) as $pos => $entry) {
            if (!is_array($entry) || !array_key_exists('index', $entry) || !self::isIntLike($entry['index'])) {
                $errors["responses.{$pos}"] = 'Each response must have an integer "index" and a "value".';
                continue;
            }
            $idx = (int) $entry['index'];
            if ($idx < 0 || $idx >= $count) {
                $errors["responses.{$pos}"] = "Question index {$idx} is out of range.";
                continue;
            }
            if (array_key_exists($idx, $byIndex)) {
                $errors["responses.{$pos}"] = "Duplicate response for question " . ($idx + 1) . '.';
                continue;
            }
            $byIndex[$idx] = $entry['value'] ?? null;
        }
        if ($errors) {
            return [$errors, []];
        }

        foreach ($questions as $i => $raw) {
            $q = self::normalizeQuestion($raw);
            $label = 'Question ' . ($i + 1);
            [$error, $value] = self::validateOne($q, $byIndex[$i] ?? null, $label);
            if ($error !== null) {
                $errors["responses.{$i}"] = $error;
            }
            $values[$i] = $value;
        }

        return [$errors, $values];
    }

    private static function validateOne(array $q, $value, string $label): array
    {
        $type = $q['type'];
        $v = $q['validation'];
        $choiceCount = count($q['choices']);
        $required = $q['required'];

        if (in_array($type, self::CHOICE_TYPES, true)) {
            if ($value === null || $value === '') {
                return [$required ? "{$label} is required." : null, null];
            }
            if (!self::isIntLike($value) || (int) $value < 0 || (int) $value >= $choiceCount) {
                return ["{$label}: selected choice is out of range.", null];
            }
            return [null, (int) $value];
        }

        if ($type === 'multiple_select') {
            if ($value === null || $value === []) {
                return [$required ? "{$label} is required: select at least one option." : null, []];
            }
            if (!is_array($value)) {
                return ["{$label}: expected a list of selected choices.", []];
            }
            $set = [];
            foreach ($value as $item) {
                if (!self::isIntLike($item) || (int) $item < 0 || (int) $item >= $choiceCount) {
                    return ["{$label}: selected choice is out of range.", []];
                }
                $set[(int) $item] = true;
            }
            $set = array_keys($set);
            sort($set);
            return [null, $set];
        }

        if ($type === 'short_text' || $type === 'long_answer') {
            if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value)) {
                return ["{$label}: expected a text answer.", null];
            }
            $text = $value === null ? '' : trim((string) $value);
            if ($text === '') {
                return [$required ? "{$label} is required." : null, null];
            }
            if (isset($v['max_length']) && is_numeric($v['max_length'])
                && mb_strlen($text) > (int) $v['max_length']) {
                return ["{$label}: answer must be at most " . (int) $v['max_length'] . ' characters.', null];
            }
            return [null, $text];
        }

        // number
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return [$required ? "{$label} is required." : null, null];
        }
        if (is_bool($value) || !is_numeric(is_string($value) ? trim($value) : $value)) {
            return ["{$label}: answer must be a number.", null];
        }
        $num = (is_string($value) ? trim($value) : $value) + 0;
        if (!is_finite((float) $num)) {
            return ["{$label}: answer must be a number.", null];
        }
        if (!empty($v['integer']) && floor((float) $num) != (float) $num) {
            return ["{$label}: answer must be a whole number.", null];
        }
        if (isset($v['min']) && is_numeric($v['min']) && (float) $num < (float) $v['min']) {
            return ["{$label}: answer must be at least {$v['min']}.", null];
        }
        if (isset($v['max']) && is_numeric($v['max']) && (float) $num > (float) $v['max']) {
            return ["{$label}: answer must be at most {$v['max']}.", null];
        }
        return [null, $num];
    }

    /**
     * Score already-validated values (output of validate()).
     */
    public static function score(array $questions, array $values): array
    {
        $answers = [];
        $earned = 0;
        $max = 0;
        $pending = false;

        foreach (array_values($questions) as $i => $raw) {
            $q = self::normalizeQuestion($raw);
            $type = $q['type'];
            $points = $q['points'];
            $value = $values[$i] ?? null;
            $choices = $q['choices'];
            $max += $points;

            $text = '';
            $isCorrect = false;
            $status = 'auto';

            if (in_array($type, self::CHOICE_TYPES, true)) {
                if ($value !== null) {
                    $text = self::choiceText($choices[$value] ?? null);
                    $isCorrect = self::isTruthy($choices[$value]['is_correct'] ?? false);
                }
            } elseif ($type === 'multiple_select') {
                $value = is_array($value) ? $value : [];
                $correct = [];
                foreach ($choices as $ci => $c) {
                    if (self::isTruthy(is_array($c) ? ($c['is_correct'] ?? false) : false)) {
                        $correct[] = $ci;
                    }
                }
                $text = implode(', ', array_map(fn ($ci) => self::choiceText($choices[$ci] ?? null), $value));
                $isCorrect = $value !== [] && $value === $correct;
            } elseif ($type === 'short_text') {
                if ($value !== null) {
                    $text = (string) $value;
                    $needle = self::normText($value);
                    foreach ((array) ($q['accepted_answers'] ?? []) as $accepted) {
                        if (is_scalar($accepted) && self::normText($accepted) === $needle) {
                            $isCorrect = true;
                            break;
                        }
                    }
                }
            } elseif ($type === 'number') {
                if ($value !== null) {
                    $text = (string) $value;
                    $expected = $q['expected_number'] ?? null;
                    $isCorrect = is_numeric($expected)
                        && abs((float) $value - (float) $expected) <= 1e-9 * max(1.0, abs((float) $expected));
                }
            } else { // long_answer
                if ($value !== null) {
                    $text = (string) $value;
                    $isCorrect = null;
                    $status = 'pending';
                    $pending = true;
                }
            }

            $awarded = $status === 'pending' ? null : ($isCorrect ? $points : 0);
            $earned += (int) $awarded;

            $answers[] = [
                'question_index' => $i,
                'question' => (string) ($q['question'] ?? ''),
                'type' => $type,
                'text' => $text,
                'response' => $value,
                'is_correct' => $isCorrect,
                'points_awarded' => $awarded,
                'max_points' => $points,
                'review_status' => $status,
            ];
        }

        return [
            'answers' => $answers,
            'points_earned' => $earned,
            'points_max' => $max,
            'pending_review' => $pending,
            'score' => $max > 0 ? (int) round($earned / $max * 100) : 0,
        ];
    }

    private static function choiceText($choice): string
    {
        return is_array($choice) ? (string) ($choice['text'] ?? '') : '';
    }

    private static function normText($s): string
    {
        return mb_strtolower(trim((string) $s));
    }

    private static function isTruthy($v): bool
    {
        if (is_string($v)) {
            return in_array(strtolower(trim($v)), ['1', 'true', 'yes'], true);
        }
        return (bool) $v;
    }

    private static function isIntLike($v): bool
    {
        return is_int($v) || (is_string($v) && preg_match('/^\d+$/', $v) === 1)
            || (is_float($v) && floor($v) == $v);
    }
}
