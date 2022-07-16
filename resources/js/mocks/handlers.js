import { graphql } from 'msw'

export const handlers = [
    graphql.mutation('DeleteResource', (req, res, ctx) => {
        return res(
            ctx.data({id: 1}),
        )
    }),
    graphql.query('valueMetricSampleQuery', (req, res, ctx) => {
        let data = {
            'valueMetricSampleQuery': JSON.stringify({
                'value': req.body.variables.range === 'current-month' ? 3000 : 2000
            })
        };

        return res(
            ctx.data(data),
        )
    }),
    graphql.query('valueMetricSampleQueryWithPreviousHigher', (req, res, ctx) => {
        let data = {
            'valueMetricSampleQueryWithPreviousHigher': JSON.stringify({
                'value': 3000,
                'previous': 4000
            })
        };

        return res(
            ctx.data(data),
        )
    }),
    graphql.query('valueMetricSampleQueryWithPreviousLower', (req, res, ctx) => {
        let data = {
            'valueMetricSampleQueryWithPreviousLower': JSON.stringify({
                'value': 3000,
                'previous': 2000
            })
        };

        return res(
            ctx.data(data),
        )
    }),
];