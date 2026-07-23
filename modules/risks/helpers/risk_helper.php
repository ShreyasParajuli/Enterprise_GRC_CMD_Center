<?php
// Helper functions for the risk module

/**
 * Calculate risk score based on likelihood and impact
 */
function calculateRiskScore($likelihood, $impact) {
    return (int)$likelihood * (int)$impact;
}

/**
 * Get Risk Level Classification based on score
 */
function getRiskLevel($score) {
    if ($score >= 16) return 'Critical';
    if ($score >= 11) return 'High';
    if ($score >= 6) return 'Medium';
    return 'Low';
}

/**
 * Get color classes for risk score severity
 */
function getRiskScoreBadgeClasses($score) {
    if ($score >= 16) return 'bg-red-100 text-red-800 border-red-200';       // Critical: Red
    if ($score >= 11) return 'bg-orange-100 text-orange-800 border-orange-200'; // High: Orange
    if ($score >= 6) return 'bg-yellow-100 text-yellow-800 border-yellow-200';  // Medium: Yellow
    return 'bg-green-100 text-green-800 border-green-200';                      // Low: Green
}

/**
 * Get Recommended Treatment based on score
 */
function getRecommendedTreatment($score) {
    if ($score >= 16) return 'Immediate Action';
    if ($score >= 11) return 'Mitigate';
    if ($score >= 6) return 'Monitor';
    return 'Accept';
}

/**
 * Summarize Risks by Level
 */
function summarizeRisks($risks) {
    $summary = [
        'Total' => count($risks),
        'Critical' => 0,
        'High' => 0,
        'Medium' => 0,
        'Low' => 0
    ];

    foreach ($risks as $risk) {
        $score = calculateRiskScore($risk['likelihood'], $risk['impact']);
        $level = getRiskLevel($score);
        $summary[$level]++;
    }

    return $summary;
}
