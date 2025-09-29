import React, { useEffect, useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Create from './Create';
import LoadMore from '@/components/Global/LoadMore';
import { Button } from '@/components/ui/button';
import Delete from '@/components/Domain/Delete';
import { getTransactions, getAllBrands } from '@/Api';
import { animateRowItem, formatNumber } from '@/Utils';
import { Card, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ArrowElbowDownRightIcon } from '@phosphor-icons/react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';


export default function Index({ auth }) {
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
            .then(({ data }) => {
                setAllBrands(data.allBrands)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        if (!hasMorePages) return;
        setLoading(true);

        getTransactions(currentPage, searchQuery)
            .then(({ data }) => {
                setTransactions([...transactions, ...data.transactions.data])
                setHasMorePages(data.transactions.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    useEffect(() => {
        setLoading(true);

        getTransactions(currentPage, searchQuery)
            .then(({ data }) => {
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
            if (transaction.id === updatedItem.id) {
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
                <div className="grid grid-cols-2 gap-2">
                    <Input
                        name="search"
                        placeholder='Search..'
                        className='bg-white'
                        onChange={performSearch}
                    />
                </div>
            </div>

            <Button children={"New transaction"} type="button" onClick={() => setShowCreate(true)} />
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
                onDelete={onDelete} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {header}

                    <div className="grid gap-2">
                        {transactions.length > 0 && transactions.map((transaction) => (
                            <Card key={transaction.id} className='py-0' id={'item-' + transaction.id}>
                                <CardContent className='flex justify-between items-center px-4 py-3'>
                                    <div className='flex gap-2 items-center'>
                                        <Avatar className='size-10'>
                                            <AvatarImage src={transaction.brand.image} />
                                            <AvatarFallback>{transaction.brand.name.charAt(0)}</AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <button onClick={() => setEditItem(transaction)} className='font-medium hover:underline'>{transaction.brand.name} </button>
                                            <div className='flex gap-1 text-muted-foreground items-center'>
                                                <ArrowElbowDownRightIcon size={10} weight="bold" />
                                                <p className=' text-xs'>{transaction.brand.category ? <span>{transaction.brand.category.name}</span> : '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='flex gap-2 items-center'>
                                        {transaction.note && <Badge variant="secondary">{transaction.note}</Badge>
                                        }
                                        {transaction.brand.category && <Badge className={"badge badge-" + transaction.brand.category.color} variant="outline">{transaction.brand.category.name}</Badge>
                                        }
                                        <p className={`${transaction.brand.category.type == "INCOME" ? 'text-green-500' : ''} min-w-26 text-right`}> {transaction.brand.category.type == "INCOME" ? '' : '-'}{AppCurrency} {formatNumber(transaction.amount, null)}</p>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}

                        <LoadMore hasContent={transactions.length > 0} hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage + 1)} />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
