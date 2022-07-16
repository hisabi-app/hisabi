import { graphql } from 'msw'

export const handlers = [
    graphql.mutation('DeleteResource', (req, res, ctx) => {
        return res(
            ctx.data({id: 1}),
        )
    }),
    graphql.query('valueMetricSampleQuery', (req, res, ctx) => {
        return res(
            ctx.data({
                'valueMetricSampleQuery': req.body.variables.range === 'current-month' ? 3000 : 2000
            }),
        )
    }),
];