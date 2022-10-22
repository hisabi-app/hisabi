import * as React from 'react'
import {cleanup, screen, render} from '@testing-library/react';
import '@testing-library/jest-dom';

import SectionDivider from '../SectionDivider';

afterEach(cleanup);

it('SectionDivider renders title text value content', () => {
  render(<SectionDivider title={"Some text"} />);

  expect(screen.getByText('Some text')).toBeVisible()
});