import { useEffect, useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { debounce } from 'lodash';
import { startOfMonth, endOfMonth } from 'date-fns';
import { DateRange } from 'react-day-picker';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import RecordTransactionButton from '@/components/Domain/RecordTransactionButton';
import Filters from './Filters';
import LoadMore from '@/components/Global/LoadMore';
import { Button } from '@/components/ui/button';
import { getTransactions, getAllBrands } from '@/Api';
import { getAllCategories } from '@/Api/categories';
import { animateRowItem, formatNumber, getAppCurrency } from '@/Utils';
import { Card, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ArrowElbowDownRightIcon, X } from '@phosphor-icons/react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import TransactionStats from '@/components/Domain/TransactionStats';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import { DatePickerWithRange } from '@/components/ui/date-picker-with-range';


export default function Index({ auth }: { auth: any }) {
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('search') || '';

    // Initialize filters from URL
    const initialFilters = {
        brandId: urlParams.get('brand') || '',
        categoryId: urlParams.get('category') || '',
        dateFrom: urlParams.get('dateFrom') || '',
        dateTo: urlParams.get('dateTo') || '',
    };

    const [transactions, setTransactions] = useState<any[]>([]);
    const [allBrands, setAllBrands] = useState<any[]>([]);
    const [allCategories, setAllCategories] = useState<any[]>([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState(initialSearch);
    const [loading, setLoading] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [filters, setFilters] = useState(initialFilters);
    const [dateRange, setDateRange] = useState<DateRange>({
        from: startOfMonth(new Date()),
        to: endOfMonth(new Date()),
    });

    useEffect(() => {
        getAllBrands()
            .then(({ data }) => {
                setAllBrands(data.allBrands)
            })
            .catch(console.error);

        getAllCategories()
            .then(({ data }) => {
                setAllCategories(data.allCategories)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        if (currentPage > 1 && !hasMorePages) return;

        setLoading(true);

        getTransactions(currentPage, searchQuery, filters)
            .then(({ data }) => {
                const newTransactions = currentPage === 1
                    ? data.transactions.data
                    : [...transactions, ...data.transactions.data];

                setTransactions(newTransactions)
                setHasMorePages(data.transactions.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage, searchQuery, filters]);

    const onCreate = (createdItem: any) => {
        setTransactions([createdItem, ...transactions]);
        animateRowItem(createdItem.id);
    };

    const onUpdate = (updatedItem: any) => {
        setTransactions(transactions.map(transaction => {
            if (transaction.id === updatedItem.id) {
                return updatedItem;
            }
            return transaction;
        }));
        animateRowItem(updatedItem.id);
    };

    const onDelete = (deletedItem: any) => {
        (animateRowItem as any)(deletedItem.id, 'deleted', () => {
            setTransactions(transactions.filter(item => item.id != deletedItem.id));
        });
    };

    const performSearchHandler = (e: any) => {
        const value = e.target.value ?? '';

        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set('search', value);
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);

        setCurrentPage(1);
        setSearchQuery(value);
    }

    const performSearch = useMemo(
        () => debounce(performSearchHandler, 300)
        , []);

    const handleFiltersApply = (newFilters: any) => {
        const url = new URL(window.location.href);

        // Update URL params for filters
        if (newFilters.brandId) {
            url.searchParams.set('brand', newFilters.brandId);
        } else {
            url.searchParams.delete('brand');
        }

        if (newFilters.categoryId) {
            url.searchParams.set('category', newFilters.categoryId);
        } else {
            url.searchParams.delete('category');
        }

        if (newFilters.dateFrom && newFilters.dateTo) {
            url.searchParams.set('dateFrom', newFilters.dateFrom);
            url.searchParams.set('dateTo', newFilters.dateTo);
        } else {
            url.searchParams.delete('dateFrom');
            url.searchParams.delete('dateTo');
        }

        window.history.pushState({}, '', url);

        setCurrentPage(1);
        setFilters(newFilters);
    };

    const clearFilter = (filterKey: string) => {
        const updatedFilters = { ...filters };

        switch (filterKey) {
            case 'brand':
                updatedFilters.brandId = '';
                break;
            case 'category':
                updatedFilters.categoryId = '';
                break;
            case 'date':
                updatedFilters.dateFrom = '';
                updatedFilters.dateTo = '';
                break;
        }

        handleFiltersApply(updatedFilters);
    };

    const handleDateChange = (newDateRange: DateRange | undefined) => {
        if (newDateRange?.from && newDateRange?.to) {
            setDateRange(newDateRange);
        }
    };

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Transactions</h2>
            <div className="flex items-center gap-2">
                <DatePickerWithRange
                    onDateChange={handleDateChange}
                    initialDate={dateRange}
                />
                <RecordTransactionButton
                    brands={allBrands}
                    onSuccess={onCreate}
                />
            </div>
        </div>
    )

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Transactions" />

            <Edit
                transaction={editItem}
                brands={allBrands}
                onUpdate={onUpdate}
                onDelete={onDelete}
                onClose={() => setEditItem(null)}
            />

            <div className="p-4">
                <div className="max-w-7xl mx-auto grid gap-4">

                    <TransactionStats dateRange={dateRange} />

                    <div className="flex justify-between gap-2">
                        <Input
                            name="search"
                            placeholder='Search..'
                            className='bg-white max-w-56'
                            defaultValue={searchQuery}
                            onChange={performSearch}
                        />
                        <div className="flex gap-2">
                            {/* Active filter badges */}
                            {filters.brandId && (
                                <Badge
                                    variant="secondary"
                                    className="h-9 gap-1.5 cursor-pointer hover:bg-secondary/80 transition-colors rounded-full px-3"
                                    onClick={() => clearFilter('brand')}
                                >
                                    {allBrands.find((b: any) => b.id == filters.brandId)?.name}
                                    <X size={14} weight="bold" />
                                </Badge>
                            )}
                            {filters.categoryId && (
                                <Badge
                                    variant="secondary"
                                    className="h-9 gap-1.5 cursor-pointer hover:bg-secondary/80 transition-colors rounded-full px-3"
                                    onClick={() => clearFilter('category')}
                                >
                                    {allCategories.find((c: any) => c.id == filters.categoryId)?.name}
                                    <X size={14} weight="bold" />
                                </Badge>
                            )}
                            {filters.dateFrom && filters.dateTo && (
                                <Badge
                                    variant="secondary"
                                    className="h-9 gap-1.5 cursor-pointer hover:bg-secondary/80 transition-colors rounded-full px-3"
                                    onClick={() => clearFilter('date')}
                                >
                                    {filters.dateFrom} - {filters.dateTo}
                                    <X size={14} weight="bold" />
                                </Badge>
                            )}
                            <Filters
                                brands={allBrands}
                                categories={allCategories}
                                onApply={handleFiltersApply}
                                activeFilters={filters}
                            />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        {transactions.length > 0 && transactions.map((transaction) => {
                            const CategoryIcon = transaction.brand.category?.icon
                                ? getCategoryIcon(transaction.brand.category.icon)
                                : null;
                            const hasCategory = transaction.brand.category !== null;
                            const isUncategorized = !hasCategory;
                            const categoryType = hasCategory ? transaction.brand.category.type : null;
                            const isIncomeTransaction = categoryType === "INCOME";

                            return (
                                <Card key={transaction.id} className={`py-0 ${isUncategorized ? 'bg-red-50 border-red-100' : ''}`} id={'item-' + transaction.id}>
                                    <CardContent className='flex justify-between items-center px-4 py-3'>
                                        <div className='flex gap-2 items-center'>
                                            {CategoryIcon && hasCategory ? (
                                                <div className={`size-10 rounded-full flex items-center justify-center badge badge-${transaction.brand.category.color}`}>
                                                    <CategoryIcon size={24} weight="regular" className="text-current" />
                                                </div>
                                            ) : (
                                                <Avatar className='size-10'>
                                                    <AvatarImage src={transaction.brand.image} />
                                                    <AvatarFallback>{transaction.brand.name.charAt(0)}</AvatarFallback>
                                                </Avatar>
                                            )}
                                            <div>
                                                <button onClick={() => setEditItem(transaction)} className='font-medium hover:underline'>{transaction.brand.name} </button>
                                                <div className='flex gap-1 text-muted-foreground items-center'>
                                                    <ArrowElbowDownRightIcon size={10} weight="bold" />
                                                    <p className=' text-xs'>{hasCategory ? <span>{transaction.brand.category.name} -</span> : ''} {transaction.created_at}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div className='flex gap-2 items-center'>
                                            {transaction.note && <Badge variant="secondary">{transaction.note}</Badge>
                                            }
                                            <p className={`${isIncomeTransaction ? 'text-green-500' : ''} min-w-26 text-right`}> {isIncomeTransaction ? '' : '-'}{getAppCurrency()} {formatNumber(transaction.amount, null)}</p>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}

                        <LoadMore hasContent={transactions.length > 0} hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage + 1)} />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
