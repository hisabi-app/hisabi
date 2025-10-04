import React, {useEffect, useMemo, useState} from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import LoadMore from '@/components/Global/LoadMore';
import Edit from './Edit';
import Create from './Create';
import { Button } from '@/components/ui/button';
import { getAllCategories, getBrands } from '@/Api';
import { animateRowItem } from '@/Utils';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ArrowElbowDownRightIcon } from '@phosphor-icons/react';
import { Badge } from '@/components/ui/badge';
import BrandStats from '@/components/Domain/BrandStats';
import { getCategoryIcon } from '@/Utils/categoryIcons';

export default function Index({auth}) {
    const [brands, setBrands] = useState([]);
    const [allCategories, setAllCategories] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [editItem, setEditItem] = useState(null);
    const [showCreate, setShowCreate] = useState(false);

    useEffect(() => {
        getAllCategories()
            .then(({data}) => {
                setAllCategories(data.allCategories)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        if(! hasMorePages) return;
        setLoading(true);

        getBrands(currentPage, searchQuery)
            .then(({data}) => {
                setBrands([...brands, ...data.brands.data])
                setHasMorePages(data.brands.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    useEffect(() => {
        setLoading(true);

        getBrands(currentPage, searchQuery)
            .then(({data}) => {
                setBrands([...brands, ...data.brands.data])
                setHasMorePages(data.brands.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [searchQuery]);

    const onCreate = (createdItem) => {
        setShowCreate(false)
        setBrands([createdItem, ...brands])

        animateRowItem(createdItem.id);
    }

    const onUpdate = (updatedItem) => {
        setBrands(brands.map(brand => {
            if (brand.id === updatedItem.id) {
                return updatedItem;
            }
            return brand;
        }));
        animateRowItem(updatedItem.id);
    };

    const onDelete = (deletedItem) => {
        animateRowItem(deletedItem.id, 'deleted', () => {
            setBrands(brands.filter(item => item.id != deletedItem.id));
        });
    };

    const performSearchHandler = (e) => {
        setBrands([]);
        setSearchQuery(e.target.value ?? '');
        setCurrentPage(1);
    }

    const performSearch = useMemo(
        () => debounce(performSearchHandler, 300)
        , []);

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Brands</h2>
            <Button onClick={() => setShowCreate(true)}>Create Brand</Button>
        </div>
    )

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Brands" />

            <Create showCreate={showCreate}
                categories={allCategories}
                onCreate={onCreate}
                onClose={() => setShowCreate(false)} />

            <Edit 
                brand={editItem}
                categories={allCategories}
                onUpdate={onUpdate}
                onDelete={onDelete}
                onClose={() => setEditItem(null)}
            />

            <div className="p-4">
                <div className="max-w-7xl mx-auto">
                    <div className='mb-4'>
                        <BrandStats />
                    </div>

                    {(brands.length > 0 || searchQuery) && (<div className='mb-4'>
                        <Input
                            name="search"
                            placeholder='Search..'
                            className='bg-white max-w-56'
                            onChange={performSearch}
                        />
                    </div>)}

                    <div className="grid gap-2">
                        {brands.length > 0 && brands.map((brand) => {
                            const hasCategory = brand.category !== null;
                            const isUncategorized = !hasCategory;
                            const CategoryIcon = brand.category?.icon 
                                ? getCategoryIcon(brand.category.icon) 
                                : null;
                            
                            return (
                                <Card key={brand.id} className={`py-0 ${isUncategorized ? 'bg-red-50 border-red-100' : ''}`} id={'item-' + brand.id}>
                                    <CardContent className='flex justify-between items-center px-4 py-3'>
                                        <div className='flex gap-2 items-center'>
                                            {CategoryIcon ? (
                                                <div className={`size-10 rounded-full flex items-center justify-center badge badge-${brand.category.color}`}>
                                                    <CategoryIcon size={24} weight="regular" className="text-current" />
                                                </div>
                                            ) : (
                                                <Avatar className='size-10'>
                                                    <AvatarImage src={brand.image} />
                                                    <AvatarFallback>{brand.name.charAt(0)}</AvatarFallback>
                                                </Avatar>
                                            )}
                                            <div>
                                                <button onClick={() => setEditItem(brand)} className='font-medium hover:underline'>{brand.name}</button>
                                                <div className='flex gap-1 text-muted-foreground items-center'>
                                                    <ArrowElbowDownRightIcon size={10} weight="bold" />
                                                    <p className='text-xs'>{brand.category ? <span>{brand.category.name}</span> : '-'}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div className='flex gap-2 items-center'>
                                            <p className='text-muted-foreground text-sm min-w-26 text-right'>{brand.transactionsCount} {brand.transactionsCount === 1 ? 'transaction' : 'transactions'}</p>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}

                        <LoadMore hasContent={brands.length > 0} hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage + 1)} />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
