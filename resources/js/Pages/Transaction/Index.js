import React, { useEffect, useState } from 'react';
import { PencilAltIcon, TrashIcon, InformationCircleIcon } from '@heroicons/react/outline';
import { Head } from '@inertiajs/inertia-react';
import Authenticated from '@/Layouts/Authenticated';
import Edit from '@/Pages/Transaction/Edit';
import Create from './Create';
import LoadMore from '@/Components/LoadMore';
import Delete from '@/Components/Delete';

export default function Index({auth}) {
    const [transactions, setTransactions] = useState([]);
    const [allBrands, setAllBrands] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [loading, setLoading] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [showCreate, setShowCreate] = useState(false);
    const [deleteItem, setDeleteItem] = useState(null);

    useEffect(() => {
        Api.getAllBrands()
            .then(({data}) => {
                setAllBrands(data.data.allBrands)
            })
            .catch(console.error);
    }, []);

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

    const onCreate = (createdItem) => {
        setShowCreate(false)
        setTransactions([createdItem, ...transactions])

        Engine.animateRowItem(createdItem.id);
    }

    const onUpdate = (updatedItem) => {
        setTransactions(transactions.map(transaction => {
            if(transaction.id === updatedItem.id) {
                return updatedItem
            }
            
            return transaction
        }));

        Engine.animateRowItem(updatedItem.id)
        setEditItem(null)
    }

    const onDelete = () => {
        let tempDeleteItem = deleteItem;
        setDeleteItem(null)
        Engine.animateRowItem(tempDeleteItem.id, 'deleted', () => {
            setTransactions(transactions.filter(item => item.id != deleteItem.id));
        })
    };

    return (
        <Authenticated auth={auth}
            header={
                <div className='flex justify-between items-center'>
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Transactions
                    </h2>

                    <button onClick={() => setShowCreate(true)} className="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                        Create Transaction
                    </button>
                </div>
            }>
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
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{AppCurrency} {Engine.formatNumber(item.amount, null)}  </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.category ? item.brand.category.name : '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.name}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.brand.category ? item.brand.category.type : '-'}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{item.created_at}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        {item.note && 
                                                            <button>
                                                                <div class="relative flex flex-col items-center group">
                                                                    <InformationCircleIcon className="h-5 w-5 text-gray-500" aria-hidden="true" />
                                                                    <div class="absolute bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex">
                                                                        <span class="relative z-10 p-2 text-xs leading-none text-white whitespace-no-wrap bg-gray-800 rounded shadow-lg">{item.note}</span>
                                                                        <div class="w-3 h-3 -mt-2 rotate-45 bg-gray-700"></div>
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
