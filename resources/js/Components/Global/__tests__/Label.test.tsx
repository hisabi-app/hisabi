import * as React from 'react'
import {cleanup, screen, render} from '@testing-library/react';
import '@testing-library/jest-dom';

import Label from '../Label';

afterEach(cleanup);

it('Label renders text value content', () => {
  render(<Label value={"Some label"} />);

  expect(screen.getByText('Some label')).toBeVisible()
});