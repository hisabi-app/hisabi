import { createClient, dedupExchange, fetchExchange } from '@urql/core';
import 'isomorphic-unfetch';

export default createClient({
  url: location.origin + '/graphql',
  maskTypename: false,
  fetchOptions: () => {
    return {
        credentials: 'include',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'X-Xsrf-Token': getCookieValue('XSRF-TOKEN')
        },
    };
  },
  exchanges: [dedupExchange, fetchExchange]
});

const getCookieValue = (name) => {
  let match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'))
  return match ? decodeURIComponent(match[3]) : null
}