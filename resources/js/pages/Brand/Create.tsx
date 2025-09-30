import React, { useEffect, useState } from 'react';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import Combobox from "@/components/Global/Combobox";
import SidePanel from '@/components/Global/SidePanel';
import { createBrand } from '../../Api';

export default function Edit({categories, showCreate, onClose, onCreate}) {
    const [name, setName] = useState('')
    const [categoryId, setCategoryId] = useState(0)
    const [isReady, setIsReady] = useState(false)
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(name != '' && categoryId != 0 ? true : false);
    }, [name, categoryId])

    const create = () => {
        if(loading || ! isReady) { return; }
        setLoading(true);

        createBrand({
            name,
            categoryId
        })
        .then(({data}) => {
            onCreate(data.createBrand)
            setCategoryId(0)
            setName('')
            setLoading(false);
        })
        .catch(console.error);
    }

    return (
        <SidePanel toggleOpen={showCreate}
                    onClose={onClose}
                    title={"Create Brand"}>
            <div>
                <div>
                    <Label htmlFor="name">
                        Name
                    </Label>

                    <Input
                        type="text"
                        name="name"
                        value={name}
                        className="mt-1 block w-full"
                        onChange={(e) => setName(e.target.value)}
                    />
                </div>


                <div className="col-span-6 sm:col-span-3 mt-4">
                    <Combobox
                        label="Category"
                        items={categories}
                        onChange={(item) => setCategoryId(item.id)}
                        />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <Button disabled={! isReady} onClick={create} className={`${isReady ? '' : 'disabled opacity-25'}`}>
                        Create
                    </Button>
                </div>
            </div>
        </SidePanel>
    )
  }
