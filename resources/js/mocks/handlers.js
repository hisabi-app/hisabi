import { rest } from 'msw'

export const handlers = [
    rest.delete('http://localhost:3000/api/v1/resource/:id', (req, res, ctx) => {
        return res(ctx.json({ id: 1 }))
    }),
    rest.get('http://localhost:3000/api/v1/metrics/total-income', (req, res, ctx) => {
        const range = req.url.searchParams.get('range')
        return res(ctx.json({
            data: {
                value: range === 'current-month' ? 3000 : 2000
            }
        }))
    }),
    rest.get('http://localhost:3000/api/v1/metrics/total-income-with-previous-higher', (req, res, ctx) => {
        return res(ctx.json({
            data: {
                value: 3000,
                previous: 4000
            }
        }))
    }),
    rest.get('http://localhost:3000/api/v1/metrics/total-income-with-previous-lower', (req, res, ctx) => {
        return res(ctx.json({
            data: {
                value: 3000,
                previous: 2000
            }
        }))
    }),
];
