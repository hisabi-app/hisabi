import React, {useEffect, useMemo, useState} from 'react';
import { Head } from '@inertiajs/react';
import { debounce } from 'lodash';

import Authenticated from '@/Layouts/Authenticated';
import LoadMore from '@/components/Global/LoadMore';
import Create from './Create';
import Edit from './Edit';
import { Button } from '@/components/ui/button';
import { getSms } from '@/Api';
import { animateRowItem, cutString } from '@/Utils';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

export default function Sms({auth}) {
    const [sms, setSms] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [showCreate, setShowCreate] = useState(false);
    const [editItem, setEditItem] = useState(null);

    useEffect(() => {
        if(! hasMorePages) return;
        setLoading(true);

        getSms(currentPage, searchQuery)
            .then(({data}) => {
                setSms([...sms, ...data.sms.data])
                setHasMorePages(data.sms.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [currentPage]);

    useEffect(() => {
        setLoading(true);

        getSms(currentPage, searchQuery)
            .then(({data}) => {
                setSms([...sms, ...data.sms.data])
                setHasMorePages(data.sms.paginatorInfo.hasMorePages)
                setLoading(false);
            })
            .catch(console.error);
    }, [searchQuery]);

    const onUpdate = (updatedItem) => {
        setSms(sms.map(item => {
            if(item.id === updatedItem.id) {
                return updatedItem
            }

            return item
        }));

        animateRowItem(updatedItem.id)
    }

    const onDelete = (deletedItem) => {
        animateRowItem(deletedItem.id, 'deleted', () => {
            setSms(sms.filter(item => item.id != deletedItem.id));
        })
    }

    const performSearchHandler = (e) => {
        setSms([]);
        setSearchQuery(e.target.value ?? '');
        setCurrentPage(1);
    }

    const performSearch = useMemo(
        () => debounce(performSearchHandler, 300)
        , []);

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>SMS Parser</h2>
            <Button onClick={() => setShowCreate(true)}>Parse SMS</Button>
        </div>
    )

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="SMS Parser" />

            <Create showCreate={showCreate}
                onCreate={(createdSms) => {
                    setShowCreate(false)
                    setSms([...createdSms, ...sms])
                }}
                onClose={() => setShowCreate(false)} />

            <Edit sms={editItem}
                onClose={() => setEditItem(null)}
                onUpdate={item => {
                    onUpdate(item)
                    setEditItem(null)
                }}
                onDelete={onDelete}
                />

            <div className="p-4">
                <div className="max-w-7xl mx-auto">
                    {sms.length > 0 && <div className='mb-4'>
                        <Input
                            name="search"
                            placeholder='Search..'
                            className='bg-white max-w-56'
                            onChange={performSearch}
                        />
                    </div>}

                    <div className="grid gap-2">
                        {sms.length > 0 && sms.map((item) => (
                            <Card key={item.id} className='py-0' id={'item-' + item.id}>
                                <CardContent className='flex justify-between items-center px-4 py-3'>
                                    <div className='flex gap-3 items-center flex-1'>
                                        <button onClick={() => setEditItem(item)} className='text-left hover:underline flex-1'>
                                            <p className='font-medium text-sm'>{cutString(item.body, 80)}</p>
                                        </button>
                                    </div>
                                    <div className='flex gap-2 items-center'>
                                        {item.transaction_id ? (
                                            <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200">
                                                ✅ Valid
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline" className="bg-red-50 text-red-700 border-red-200">
                                                ❌ Invalid
                                            </Badge>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}

                        <LoadMore hasContent={sms.length > 0} hasMorePages={hasMorePages} loading={loading} onClick={() => setCurrentPage(currentPage + 1)} />
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
