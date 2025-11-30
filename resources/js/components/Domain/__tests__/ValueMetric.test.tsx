import * as React from 'react'
import { screen, render, waitFor, waitForElementToBeRemoved } from '@testing-library/react';
import '@testing-library/jest-dom';

import ValueMetric from '../ValueMetric';
import { RangeProvider } from '@/contexts/RangeContext';

const renderWithProvider = (component: React.ReactElement) => {
    return render(
        <RangeProvider>
            {component}
        </RangeProvider>
    );
};

it('it fetches data once loaded and stop loader once data is fetched', async () => {
    renderWithProvider(<ValueMetric name="Some Metric" metric="totalIncome" helpText={undefined} />);

    expect(screen.getByTestId('loading-view')).toBeVisible()

    await waitForElementToBeRemoved(() => screen.queryByTestId('loading-view'))
    await waitFor(() => expect(screen.getByText(/some metric/i)).toBeVisible())
});

it('it displays decrease if previous value is higher than current', async () => {
    // Add test-specific handler for previous higher case
    const { server } = require('../../../mocks/server');
    const { rest } = require('msw');

    server.use(
        rest.get('http://localhost:3000/api/v1/metrics/total-income', (req, res, ctx) => {
            return res(ctx.json({
                data: {
                    value: 3000,
                    previous: 4000
                }
            }))
        })
    );

    renderWithProvider(<ValueMetric name="Some Metric" metric="totalIncome" helpText={undefined} />);

    await waitFor(() => expect(screen.getByText(/25% Decrease/i)).toBeVisible())
})

it('it displays increase if previous value is lower than current', async () => {
    // Add test-specific handler for previous lower case
    const { server } = require('../../../mocks/server');
    const { rest } = require('msw');

    server.use(
        rest.get('http://localhost:3000/api/v1/metrics/total-income', (req, res, ctx) => {
            return res(ctx.json({
                data: {
                    value: 3000,
                    previous: 2000
                }
            }))
        })
    );

    renderWithProvider(<ValueMetric name="Some Metric" metric="totalIncome" helpText={undefined} />);

    await waitFor(() => expect(screen.getByText(/50% Increase/i)).toBeVisible())
})
