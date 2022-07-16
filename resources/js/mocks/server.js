import '@testing-library/jest-dom';
import 'intersection-observer';
import {setupServer} from 'msw/node'

import { handlers } from './handlers'

export const server = setupServer(...handlers)