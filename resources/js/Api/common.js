import { gql } from '@urql/core';
import client from './client.js';

export const deleteResource = ({id, resource}) => {
    return client
        .mutation(gql`
            mutation DeleteResource {
                delete${resource}(id: ${id}) {
                    id
                }
            }
        `)
        .toPromise();
}

export const query = (query, range = null, queryName = null) => {
    const queryCustomName = queryName ? queryName : query;

    if(range == null) {
        return client
            .query(gql`
                query ${queryCustomName} {
                    ${query}
                }
            `)
            .toPromise();
    }
    
    return client
            .query(gql`
                query ${queryCustomName} {
                    ${query}(range: """${range}""") 
                }
            `, {range})
            .toPromise();
}
