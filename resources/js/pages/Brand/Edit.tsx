import React, { useEffect, useState } from "react";

import { updateBrand, deleteResource } from "../../Api";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { LongPressButton } from '@/components/ui/long-press-button';
import Combobox from "@/components/Global/Combobox";
import SidePanel from '@/components/Global/SidePanel';

export default function Edit({categories, brand, onClose, onUpdate, onDelete}) {
    const [name, setName] = useState(0)
    const [category, setCategory] = useState(null)

    useEffect(() => {
        if(! brand) return;

        setName(brand.name)
        if(brand.category) {
            setCategory(brand.category)
        }
    }, [brand])

    const update = () => {
        updateBrand({
            id: brand.id,
            name,
            categoryId: category.id
        })
        .then(({data}) => {
            onUpdate(data.updateBrand)
            setCategory(null)
        })
        .catch(console.error);
    }

    let isReady = name != '' && category != null;

    return (
        <SidePanel toggleOpen={! brand ? false : true}
                    onClose={onClose}
                    title={"Edit Brand"}>
            {
                brand &&
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
                            initialSelectedItem={category}
                            onChange={(item) => setCategory(item)}
                            displayInputValue={(item) => item?.name ?? ''}
                            />
                    </div>

                    <div className="flex items-center justify-between mt-4">
                        <LongPressButton
                            onLongPress={() => {
                                deleteResource({id: brand.id, resource: 'Brand'})
                                    .then(() => {
                                        onDelete(brand)
                                        onClose()
                                    })
                                    .catch(console.error);
                            }}
                        >
                            Hold to Delete
                        </LongPressButton>
                        <Button disabled={!isReady} onClick={update}>
                            Update
                        </Button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }
