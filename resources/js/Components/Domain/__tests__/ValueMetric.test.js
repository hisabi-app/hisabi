import * as React from 'react'
import { screen, render, waitFor, waitForElementToBeRemoved } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';

import ValueMetric from '../ValueMetric';

it('it fetches data once loaded and stop loader once data is fetched', async () => {
    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" />);
    
    expect(screen.getByTestId('loading-view')).toBeVisible()
    
    await waitForElementToBeRemoved(() => screen.queryByTestId('loading-view'))
    await waitFor(() => expect(screen.getByText(/some metric/i)).toBeVisible())
});


it('ranges provided and select first by default', async () => {
    const ranges = [
        {name: "Current Month", key: "current-month", start: "2022-04-01", end: "2022-04-30"}, 
        {name: "Last Month", key: "last-month", start: "2022-03-01", end: "2022-03-31"}
    ];

    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" ranges={ranges} />);

    await waitFor(() => {
        expect(screen.getByRole('option', {name: 'Current Month'}).selected).toBe(true)
        expect(screen.getByRole('option', {name: 'Last Month'}).selected).toBe(false)
    })
});

it('it allows to select a range if ranges provides', async () => {
    const ranges = [
        {name: "Current Month", key: "current-month", start: "2022-04-01", end: "2022-04-30"}, 
        {name: "Last Month", key: "last-month", start: "2022-03-01", end: "2022-03-31"}
    ];

    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" ranges={ranges} />);

    await waitFor(async () => {
        await userEvent.selectOptions(
            screen.getByRole('combobox'),
            screen.getByRole('option', {name: 'Last Month'}),
        )
    })

    await waitFor(() => {
        expect(screen.getByRole('option', {name: 'Current Month'}).selected).toBe(false)
        expect(screen.getByRole('option', {name: 'Last Month'}).selected).toBe(true)
    })
});

it('it fetches data if a range is selected', async () => {
    const ranges = [
        {name: "Current Month", key: "current-month", start: "2022-04-01", end: "2022-04-30"}, 
        {name: "Last Month", key: "last-month", start: "2022-03-01", end: "2022-03-31"}
    ];

    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQuery" ranges={ranges} />);

    await waitFor(() => expect(screen.getByText(/3k/i)).toBeVisible())

    await waitFor(async () => {
        await userEvent.selectOptions(
            screen.getByRole('combobox'),
            screen.getByRole('option', {name: 'Last Month'}),
        )
    })

    await waitFor(() => expect(screen.getByText(/2k/i)).toBeVisible())
})

it('it displays decrease if previous value is higher than current', async () => {
    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQueryWithPreviousHigher"/>);

    await waitFor(() => expect(screen.getByText(/25% Decrease/i)).toBeVisible())
})

it('it displays increase if previous value is lower than current', async () => {
    render(<ValueMetric name="Some Metric" graphql_query="valueMetricSampleQueryWithPreviousLower"/>);

    await waitFor(() => expect(screen.getByText(/50% Increase/i)).toBeVisible())
})