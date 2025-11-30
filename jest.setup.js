import 'whatwg-fetch';
import { server } from './resources/js/mocks/server.js'

// Mock location object for API client
Object.defineProperty(window, 'location', {
  value: {
    origin: 'http://localhost:3000',
    href: 'http://localhost:3000',
    protocol: 'http:',
    hostname: 'localhost',
    port: '3000',
    pathname: '/',
    search: '',
    hash: ''
  },
  writable: true
});

// Mock global location for Node environment
global.location = window.location;

beforeAll(() => server.listen())
afterEach(() => server.resetHandlers())
afterAll(() => server.close())
