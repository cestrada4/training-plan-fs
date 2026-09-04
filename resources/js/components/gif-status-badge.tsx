// Day 2 seed — GifStatusBadge
// Seeded into the training repo on Day 1 (non-scored training material — see the plan's seed-Day-1
// rule). The member writes a Jest + React Testing Library suite against this component on Day 2.
// The component is intentionally untested — it works, but has no test coverage.

import React from 'react';

export interface GifStatusBadgeProps {
    status: 'queued' | 'processing' | 'completed' | 'failed';
    onClick?: (status: string) => void;
}

export function GifStatusBadge({ status, onClick }: GifStatusBadgeProps) {
    const config: Record<string, { label: string; color: string }> = {
        queued: { label: 'Queued', color: '#6B7280' },
        processing: { label: 'Processing', color: '#FF6600' },
        completed: { label: 'Completed', color: '#17A150' },
        failed: { label: 'Failed', color: '#DC2626' },
    };

    const current = config[status];

    // If status is unrecognized, the component renders "Unknown" in gray
    // — this is the edge case the member must test
    if (!current) {
        return (
            <span
                data-testid="status-badge"
                style={{ color: '#9CA3AF', fontWeight: 600 }}
                onClick={onClick ? () => onClick(status) : undefined}
            >
                Unknown
            </span>
        );
    }

    return (
        <span
            data-testid="status-badge"
            style={{ color: current.color, fontWeight: 600 }}
            onClick={onClick ? () => onClick(status) : undefined}
        >
            {current.label}
        </span>
    );
}

export default GifStatusBadge;
