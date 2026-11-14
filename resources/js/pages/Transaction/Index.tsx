import { useEffect, useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';
import { format, isThisYear, isToday, isYesterday, parseISO } from 'date-fns';
import { MagnifyingGlassIcon, X } from '@phosphor-icons/react';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Filters from './Filters';
import DetailSheet from '@/components/Home/DetailSheet';
import type { Detail, UncategorizedBrand } from '@/components/Home/types';
import EmptyState from '@/components/Wallet/EmptyState';
import AddButton from '@/components/Shell/AddButton';
import PageHeader from '@/components/Wallet/PageHeader';
import Tile from '@/components/Wallet/Tile';
import { amount, money, toAmount } from '@/components/Wallet/format';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { getAllBrands, getTransactions } from '@/Api';
import { getAllCategories } from '@/Api/categories';
import { getHome } from '@/Api/home';
import { useDataChanged } from '@/hooks/useDataChanged';
import { cn } from '@/lib/utils';

const dayLabel = (date: string) => {
    const day = parseISO(date);
    if (isToday(day)) return 'Today';
    if (isYesterday(day)) return 'Yesterday';

    return format(day, isThisYear(day) ? 'EEEE d MMM' : 'EEEE d MMM yyyy');
};

export default function Index({ auth }: { auth: any }) {
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('search') || '';
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
    const [loading, setLoading] = useState(true);
    const [editItem, setEditItem] = useState(null);
    const [filters, setFilters] = useState(initialFilters);
    const [reloadKey, setReloadKey] = useState(0);
    const [detail, setDetail] = useState<Detail>(null);
    const [uncategorized, setUncategorized] = useState<UncategorizedBrand[]>([]);

    useEffect(() => {
        getAllBrands()
            .then(({ data }) => setAllBrands(data.allBrands))
            .catch(console.error);
        getAllCategories()
            .then(({ data }) => setAllCategories(data.allCategories))
            .catch(console.error);
    }, []);

    useEffect(() => {
        setLoading(true);

        getTransactions(currentPage, searchQuery, filters)
            .then(({ data }) => {
                setTransactions((current) => (currentPage === 1 ? data.transactions.data : [...current, ...data.transactions.data]));
                setHasMorePages(data.transactions.paginatorInfo.hasMorePages);
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [currentPage, searchQuery, filters, reloadKey]);

    // Added from the sidebar or parsed from an SMS
    const reload = () => {
        setCurrentPage(1);
        setReloadKey((key) => key + 1);
    };
    useDataChanged(reload);

    const onUpdate = (updated: any) => setTransactions((current) => current.map((transaction) => (transaction.id === updated.id ? updated : transaction)));
    const onDelete = (deleted: any) => setTransactions((current) => current.filter((transaction) => transaction.id != deleted.id));

    const performSearch = useMemo(
        () =>
            debounce((event: any) => {
                const value = event.target.value ?? '';
                const url = new URL(window.location.href);
                value ? url.searchParams.set('search', value) : url.searchParams.delete('search');
                window.history.pushState({}, '', url);

                setCurrentPage(1);
                setSearchQuery(value);
            }, 300),
        [],
    );

    const applyFilters = (next: typeof initialFilters) => {
        const url = new URL(window.location.href);
        next.brandId ? url.searchParams.set('brand', next.brandId) : url.searchParams.delete('brand');
        next.categoryId ? url.searchParams.set('category', next.categoryId) : url.searchParams.delete('category');
        if (next.dateFrom && next.dateTo) {
            url.searchParams.set('dateFrom', next.dateFrom);
            url.searchParams.set('dateTo', next.dateTo);
        } else {
            url.searchParams.delete('dateFrom');
            url.searchParams.delete('dateTo');
        }
        window.history.pushState({}, '', url);

        setCurrentPage(1);
        setFilters(next);
    };

    const chooseCategories = () => {
        getHome()
            .then((home: any) => {
                setUncategorized(home.needs_you.brands.items);
                setDetail({ kind: 'brands' });
            })
            .catch(console.error);
    };

    const groups = useMemo(() => {
        const byDay = new Map<string, any[]>();
        transactions.forEach((transaction) => byDay.set(transaction.created_at, [...(byDay.get(transaction.created_at) ?? []), transaction]));

        return [...byDay.entries()].map(([day, items]) => ({
            day,
            items,
            spent: items.filter((item) => !item.brand.category || item.brand.category.type === 'EXPENSES').reduce((sum, item) => sum + toAmount(item.amount), 0),
        }));
    }, [transactions]);

    const chips = [
        filters.brandId && { key: 'brand', label: allBrands.find((brand) => brand.id == filters.brandId)?.name ?? 'Brand', clear: { ...filters, brandId: '' } },
        filters.categoryId && { key: 'category', label: allCategories.find((category) => category.id == filters.categoryId)?.name ?? 'Category', clear: { ...filters, categoryId: '' } },
        filters.dateFrom && filters.dateTo && { key: 'date', label: `${format(parseISO(filters.dateFrom), 'd MMM')} to ${format(parseISO(filters.dateTo), 'd MMM yyyy')}`, clear: { ...filters, dateFrom: '', dateTo: '' } },
    ].filter(Boolean) as { key: string; label: string; clear: typeof initialFilters }[];

    const filtered = Boolean(searchQuery) || chips.length > 0;

    return (
        <Authenticated auth={auth}>
            <Head title="Transactions" />

            <Edit transaction={editItem} brands={allBrands} onUpdate={onUpdate} onDelete={onDelete} onClose={() => setEditItem(null)} />
            <DetailSheet
                detail={detail}
                brands={uncategorized}
                onClose={() => {
                    setDetail(null);
                    reload();
                }}
            />

            <div className="mx-auto grid w-full max-w-[1100px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">
                <PageHeader title="Transactions" actions={<AddButton />} />

                <div className="rise flex flex-wrap items-center gap-2" style={{ '--i': 1 } as React.CSSProperties}>
                    <div className="relative w-full sm:w-64">
                        <MagnifyingGlassIcon size={16} className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                        <Input name="search" placeholder="Search brands, categories, notes" className="pl-9" defaultValue={searchQuery} onChange={performSearch} aria-label="Search transactions" />
                    </div>
                    {chips.map((chip) => (
                        <button
                            key={chip.key}
                            type="button"
                            onClick={() => applyFilters(chip.clear)}
                            className="inline-flex h-9 items-center gap-1.5 rounded-full bg-card px-3.5 text-[13px] font-semibold shadow-card ring-1 ring-border ring-inset hover:bg-secondary"
                            aria-label={`Remove filter ${chip.label}`}
                        >
                            {chip.label}
                            <X size={13} weight="bold" aria-hidden="true" />
                        </button>
                    ))}
                    <div className="ml-auto">
                        <Filters brands={allBrands} categories={allCategories} onApply={applyFilters} activeFilters={filters} />
                    </div>
                </div>

                {loading && transactions.length === 0 && (
                    <div className="grid gap-3.5" aria-busy="true">
                        <Skeleton className="h-48 w-full rounded-2xl" />
                        <Skeleton className="h-32 w-full rounded-2xl" />
                    </div>
                )}

                {!loading && transactions.length === 0 && (
                    <EmptyState
                        className="rounded-2xl bg-card shadow-card"
                        title={filtered ? 'No transactions match' : 'No transactions yet'}
                        body={filtered ? 'Try another search or remove a filter.' : 'Paste a bank SMS or add one by hand from Add.'}
                    />
                )}

                {groups.map((group, i) => (
                    <section key={group.day} className={cn('rounded-2xl bg-card px-4 py-3 shadow-card sm:px-[18px]', i < 6 && 'rise')} style={{ '--i': i + 2 } as React.CSSProperties}>
                        <div className="flex items-baseline justify-between gap-3 border-b pb-2">
                            <h2 className="font-bold tracking-[-0.01em]">{dayLabel(group.day)}</h2>
                            {group.spent > 0 && <span className="text-[13px] text-muted-foreground font-rounded tabular">{money(group.spent, 2)} spent</span>}
                        </div>
                        <div className="divide-y">
                            {group.items.map((transaction) => {
                                const category = transaction.brand.category;
                                const isIncome = category?.type === 'INCOME';

                                return (
                                    <div key={transaction.id} className="grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 py-2.5">
                                        <Tile color={category?.color} icon={category?.icon} className="row-span-2" label={category?.name ?? 'No category'} />
                                        <button type="button" onClick={() => setEditItem(transaction)} className="min-w-0 truncate text-left font-semibold hover:underline">
                                            {transaction.brand.name}
                                        </button>
                                        <p className="col-start-2 truncate text-[12.5px] text-muted-foreground">
                                            {category ? (
                                                category.name
                                            ) : (
                                                <button type="button" onClick={chooseCategories} className="font-semibold text-info hover:underline">
                                                    Choose a category
                                                </button>
                                            )}
                                            {transaction.note && <span> · {transaction.note}</span>}
                                        </p>
                                        <p className={cn('col-start-3 row-span-2 row-start-1 text-right text-sm font-semibold font-rounded tabular', isIncome && 'text-success')}>
                                            {isIncome ? '+' : ''}
                                            {amount(transaction.amount, 2)}
                                        </p>
                                    </div>
                                );
                            })}
                        </div>
                    </section>
                ))}

                {transactions.length > 0 && hasMorePages && (
                    <div className="flex justify-center py-2">
                        <Button variant="secondary" disabled={loading} onClick={() => setCurrentPage((page) => page + 1)}>
                            {loading ? 'Loading…' : 'Show more'}
                        </Button>
                    </div>
                )}
            </div>
        </Authenticated>
    );
}
