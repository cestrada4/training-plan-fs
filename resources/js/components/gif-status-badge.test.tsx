import {render, screen} from '@testing-library/react'
import GifStatusBadge, { GifStatusBadgeProps } from './gif-status-badge'

type GifStatus = GifStatusBadgeProps["status"]

test.each([
    ["queued", "#6B7280", "Queued"],
    ["processing", "#FF6600", "Processing"],
    ["completed", "#17A150", "Completed"],
    ["failed", "#DC2626", "Failed"]
])(" with status '%s' displays the correct color '%s' and label '%s'", (status, color, label) => {
    render(<GifStatusBadge status={status as GifStatus}/>)

    const statusBadge = screen.getByTestId('status-badge')
    expect(statusBadge).toHaveStyle({
        color: color
    })
    expect(statusBadge).toHaveTextContent(label)
} )
