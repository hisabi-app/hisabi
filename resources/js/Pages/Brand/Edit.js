import React, { useEffect, useState } from "react";

import { updateBrand } from "../../Api";
import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import Combobox from "@/Components/Global/Combobox";
import SidePanel from '@/Components/Global/SidePanel';

export default function Edit({categories, brand, onClose, onUpdate}) {
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
                        <Label forInput="name" value="Name" />

                        <Input
                            type="text"
                            name="name"
                            value={name}
                            className="mt-1 block w-full"
                            handleChange={(e) => setName(e.target.value)}
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

                    <div className="flex items-center justify-end mt-4">
                        {isReady && 
                        <button onClick={update} className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                            Update
                        </button>
                        }
                        {!isReady && 
                        <button className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150 opacity-25" disabled>
                            Update
                        </button>
                        }
                    </div>
                </div>
            }
        </SidePanel>
    )
  }