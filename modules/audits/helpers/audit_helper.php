<?php
// Helper functions for the Audit Management Module

/**
 * Get visual badge HTML for audit engagement status
 */
function getAuditStatusBadge($status) {
    switch ($status) {
        case 'Planned':
            $classes = 'bg-blue-50 text-blue-700 border-blue-200';
            $icon = 'calendar';
            break;
        case 'Fieldwork':
            $classes = 'bg-orange-50 text-orange-700 border-orange-200';
            $icon = 'play-circle';
            break;
        case 'Review':
            $classes = 'bg-purple-50 text-purple-700 border-purple-200';
            $icon = 'file-search';
            break;
        case 'Finalized':
            $classes = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $icon = 'check-circle-2';
            break;
        case 'Archived':
        default:
            $classes = 'bg-slate-100 text-slate-600 border-slate-200';
            $icon = 'archive';
            break;
    }

    return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {$classes}\">
                <i data-lucide=\"{$icon}\" class=\"w-3 h-3 mr-1 inline\"></i>
                " . htmlspecialchars($status) . "
            </span>";
}

/**
 * Get visual badge HTML for audit evaluation rating
 */
function getAuditRatingBadge($rating) {
    switch ($rating) {
        case 'Satisfactory':
            $classes = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            break;
        case 'Needs Improvement':
            $classes = 'bg-amber-100 text-amber-800 border-amber-200';
            break;
        case 'Unsatisfactory':
            $classes = 'bg-red-100 text-red-800 border-red-200';
            break;
        case 'Pending Rating':
        default:
            $classes = 'bg-slate-100 text-slate-600 border-slate-200';
            break;
    }

    return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {$classes}\">
                " . htmlspecialchars($rating) . "
            </span>";
}

/**
 * Get visual badge HTML for finding severity
 */
function getFindingSeverityBadge($severity) {
    switch ($severity) {
        case 'Critical':
            $classes = 'bg-red-100 text-red-800 border-red-200 font-bold';
            break;
        case 'High':
            $classes = 'bg-orange-100 text-orange-800 border-orange-200 font-semibold';
            break;
        case 'Medium':
            $classes = 'bg-amber-100 text-amber-800 border-amber-200';
            break;
        case 'Low':
        default:
            $classes = 'bg-blue-100 text-blue-800 border-blue-200';
            break;
    }

    return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs border {$classes}\">
                " . htmlspecialchars($severity) . "
            </span>";
}

/**
 * Get visual badge HTML for finding remediation status
 */
function getFindingStatusBadge($status) {
    switch ($status) {
        case 'Open':
            $classes = 'bg-red-50 text-red-700 border-red-200';
            break;
        case 'Under Remediation':
            $classes = 'bg-orange-50 text-orange-700 border-orange-200';
            break;
        case 'Pending Verification':
            $classes = 'bg-purple-50 text-purple-700 border-purple-200';
            break;
        case 'Closed':
        default:
            $classes = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            break;
    }

    return "<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {$classes}\">
                " . htmlspecialchars($status) . "
            </span>";
}

/**
 * Get visual badge HTML for control testing status
 */
function getControlStatusBadge($status) {
    switch ($status) {
        case 'Compliant':
            $classes = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $icon = 'check';
            break;
        case 'Non-Compliant':
            $classes = 'bg-red-50 text-red-700 border-red-200';
            $icon = 'x';
            break;
        case 'Partially Compliant':
            $classes = 'bg-amber-50 text-amber-700 border-amber-200';
            $icon = 'alert-circle';
            break;
        case 'Pending':
        default:
            $classes = 'bg-slate-100 text-slate-600 border-slate-200';
            $icon = 'clock';
            break;
    }

    return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium border {$classes}\">
                <i data-lucide=\"{$icon}\" class=\"w-3 h-3 mr-1 inline\"></i>
                " . htmlspecialchars($status) . "
            </span>";
}
