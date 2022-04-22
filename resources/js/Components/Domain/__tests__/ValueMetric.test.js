import * as React from 'react'
import { screen, render, fireEvent, waitFor, act, waitForElementToBeRemoved } from '@testing-library/react';
import '@testing-library/jest-dom';

import ValueMetric from '../ValueMetric';

it('it shows loading by default', async () => {
    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" />);
    
    expect(screen.getByTestId('loading-view')).toBeVisible()
    
    await waitForElementToBeRemoved(() => screen.queryByTestId('loading-view'))
});

// it('it fetches data once loaded and stop loader once data is fetched', () => {
//     render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" />);

// });

// it('it allows to select range if provided', () => {
//     render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" />);
// });

// it('it fetches data if a range is selected', () => {
//     render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" />);
// })