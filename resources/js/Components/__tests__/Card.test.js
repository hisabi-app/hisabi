import * as React from 'react'
import {cleanup, screen, render} from '@testing-library/react';
import '@testing-library/jest-dom';

import Card from '../Card';

afterEach(cleanup);

it('Card renders text content', () => {
  render(<Card children={"Hello from card"} />);

  expect(screen.getByText('Hello from card')).toBeVisible()
});

it('Card renders child content', () => {
  render(<Card children={<p data-testid="childtestid">Hello</p>} />);

  expect(screen.getByTestId('childtestid').textContent).toBe("Hello")
});