import {render, screen} from '@testing-library/react'
import GifStatusBadge, { GifStatusBadgeProps } from './gif-status-badge'

type GifStatus = GifStatusBadgeProps["status"]

test.each([
    ["queued", "#6B7280", "Queued"],
    ["processing", "#FF6600", "Processing"],
    ["completed", "#17A150", "Completed"],
    ["failed", "#DC2626", "Failed"]
])("if with status '%s', it should display the correct color '%s' and label '%s'", (status, color, label) => {
    render(<GifStatusBadge status={status as GifStatus}/>)

    const statusBadge = screen.getByTestId('status-badge')
    expect(statusBadge).toHaveStyle({
        color: color
    })
    expect(statusBadge).toHaveTextContent(label)
} )

test("should display Unknown with color #9CA3AF if GifStatus is not known", () => {
    // @ts-expect-error for testing
    render(<GifStatusBadge status="unknown"/>)

    expect(screen.getByTestId('status-badge')).toHaveTextContent("Unknown")
    expect(screen.getByTestId('status-badge')).toHaveStyle({
        color: "#9CA3AF"
    })
})

test("should call onClick if clicked on status badge", () => {
    const mockFn = jest.fn()
    render(<GifStatusBadge status="queued" onClick={mockFn} />)
    
    const statusBadgeEl = screen.getByTestId('status-badge')
    statusBadgeEl.click();
    expect(mockFn.mock.calls).toHaveLength(1)
})
