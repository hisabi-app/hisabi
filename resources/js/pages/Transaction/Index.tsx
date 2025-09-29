import React, { useEffect, useState, useMemo } from 'react';
import { PencilAltIcon, TrashIcon, InformationCircleIcon } from '@heroicons/react/outline';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Create from './Create';
import LoadMore from '@/components/Global/LoadMore';
import Button from '@/components/Global/Button';
import Delete from '@/components/Domain/Delete';
import { getTransactions, getAllBrands } from '@/Api';
import { animateRowItem, formatNumber } from '@/Utils';

export default function Index({auth}) {
    const [transactions, setTransactions] = useState([]);
    const [allBrands, setAllBrands] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [showCreate, setShowCreate] = useState(false);
    const [deleteItem, setDeleteItem] = useState(null);

    useEffect(() => {
        getAllBrands()
            .then(({data}) => {
                setAllBrands(data.allBrands)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        if(! hasMorePages) return;
        setLoading(true);

        getTransactions(currentPage, searchQuery)
            .then(({data}) => {
                setTransactions([...transactions, ...data.transactions.data])
                setHasMorePages(data.transactions.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    useEffect(() => {
        setLoading(true);

        getTransactions(currentPage, searchQuery)
            .then(({data}) => {
                setTransactions([...transactions, ...data.transactions.data])
                setHasMorePages(data.transactions.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [searchQuery]);

    const onCreate = (createdItem) => {
        setShowCreate(false)
        setTransactions([createdItem, ...transactions])

        animateRowItem(createdItem.id);
    }

    const onUpdate = (updatedItem) => {
        setTransactions(transactions.map(transaction => {
            if(transaction.id === updatedItem.id) {
                return updatedItem
            }

            return transaction
        }));

        animateRowItem(updatedItem.id)
        setEditItem(null)
    }

    const onDelete = () => {
        let tempDeleteItem = deleteItem;
        setDeleteItem(null)
        animateRowItem(tempDeleteItem.id, 'deleted', () => {
            setTransactions(transactions.filter(item => item.id != deleteItem.id));
        })
    };

    const performSearchHandler = (e) => {
        setTransactions([]);
        setSearchQuery(e.target.value ?? '');
        setCurrentPage(1);
    }

    const performSearch = useMemo(
        () => debounce(performSearchHandler, 300)
    , []);

    const header = <div className="w-full pb-3 mb-4 px-4 sm:px-0">
        <h2 className='text-lg text-gray-600'>Transactions</h2>

        <div className='flex justify-between items-center mt-2'>
            <div>
                <div className="relative flex items-center">
                    <input
                        type="text"
                        name="search"
                        placeholder='🔍 Search'
                        onChange={performSearch}
                        className="block w-full rounded-full border-0 py-1.5 pr-14 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm sm:leading-6"
                    />
                </div>
            </div>

            <Button children={"Create Transaction"} type="button" onClick={() => setShowCreate(true)} />
        </div>
    </div>

    return (
        <Authenticated auth={auth}>
            <Head title="Transactions" />

            <Create showCreate={showCreate}
                brands={allBrands}
                onCreate={onCreate}
                onClose={() => setShowCreate(false)} />

            <Edit transaction={editItem}
                brands={allBrands}
                onUpdate={onUpdate}
                onClose={() => setEditItem(null)}
            />

            <Delete item={deleteItem}
                resource="Transaction"
                onClose={() => setDeleteItem(null)}
                onDelete={onDelete}  />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {header}

                    <div className="flex flex-col">
                        {transactions.length > 0 && <div className="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
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
                                                    Brand
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Category
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
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{AppCurrency} {formatNumber(item.amount, null)}  </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.name}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.category ? <span className={"badge badge-" + item.brand.category.color}>{item.brand.category.name} ({item.brand.category.type})</span> : '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.created_at}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        {item.note &&
                                                            <button>
                                                                <div className="relative flex flex-col items-center group">
                                                                    <InformationCircleIcon className="h-5 w-5 text-gray-500" aria-hidden="true" />
                                                                    <div className="absolute bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
                                                                        <span className="relative z-10 p-2 text-xs leading-none text-white whitespace-no-wrap bg-gray-800 rounded shadow-lg">{item.note}</span>
                                                                        <div className="w-3 h-3 -mt-2 rotate-45 bg-gray-700"></div>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        }

                                                        <button onClick={() => setEditItem(item)} type="button" className="ml-2">
                                                            <span className="sr-only">Edit</span>
                                                            <PencilAltIcon className="h-5 w-5 text-gray-500" aria-hidden="true" />
                                                        </button>

                                                        <button onClick={() => setDeleteItem(item)} type="button" className="ml-2">
                                                            <span className="sr-only">Delete</span>

                                                            <TrashIcon className="h-5 w-5 text-gray-500" aria-hidden="true" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>}

                        <LoadMore hasContent={transactions.length > 0} hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage+1)} />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
