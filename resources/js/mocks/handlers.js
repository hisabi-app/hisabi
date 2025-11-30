import { http, HttpResponse } from 'msw'

export const handlers = [
    http.delete('/api/v1/resource/:id', () => {
        return HttpResponse.json({ id: 1 })
    }),
    http.get('/api/v1/metrics/total-income', ({ request }) => {
        const url = new URL(request.url)
        const range = url.searchParams.get('range')
        return HttpResponse.json({
            data: {
                value: range === 'current-month' ? 3000 : 2000
            }
        })
    }),
    http.get('/api/v1/metrics/total-income-with-previous-higher', () => {
        return HttpResponse.json({
            data: {
                value: 3000,
                previous: 4000
            }
        })
    }),
    http.get('/api/v1/metrics/total-income-with-previous-lower', () => {
        return HttpResponse.json({
            data: {
                value: 3000,
                previous: 2000
            }
        })
    }),
];
