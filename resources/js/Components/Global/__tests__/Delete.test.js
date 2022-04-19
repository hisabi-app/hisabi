import * as React from 'react'
import { cleanup, screen, render, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import 'intersection-observer';

import Delete from '../Delete';

afterEach(cleanup);

it('If item not passed then delete popup will not be shown', () => {
  render(<Delete />);

  expect(screen.queryByTestId('delete-warning')).toBeNull
});

it('If item passed then delete popup will be shown', () => {
    render(<Delete item={{id: 1}} onClose={() => {}} />);
  
    expect(screen.getByTestId('delete-warning')).toBeVisible()
    expect(screen.getByText('Delete')).toBeVisible()
    expect(screen.getByText('Cancel')).toBeVisible()
});

it('If cancel is pressed then onClose is triggered', () => {
    const callback = jest.fn();
    render(<Delete item={{id: 1}} onClose={callback} />);

    fireEvent.click(screen.getByText('Cancel'))
  
    expect(callback).toHaveBeenCalled()
});