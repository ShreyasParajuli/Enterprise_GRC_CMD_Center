<?php
/**
 * Asset Helper Functions
 * Phase 4: Intelligence & Classification Engine
 */

function calculateCiaScore($c, $i, $a) {
    return (int)$c + (int)$i + (int)$a;
}

function calculateAssetCriticality($score) {
    if ($score >= 12) return 'Critical';
    if ($score >= 9) return 'High';
    if ($score >= 6) return 'Medium';
    return 'Low';
}

function getAssetPriority($criticality) {
    switch ($criticality) {
        case 'Critical': return 'Immediate Attention';
        case 'High': return 'Prioritize';
        case 'Medium': return 'Monitor';
        case 'Low': return 'Routine';
        default: return 'Routine';
    }
}

function getCriticalityDescription($criticality) {
    switch ($criticality) {
        case 'Critical': return 'High confidentiality, integrity, and availability requirements indicate that compromise or prolonged disruption could significantly impact the organization.';
        case 'High': return 'Material business impact is expected if the asset is compromised or unavailable.';
        case 'Medium': return 'Moderate business impact with manageable operational consequences.';
        case 'Low': return 'Limited organizational impact if compromised or unavailable.';
        default: return 'Unknown business impact.';
    }
}

function getAssetBadgeClass($type, $value) {
    if ($type === 'criticality') {
        switch ($value) {
            case 'Critical': return 'bg-red-100 text-red-800 border-red-200';
            case 'High': return 'bg-orange-100 text-orange-800 border-orange-200';
            case 'Medium': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'Low': return 'bg-green-100 text-green-800 border-green-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
    
    if ($type === 'priority') {
        switch ($value) {
            case 'Immediate Attention': return 'bg-red-100 text-red-800 border-red-200';
            case 'Prioritize': return 'bg-orange-100 text-orange-800 border-orange-200';
            case 'Monitor': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'Routine': return 'bg-green-100 text-green-800 border-green-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
    
    if ($type === 'status') {
        switch ($value) {
            case 'Active': return 'bg-green-100 text-green-800 border-green-200';
            case 'Inactive': return 'bg-gray-100 text-gray-800 border-gray-200';
            case 'Maintenance': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'Retired': return 'bg-red-100 text-red-800 border-red-200';
            case 'Pending Deployment': return 'bg-blue-100 text-blue-800 border-blue-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    }
    
    return 'bg-gray-100 text-gray-800 border-gray-200';
}
