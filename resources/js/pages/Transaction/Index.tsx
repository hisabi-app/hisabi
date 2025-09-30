import { useEffect, useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Create from './Create';
import LoadMore from '@/components/Global/LoadMore';
import { Button } from '@/components/ui/button';
import { getTransactions, getAllBrands, getTransactionStats } from '@/Api';
import { animateRowItem, formatNumber } from '@/Utils';
import { Card, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ArrowElbowDownRightIcon } from '@phosphor-icons/react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { getAppCurrency } from '@/Utils';


export default function Index({ auth }) {
    const [transactions, setTransactions] = useState([]);
    const [allBrands, setAllBrands] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [showCreate, setShowCreate] = useState(false);
    const [stats, setStats] = useState({ totalIncome: 0, totalExpenses: 0, totalTransactions: 0 });
    const [statsLoading, setStatsLoading] = useState(true);

    const fetchStats = () => {
        setStatsLoading(true);
        getTransactionStats()
            .then(({ data }) => {
                const income = JSON.parse(data.totalIncome);
                const expenses = JSON.parse(data.totalExpenses);
                const transactionCounts = JSON.parse(data.numberOfTransactions);
                const totalCount = transactionCounts.reduce((sum, item) => sum + item.value, 0);
                
                setStats({
                    totalIncome: income.value,
                    totalExpenses: expenses.value,
                    totalTransactions: totalCount
                });
            })
            .catch(console.error)
            .finally(() => setStatsLoading(false));
    };

    useEffect(() => {
        getAllBrands()
            .then(({ data }) => {
                setAllBrands(data.allBrands)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        fetchStats();
    }, []);

    useEffect(() => {
        if (!hasMorePages) return;
        setLoading(true);

        getTransactions(currentPage, searchQuery)
            .then(({ data }) => {
                const newTransactions = [...transactions, ...data.transactions.data];
                setTransactions(newTransactions)
                setHasMorePages(data.transactions.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    useEffect(() => {
        setLoading(true);

        getTransactions(currentPage, searchQuery)
            .then(({ data }) => {
                const newTransactions = [...transactions, ...data.transactions.data];
                setTransactions(newTransactions)
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

    const onDelete = (deletedItem) => {
        animateRowItem(deletedItem.id, 'deleted', () => {
            setTransactions(transactions.filter(item => item.id != deletedItem.id));
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

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Transactions</h2>
            <Button onClick={() => setShowCreate(true)}>New transaction</Button>
        </div>
    )

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Transactions" />

            <Create showCreate={showCreate}
                brands={allBrands}
                onCreate={onCreate}
                onClose={() => setShowCreate(false)} />

            <Edit transaction={editItem}
                brands={allBrands}
                onUpdate={onUpdate}
                onDelete={onDelete}
                onClose={() => setEditItem(null)}
            />

            <div className="p-4">
                <div className="max-w-7xl mx-auto grid gap-4">
                    
                    {/* Stats Cards */}
                    <Card className="overflow-hidden p-0">
                        <div className="grid grid-cols-3 divide-y md:divide-y-0 md:divide-x">
                            <CardContent className="px-6 py-4">
                                <div className="text-sm text-muted-foreground mb-2">Total transactions</div>
                                {statsLoading ? (
                                    <div className="h-6 w-16 bg-muted animate-pulse rounded"></div>
                                ) : (
                                    <div className="font-semibold">{formatNumber(stats.totalTransactions)}</div>
                                )}
                            </CardContent>
                            <CardContent className="px-6 py-4">
                                <div className="text-sm text-muted-foreground mb-2">Income</div>
                                {statsLoading ? (
                                    <div className="h-6 w-24 bg-muted animate-pulse rounded"></div>
                                ) : (
                                    <div className="font-semibold">{getAppCurrency()}{formatNumber(stats.totalIncome)}</div>
                                )}
                            </CardContent>
                            <CardContent className="px-6 py-4">
                                <div className="text-sm text-muted-foreground mb-2">Expenses</div>
                                {statsLoading ? (
                                    <div className="h-6 w-24 bg-muted animate-pulse rounded"></div>
                                ) : (
                                    <div className="font-semibold">{getAppCurrency()}{formatNumber(stats.totalExpenses)}</div>
                                )}
                            </CardContent>
                        </div>
                    </Card>
                    
                    {transactions.length > 0 && <div>
                        <Input
                            name="search"
                            placeholder='Search..'
                            className='bg-white max-w-56'
                            onChange={performSearch}
                        />
                    </div>}

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
                                                <p className=' text-xs'>{transaction.brand.category ? <span>{transaction.brand.category.name}</span> : '-'} - {transaction.created_at}</p>
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
