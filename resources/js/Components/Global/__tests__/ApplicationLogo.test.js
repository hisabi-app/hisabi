import * as React from 'react'
import {cleanup, screen, render} from '@testing-library/react';
import '@testing-library/jest-dom';

import ApplicationLogo from '../ApplicationLogo';

afterEach(cleanup);

it('ApplicationLogo must contain FINANCE', () => {
  render(<ApplicationLogo />);

  expect(screen.getByTestId('application-logo').textContent).toBe('💰 FINANCE')
});