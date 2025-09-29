import * as React from 'react'
import { screen, render, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';

import Delete from '../Delete';

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
    const onCloseCallback = jest.fn();

    render(<Delete item={{id: 1}} onClose={onCloseCallback} />);

    fireEvent.click(screen.getByText('Cancel'))
  
    expect(onCloseCallback).toHaveBeenCalled()
});

it('If delete is pressed then onDelete is triggered', async () => {
  const onCloseCallback = jest.fn();
  const onDeleteCallback = jest.fn();

  render(<Delete resource="Category" item={{id: 1}} onClose={onCloseCallback} onDelete={onDeleteCallback} />);

  fireEvent.click(screen.getByText('Delete'))

  await waitFor(() => {
    expect(onDeleteCallback).toHaveBeenCalledTimes(1)
    expect(onCloseCallback).toHaveBeenCalledTimes(0)
  })
});