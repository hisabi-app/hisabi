import { useEffect, useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import Edit from './Edit';
import Create from './Create';
import { Button } from '@/components/ui/button';
import { getAllCategories } from '@/Api';
import { animateRowItem } from '@/Utils';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import CategoryStats from '@/components/Domain/CategoryStats';
import { getCategoryIcon } from '@/Utils/categoryIcons';

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

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Categories</h2>
            <Button onClick={() => setShowCreate(true)}>Create Category</Button>
        </div>
    )

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Categories" />

            <Create showCreate={showCreate}
                onCreate={onCreate}
                onClose={() => setShowCreate(false)} />

            <Edit
                category={editCategory}
                onUpdate={onUpdate}
                onDelete={onDelete}
                onClose={() => setEditCategory(null)}
            />

            <div className="p-4">
                <div className="max-w-7xl mx-auto grid gap-4">
                    <CategoryStats />

                    {categories.length > 0 && (
                        <Tabs defaultValue="all" className="w-full">
                            <div className="flex justify-between items-center mb-2">
                                <Input
                                    name="search"
                                    placeholder='Search..'
                                    className='bg-white max-w-56'
                                    onChange={performSearch}
                                />
                                <TabsList>
                                    <TabsTrigger value="all">
                                        All ({filteredCategories.length})
                                    </TabsTrigger>
                                    {groupedCategories.INCOME.length > 0 && (
                                        <TabsTrigger value="INCOME">
                                            Income ({groupedCategories.INCOME.length})
                                        </TabsTrigger>
                                    )}
                                    {groupedCategories.EXPENSES.length > 0 && (
                                        <TabsTrigger value="EXPENSES">
                                            Expenses ({groupedCategories.EXPENSES.length})
                                        </TabsTrigger>
                                    )}
                                    {groupedCategories.SAVINGS.length > 0 && (
                                        <TabsTrigger value="SAVINGS">
                                            Savings ({groupedCategories.SAVINGS.length})
                                        </TabsTrigger>
                                    )}
                                    {groupedCategories.INVESTMENT.length > 0 && (
                                        <TabsTrigger value="INVESTMENT">
                                            Investment ({groupedCategories.INVESTMENT.length})
                                        </TabsTrigger>
                                    )}
                                </TabsList>
                            </div>

                            <TabsContent value="all">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    {filteredCategories.map((category) => {
                                        const CategoryIcon = category.icon ? getCategoryIcon(category.icon) : null;
                                        return (
                                            <Card key={category.id} className='py-0' id={'item-' + category.id}>
                                                <CardContent className='flex justify-between items-center p-4'>
                                                    <div className='flex gap-3 items-center'>
                                                        {CategoryIcon ? (
                                                            <div className={`size-10 rounded-full flex items-center justify-center badge badge-${category.color}`}>
                                                                <CategoryIcon size={20} weight="regular" className="text-current" />
                                                            </div>
                                                        ) : (
                                                            <Badge
                                                                className={`badge badge-${category.color} h-3 w-3 p-0 rounded-full`}
                                                                variant="outline"
                                                            />
                                                        )}
                                                        <div>
                                                            <button onClick={() => setEditCategory(category)} className='font-medium hover:underline text-left'>
                                                                <p>{category.name}</p>
                                                            </button>
                                                            <p className='text-muted-foreground text-xs'>
                                                                {category.transactionsCount} {category.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            </TabsContent>

                            {groupedCategories.INCOME.length > 0 && (
                                <TabsContent value="INCOME">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        {groupedCategories.INCOME.map((category) => {
                                            const CategoryIcon = category.icon ? getCategoryIcon(category.icon) : null;
                                            return (
                                                <Card key={category.id} className='py-0' id={'item-' + category.id}>
                                                    <CardContent className='flex justify-between items-center p-4'>
                                                        <div className='flex gap-3 items-center'>
                                                            {CategoryIcon ? (
                                                                <div className={`size-10 rounded-full flex items-center justify-center badge badge-${category.color}`}>
                                                                    <CategoryIcon size={20} weight="regular" className="text-current" />
                                                                </div>
                                                            ) : (
                                                                <Badge
                                                                    className={`badge badge-${category.color} h-3 w-3 p-0 rounded-full`}
                                                                    variant="outline"
                                                                />
                                                            )}
                                                            <div>
                                                                <button onClick={() => setEditCategory(category)} className='font-medium hover:underline text-left'>
                                                                    <p>{category.name}</p>
                                                                </button>
                                                                <p className='text-muted-foreground text-xs'>
                                                                    {category.transactionsCount} {category.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            );
                                        })}
                                    </div>
                                </TabsContent>
                            )}

                            {groupedCategories.EXPENSES.length > 0 && (
                                <TabsContent value="EXPENSES">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        {groupedCategories.EXPENSES.map((category) => {
                                            const CategoryIcon = category.icon ? getCategoryIcon(category.icon) : null;
                                            return (
                                                <Card key={category.id} className='py-0' id={'item-' + category.id}>
                                                    <CardContent className='flex justify-between items-center p-4'>
                                                        <div className='flex gap-3 items-center'>
                                                            {CategoryIcon ? (
                                                                <div className={`size-10 rounded-full flex items-center justify-center badge badge-${category.color}`}>
                                                                    <CategoryIcon size={20} weight="regular" className="text-current" />
                                                                </div>
                                                            ) : (
                                                                <Badge
                                                                    className={`badge badge-${category.color} h-3 w-3 p-0 rounded-full`}
                                                                    variant="outline"
                                                                />
                                                            )}
                                                            <div>
                                                                <button onClick={() => setEditCategory(category)} className='font-medium hover:underline text-left'>
                                                                    <p>{category.name}</p>
                                                                </button>
                                                                <p className='text-muted-foreground text-xs'>
                                                                    {category.transactionsCount} {category.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            );
                                        })}
                                    </div>
                                </TabsContent>
                            )}

                            {groupedCategories.SAVINGS.length > 0 && (
                                <TabsContent value="SAVINGS">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        {groupedCategories.SAVINGS.map((category) => {
                                            const CategoryIcon = category.icon ? getCategoryIcon(category.icon) : null;
                                            return (
                                                <Card key={category.id} className='py-0' id={'item-' + category.id}>
                                                    <CardContent className='flex justify-between items-center p-4'>
                                                        <div className='flex gap-3 items-center'>
                                                            {CategoryIcon ? (
                                                                <div className={`size-10 rounded-full flex items-center justify-center badge badge-${category.color}`}>
                                                                    <CategoryIcon size={20} weight="regular" className="text-current" />
                                                                </div>
                                                            ) : (
                                                                <Badge
                                                                    className={`badge badge-${category.color} h-3 w-3 p-0 rounded-full`}
                                                                    variant="outline"
                                                                />
                                                            )}
                                                            <div>
                                                                <button onClick={() => setEditCategory(category)} className='font-medium hover:underline text-left'>
                                                                    <p>{category.name}</p>
                                                                </button>
                                                                <p className='text-muted-foreground text-xs'>
                                                                    {category.transactionsCount} {category.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            );
                                        })}
                                    </div>
                                </TabsContent>
                            )}

                            {groupedCategories.INVESTMENT.length > 0 && (
                                <TabsContent value="INVESTMENT">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        {groupedCategories.INVESTMENT.map((category) => {
                                            const CategoryIcon = category.icon ? getCategoryIcon(category.icon) : null;
                                            return (
                                                <Card key={category.id} className='py-0' id={'item-' + category.id}>
                                                    <CardContent className='flex justify-between items-center p-4'>
                                                        <div className='flex gap-3 items-center'>
                                                            {CategoryIcon ? (
                                                                <div className={`size-10 rounded-full flex items-center justify-center badge badge-${category.color}`}>
                                                                    <CategoryIcon size={20} weight="regular" className="text-current" />
                                                                </div>
                                                            ) : (
                                                                <Badge
                                                                    className={`badge badge-${category.color} h-3 w-3 p-0 rounded-full`}
                                                                    variant="outline"
                                                                />
                                                            )}
                                                            <div>
                                                                <button onClick={() => setEditCategory(category)} className='font-medium hover:underline text-left'>
                                                                    <p>{category.name}</p>
                                                                </button>
                                                                <p className='text-muted-foreground text-xs'>
                                                                    {category.transactionsCount} {category.transactionsCount === 1 ? 'transaction' : 'transactions'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            );
                                        })}
                                    </div>
                                </TabsContent>
                            )}
                        </Tabs>
                    )}
                </div>
            </div>
        </Authenticated>
    );
}
