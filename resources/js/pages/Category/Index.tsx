import { useEffect, useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Create from './Create';
import { Button } from '@/components/ui/button';
import { getAllCategories } from '@/Api';
import { animateRowItem } from '@/Utils';
import { Input } from '@/components/ui/input';
import { CaretRightIcon, MagnifyingGlassIcon, PlusIcon } from '@phosphor-icons/react';
import EmptyState from '@/components/Wallet/EmptyState';
import PageHeader from '@/components/Wallet/PageHeader';
import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';

interface Category {
    id: number;
    name: string;
    type: string;
    color: string;
    icon: string;
    transactionsCount: number;
}

interface GroupedCategories {
    INCOME: Category[];
    EXPENSES: Category[];
    SAVINGS: Category[];
    INVESTMENT: Category[];
}

export default function Index({ auth }: { auth: any }) {
    const [categories, setCategories] = useState<Category[]>([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [editCategory, setEditCategory] = useState<Category | null>(null);
    const [showCreate, setShowCreate] = useState(false);

    useEffect(() => {
        getAllCategories()
            .then(({ data }) => {
                setCategories(data.allCategories)
            })
            .catch(console.error);
    }, []);

    const onCreate = (createdItem: Category) => {
        setShowCreate(false)
        setCategories([createdItem, ...categories])

        animateRowItem(createdItem.id);
    }

    const onUpdate = (updatedItem: Category) => {
        setCategories(categories.map(category => {
            if (category.id === updatedItem.id) {
                return updatedItem;
            }
            return category;
        }));
        animateRowItem(updatedItem.id);
    };

    const onDelete = (deletedItem: Category) => {
        // @ts-ignore
        animateRowItem(deletedItem.id, 'deleted', () => {
            setCategories(categories.filter(item => item.id != deletedItem.id));
        });
    };

    const performSearchHandler = (e: any) => {
        setSearchQuery(e.target.value ?? '');
    }

    const performSearch = useMemo(
        () => debounce(performSearchHandler, 300)
        , []);

    // Filter categories based on search query
    const filteredCategories = useMemo(() => {
        if (!searchQuery) return categories;
        return categories.filter(category =>
            category.name.toLowerCase().includes(searchQuery.toLowerCase())
        );
    }, [categories, searchQuery]);

    // Group categories by type
    const groupedCategories = useMemo<GroupedCategories>(() => {
        const grouped: GroupedCategories = {
            INCOME: [],
            EXPENSES: [],
            SAVINGS: [],
            INVESTMENT: []
        };

        filteredCategories.forEach(category => {
            if (grouped[category.type as keyof GroupedCategories]) {
                grouped[category.type as keyof GroupedCategories].push(category);
            }
        });

        return grouped;
    }, [filteredCategories]);

    const sections: { type: keyof GroupedCategories; label: string }[] = [
        { type: 'EXPENSES', label: 'Spending' },
        { type: 'INCOME', label: 'Income' },
        { type: 'SAVINGS', label: 'Savings' },
        { type: 'INVESTMENT', label: 'Investment' },
    ];
    const visibleSections = sections.filter((section) => groupedCategories[section.type].length > 0);

    return (
        <Authenticated auth={auth}>
            <Head title="Categories" />

            <Create showCreate={showCreate} onCreate={onCreate} onClose={() => setShowCreate(false)} />

            <Edit category={editCategory} onUpdate={onUpdate} onDelete={onDelete} onClose={() => setEditCategory(null)} />

            <div className="mx-auto grid w-full max-w-[1100px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">
                <PageHeader
                    title="Categories"
                    actions={
                        <Button onClick={() => setShowCreate(true)}>
                            <PlusIcon weight="bold" aria-hidden="true" />
                            New category
                        </Button>
                    }
                />

                {categories.length > 0 && (
                    <div className="rise relative w-full sm:w-64" style={{ '--i': 1 } as React.CSSProperties}>
                        <MagnifyingGlassIcon size={16} className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                        <Input name="search" placeholder="Search categories" className="pl-9" onChange={performSearch} aria-label="Search categories" />
                    </div>
                )}

                {categories.length === 0 && (
                    <EmptyState
                        className="rounded-2xl bg-card shadow-card"
                        title="No categories yet"
                        body="Categories group your brands, like Groceries or Salary. Every brand's transactions follow its category."
                        action={<Button onClick={() => setShowCreate(true)}>New category</Button>}
                    />
                )}

                {categories.length > 0 && visibleSections.length === 0 && <EmptyState title="No categories match" body="Try another name." />}

                {visibleSections.map((section, i) => (
                    <Panel key={section.type} title={section.label} index={i + 2}>
                        <div className="grid sm:grid-cols-2 sm:gap-x-8">
                            {groupedCategories[section.type].map((category) => (
                                <div key={category.id} id={'item-' + category.id} className="grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 border-b py-2.5 last:border-b-0 sm:[&:nth-last-child(2):nth-child(odd)]:border-b-0">
                                    <Tile color={category.color} icon={category.icon} className="row-span-2" />
                                    <button type="button" onClick={() => setEditCategory(category)} className="min-w-0 truncate text-left font-semibold hover:underline">
                                        {category.name}
                                    </button>
                                    <Link href={`/transactions?category=${category.id}`} className="col-start-2 text-[12.5px] text-muted-foreground hover:text-foreground hover:underline">
                                        {category.transactionsCount} {category.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                    </Link>
                                    <CaretRightIcon size={14} weight="bold" className="col-start-3 row-span-2 row-start-1 text-muted-foreground" aria-hidden="true" />
                                </div>
                            ))}
                        </div>
                    </Panel>
                ))}
            </div>
        </Authenticated>
    );
}
