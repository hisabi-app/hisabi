import { useEffect, useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { debounce } from 'lodash';
import { CaretRightIcon, MagnifyingGlassIcon, PlusIcon } from '@phosphor-icons/react';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Create from './Create';
import DetailSheet from '@/components/Home/DetailSheet';
import type { Detail, UncategorizedBrand } from '@/components/Home/types';
import EmptyState from '@/components/Wallet/EmptyState';
import PageHeader from '@/components/Wallet/PageHeader';
import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { getAllCategories, getBrands } from '@/Api';
import { getHome } from '@/Api/home';
import { useDataChanged } from '@/hooks/useDataChanged';

export default function Index({ auth }: { auth: any }) {
    const [brands, setBrands] = useState<any[]>([]);
    const [allCategories, setAllCategories] = useState<any[]>([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(true);
    const [editItem, setEditItem] = useState(null);
    const [showCreate, setShowCreate] = useState(false);
    const [reloadKey, setReloadKey] = useState(0);
    const [uncategorized, setUncategorized] = useState<UncategorizedBrand[]>([]);
    const [detail, setDetail] = useState<Detail>(null);

    useEffect(() => {
        getAllCategories()
            .then(({ data }) => setAllCategories(data.allCategories))
            .catch(console.error);
    }, []);

    // The brands still waiting for a category, the same list the home's Needs you uses
    useEffect(() => {
        getHome()
            .then((home: any) => setUncategorized(home.needs_you.brands.items))
            .catch(console.error);
    }, [reloadKey]);

    useEffect(() => {
        setLoading(true);

        getBrands(currentPage, searchQuery)
            .then(({ data }) => {
                setBrands((current) => (currentPage === 1 ? data.brands.data : [...current, ...data.brands.data]));
                setHasMorePages(data.brands.paginatorInfo.hasMorePages);
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [currentPage, searchQuery, reloadKey]);

    const reload = () => {
        setCurrentPage(1);
        setReloadKey((key) => key + 1);
    };
    useDataChanged(reload);

    const onCreate = (created: any) => {
        setShowCreate(false);
        setBrands((current) => [created, ...current]);
    };
    const onUpdate = (updated: any) => {
        setBrands((current) => current.map((brand) => (brand.id === updated.id ? updated : brand)));
        setUncategorized((current) => (updated.category ? current.filter((brand) => brand.id !== updated.id) : current));
    };
    const onDelete = (deleted: any) => setBrands((current) => current.filter((brand) => brand.id != deleted.id));

    const performSearch = useMemo(
        () =>
            debounce((event: any) => {
                setCurrentPage(1);
                setSearchQuery(event.target.value ?? '');
            }, 300),
        [],
    );

    return (
        <Authenticated auth={auth}>
            <Head title="Brands" />

            <Create showCreate={showCreate} categories={allCategories} onCreate={onCreate} onClose={() => setShowCreate(false)} />
            <Edit brand={editItem} categories={allCategories} onUpdate={onUpdate} onDelete={onDelete} onClose={() => setEditItem(null)} />
            <DetailSheet
                detail={detail}
                brands={uncategorized}
                onClose={() => {
                    setDetail(null);
                    reload();
                }}
            />

            <div className="mx-auto grid w-full max-w-[1100px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">
                <PageHeader
                    title="Brands"
                    actions={
                        <Button onClick={() => setShowCreate(true)}>
                            <PlusIcon weight="bold" aria-hidden="true" />
                            New brand
                        </Button>
                    }
                />

                {uncategorized.length > 0 && (
                    <Panel title="Need a category" count={uncategorized.length} index={1}>
                        <div className="flex flex-wrap items-center justify-between gap-3 py-1.5">
                            <p className="min-w-0 text-muted-foreground">
                                {uncategorized
                                    .slice(0, 3)
                                    .map((brand) => brand.name)
                                    .join(', ')}
                                {uncategorized.length > 3 && ` and ${uncategorized.length - 3} more`}
                            </p>
                            <Button variant="secondary" onClick={() => setDetail({ kind: 'brands' })}>
                                Assign
                            </Button>
                        </div>
                    </Panel>
                )}

                <div className="rise relative w-full sm:w-64" style={{ '--i': 2 } as React.CSSProperties}>
                    <MagnifyingGlassIcon size={16} className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                    <Input name="search" placeholder="Search brands or categories" className="pl-9" onChange={performSearch} aria-label="Search brands" />
                </div>

                {loading && brands.length === 0 && <Skeleton className="h-72 w-full rounded-2xl" />}

                {!loading && brands.length === 0 && (
                    <EmptyState
                        className="rounded-2xl bg-card shadow-card"
                        title={searchQuery ? 'No brands match' : 'No brands yet'}
                        body={searchQuery ? 'Try another name.' : 'Brands are created from your bank SMS, one per shop, or by hand.'}
                    />
                )}

                {brands.length > 0 && (
                    <Panel index={3}>
                        <div className="divide-y">
                            {brands.map((brand) => (
                                <div key={brand.id} id={'item-' + brand.id} className="grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 py-2.5">
                                    <Tile color={brand.category?.color} icon={brand.category?.icon} className="row-span-2" label={brand.category?.name ?? 'No category'} />
                                    <button type="button" onClick={() => setEditItem(brand)} className="min-w-0 truncate text-left font-semibold hover:underline">
                                        {brand.name}
                                    </button>
                                    <p className="col-start-2 truncate text-[12.5px] text-muted-foreground">
                                        {brand.category ? (
                                            brand.category.name
                                        ) : (
                                            <button type="button" onClick={() => setEditItem(brand)} className="font-semibold text-info hover:underline">
                                                Choose a category
                                            </button>
                                        )}
                                    </p>
                                    <Link
                                        href={`/transactions?brand=${brand.id}`}
                                        className="col-start-3 row-span-2 row-start-1 inline-flex items-center gap-1 text-[13px] text-muted-foreground hover:text-foreground"
                                    >
                                        {brand.transactionsCount} {brand.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                        <CaretRightIcon size={13} weight="bold" aria-hidden="true" />
                                    </Link>
                                </div>
                            ))}
                        </div>
                    </Panel>
                )}

                {brands.length > 0 && hasMorePages && (
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
