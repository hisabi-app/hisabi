import { createClient, dedupExchange, fetchExchange } from '@urql/core';
import 'isomorphic-unfetch';

export default createClient({
  url: '/graphql',
  maskTypename: false,
  fetchOptions: () => {
    const token = document.head.querySelector('meta[name="csrf-token"]');

    return {
        credentials: 'include',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN' : token ? token.content : '',
        },
    };
  },
  exchanges: [dedupExchange, fetchExchange]
});