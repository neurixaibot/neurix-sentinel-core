<?php
/**
 * NEURIX AI • Sovereign Community Sentinel & Threat Neutralization Engine
 * 
 * Version: v105.0 Open-Source Community Core
 * Network: Solana Ecosystem (Base58 Standard)
 * License: MIT License
 * 
 * Official Portal: https://neurixai.in
 * Official X: https://x.com/neurix_ai_bot
 * Telegram Community: https://t.me/NeurixPortal
 * Telegram Bot Node: https://t.me/NeuriXAiIn_bot
 */

declare(strict_types=1);

namespace NeurixAI\Sentinel;

final class SentinelCore
{
    /**
     * Authorized Web3 ecosystem root domains for whitelist routing.
     */
    private const DEFAULT_WHITELIST = [
        'solana.com',
        'neurixai.in',
        'pump.fun',
        'raydium.io',
        'jup.ag',
        'dexscreener.com',
        't.me',
        'x.com',
        'twitter.com',
        'github.com'
    ];

    /**
     * Validates Solana Base58 public key / token contract address.
     * Solana addresses are strictly 32 to 44 alphanumeric characters excluding 0, O, I, l.
     */
    public static function validateSolanaAddress(string $address): bool
    {
        $pattern = '/^[1-9A-HJ-NP-za-km-z]{32,44}$/';
        return (bool)preg_match($pattern, trim($address));
    }

    /**
     * Sub-15ms Threat Scanner: Detects unauthorized links and zero-day drainers.
     */
    public static function scanPayloadForThreats(string $messageText, array$customWhitelist = []): array
    {
        $startTime = microtime(true);
        $whitelist = array_merge(self::DEFAULT_WHITELIST,$customWhitelist);
        
        // Extract all URLs from message payload
        $urlPattern = '/https?:\/\/[^\s<>"{}|\\^`]+[^\s<>"{}|\\^`.,;:?!]/ui';
        preg_match_all($urlPattern,$messageText, $matches);$detectedUrls = $matches[0] ?? [];$threats = [];

        foreach ($detectedUrls as$url) {
            $parsedHost = parse_url(strtolower($url), PHP_URL_HOST);
            if (!$parsedHost) {
                continue;
            }

            $isWhitelisted = false;
            foreach ($whitelist as$trustedDomain) {
                if ($parsedHost === $trustedDomain \vert{}\vert{} str_ends_with($parsedHost, '.' . $trustedDomain)) {$isWhitelisted = true;
                    break;
                }
            }

            if (!$isWhitelisted) {$threats[] = [
                    'url' => $url,
                    'host' => $parsedHost,
                    'severity' => 'CRITICAL_UNAUTHORIZED_LINK'
                ];
            }
        }

        $latencyMs = round((microtime(true) -$startTime) * 1000, 2);

        return [
            'is_clean' => empty($threats),
            'threats_detected' => count($threats),
            'threat_payloads' => $threats,
            'inspection_latency_ms' => $latencyMs
        ];
    }

    /**
     * Calculates kinetic raid soldier XP attribution.
     */
    public static function calculateRaidXP(bool $liked, bool$reposted, bool $replied, bool$quoted): int
    {
        $xp = 0;
        if ($liked)$xp += 10;
        if ($reposted)$xp += 30;
        if ($replied)$xp += 50;
        if ($quoted)$xp += 100;
        return $xp;
    }

    /**
     * Queries Google Gemini Neural AI Oracle with timeout protection.
     */
    public static function queryGeminiOracle(string $prompt, string$geminiApiKey): array
    {
        $startTime = microtime(true);
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $geminiApiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "You are Neurix AI, the sovereign security sentinel on Solana. Provide concise, expert Web3 intelligence. Query: " . $prompt
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        $latencyMs = round((microtime(true) -$startTime) * 1000, 2);

        if ($error \vert{}\vert{} !$response) {
            return [
                'status' => 'ERROR',
                'message' => 'Neural oracle timeout or communication fault.',
                'latency_ms' => $latencyMs
            ];
        }

        $decoded = json_decode((string)$response, true);
        $oracleAnswer =$decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'Zero-hallucination buffer empty.';

        return [
            'status' => 'SUCCESS',
            'answer' => trim($oracleAnswer),
            'latency_ms' => $latencyMs
        ];
    }
}
