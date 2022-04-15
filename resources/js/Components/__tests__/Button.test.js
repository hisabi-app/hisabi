import * as React from 'react'
import {cleanup, screen, render} from '@testing-library/react';
import '@testing-library/jest-dom';

import Button from '../Button';

afterEach(cleanup);

it('Button can render custom className', () => {
    render(<Button className='test-class' />);
  
    expect(screen.getByRole('button')).toHaveClass('test-class')
});

it('Button can render text child', () => {
  render(<Button children={"Hello from card"} />);

  expect(screen.getByText('Hello from card')).toBeVisible()
});

it('Button by default has submit type', () => {
    render(<Button />);
    
    expect(screen.getByRole('button')).toHaveAttribute('type', 'submit')
});

it('Button type can be changed', () => {
    render(<Button type={'test'} />);

    expect(screen.getByRole('button')).toHaveAttribute('type', 'test')
});

it('Button by default is not disabled', () => {
    render(<Button />);
    
    var button = screen.getByRole('button');

    expect(button).not.toBeDisabled()
    expect(button).not.toHaveClass('opacity-25')
});

it('Button is disabled if under processing', () => {
    render(<Button processing={true} />);

    var button = screen.getByRole('button');
    
    expect(button).toBeDisabled()
    expect(button).toHaveClass('opacity-25')
});