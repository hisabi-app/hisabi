import React, {useEffect, useMemo, useState} from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import LoadMore from '@/components/Global/LoadMore';
import Edit from './Edit';
import Create from './Create';
import { Button } from '@/components/ui/button';
import { getCategories } from '@/Api';
import { animateRowItem } from '@/Utils';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

export default function Index({auth}) {
    const [categories, setCategories] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [editCategory, setEditCategory] = useState(null);
    const [showCreate, setShowCreate] = useState(false);

    useEffect(() => {
        if(! hasMorePages) return;
        setLoading(true);

        getCategories(currentPage, searchQuery)
            .then(({data}) => {
                setCategories([...categories, ...data.categories.data])
                setHasMorePages(data.categories.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    useEffect(() => {
        setLoading(true);

        getCategories(currentPage, searchQuery)
            .then(({data}) => {
                setCategories([...categories, ...data.categories.data])
                setHasMorePages(data.categories.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [searchQuery]);

    const onCreate = (createdItem) => {
        setShowCreate(false)
        setCategories([createdItem, ...categories])

        animateRowItem(createdItem.id);
    }

    const onUpdate = (updatedItem) => {
        setCategories(categories.map(category => {
            if(category.id === updatedItem.id) {
                return updatedItem
            }

            return category
        }));

        animateRowItem(updatedItem.id);
        setEditCategory(null)
    }

    const onDelete = (deletedItem) => {
        animateRowItem(deletedItem.id, 'deleted', () => {
            setCategories(categories.filter(item => item.id != deletedItem.id));
        })
    }

    const performSearchHandler = (e) => {
        setCategories([]);
        setSearchQuery(e.target.value ?? '');
        setCurrentPage(1);
    }

    const performSearch = useMemo(
        () => debounce(performSearchHandler, 300)
        , []);

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

            <Edit category={editCategory}
                onClose={() => setEditCategory(null)}
                onUpdate={onUpdate}
                onDelete={onDelete}
                />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className='mb-6'>
                        <Input
                            name="search"
                            placeholder='Search..'
                            className='bg-white max-w-sm'
                            onChange={performSearch}
                        />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                        {categories.length > 0 && categories.map((category) => (
                            <Card key={category.id} className='py-0' id={'item-' + category.id}>
                                <CardContent className='flex justify-between items-center px-4 py-3'>
                                    <div className='flex gap-2 items-center'>
                                        <button onClick={() => setEditCategory(category)} className='font-medium hover:underline'>
                                            <Badge className={"badge badge-" + category.color} variant="outline">{category.name}</Badge>
                                        </button>
                                    </div>
                                    <div className='flex gap-2 items-center'>
                                        <p className={`text-sm font-medium ${category.type === 'INCOME' ? 'text-green-500' : 'text-gray-900'}`}>
                                            {category.type}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    <LoadMore hasContent={categories.length > 0} hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage + 1)} />
                </div>
            </div>
        </Authenticated>
    );
}
