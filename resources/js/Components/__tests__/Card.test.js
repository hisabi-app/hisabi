import * as React from 'react'
import {cleanup, screen, render} from '@testing-library/react';
import '@testing-library/jest-dom';

import Card from '../Card';

afterEach(cleanup);

it('Card changes the text after click', () => {
  render(<Card children={"Hello from card"} />);

  expect(screen.getByText('Hello from card')).toBeVisible()
});