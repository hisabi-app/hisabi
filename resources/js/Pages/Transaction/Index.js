import React, { useEffect, useState } from 'react';
import { PencilAltIcon } from '@heroicons/react/outline';
import { Head } from '@inertiajs/inertia-react';
import Authenticated from '@/Layouts/Authenticated';
import Edit from '@/Pages/Transaction/Edit';
import LoadMore from '@/Components/LoadMore';

export default function Index({auth}) {
    const [transactions, setTransactions] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [loading, setLoading] = useState(false);
    const [editItem, setEditItem] = useState(null);

    useEffect(() => {
        if(! hasMorePages) return;
        setLoading(true);

        Api.getTransactions(currentPage)
            .then(({data}) => {
                setTransactions([...transactions, ...data.data.transactions.data])
                setHasMorePages(data.data.transactions.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    const updateTransaction = (updatedTransaction) => {
        setTransactions(transactions.map(transaction => {
            if(transaction.id === updatedTransaction.id) {
                return updatedTransaction
            }
            
            return transaction
        }));

        Engine.animateRowItem('item-' + updatedTransaction.id)
    }

    return (
        <Authenticated auth={auth}>
            <Head title="Transactions" />

            <Edit transaction={editItem} 
                onClose={() => setEditItem(null)} 
                onUpdate={transaction => {
                    updateTransaction(transaction)
                    setEditItem(null)
                }}
                />
        
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex flex-col">
                        <div className="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div className="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div className="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Id
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Amount
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Category
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Brand
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Type
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Date
                                                </th>
                                                <th scope="col" className="relative py-3">
                                                    <span className="sr-only">Edit</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {transactions.map((item) => (
                                                <tr key={item.id} className='loaded' id={'item-' + item.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.id}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{AppCurrency} {Engine.formatNumber(item.amount, null)}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.category ? item.brand.category.name : '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.name}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.category ? item.brand.category.type : '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.created_at}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <button onClick={() => setEditItem(item)} type="button">
                                                            <span className="sr-only">Edit</span>
                                                            <PencilAltIcon className="h-5 w-5 text-gray-500" aria-hidden="true" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <LoadMore hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage+1)} />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
