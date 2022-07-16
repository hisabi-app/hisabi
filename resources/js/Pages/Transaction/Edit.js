import React, { useEffect, useState } from "react";

import { updateTransaction } from "../../Api";
import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import Combobox from "@/Components/Global/Combobox";
import SidePanel from '@/Components/Global/SidePanel';

export default function Edit({brands, transaction, onClose, onUpdate}) {
    const [amount, setAmount] = useState(0)
    const [createdAt, setCreatedAt] = useState('')
    const [brand, setBrand] = useState(null)
    const [note, setNote] = useState('')

    useEffect(() => {
        if(! transaction) return;

        setAmount(transaction.amount)
        setBrand(transaction.brand)
        setCreatedAt(transaction.created_at)
        setNote(transaction.note ?? '')
    }, [transaction])

    const update = () => {
        updateTransaction({
            id: transaction.id,
            amount,
            brandId: brand.id,
            createdAt,
            note
        })
        .then(({data}) => {
            onUpdate(data.updateTransaction)
        })
        .catch(console.error);
    }
    
    return (
        <SidePanel toggleOpen={! transaction ? false : true} 
                    onClose={onClose} 
                    title={"Edit Transaction"}>
            {
                transaction &&
                <div>
                    <div>
                        <Label forInput="amount" value={`Amount (${AppCurrency})`} />

                        <Input
                            type="text"
                            name="amount"
                            value={amount}
                            className="mt-1 block w-full"
                            handleChange={(e) => setAmount(e.target.value)}
                        />
                    </div>

                    <div className="mt-4">
                        <Label forInput="date" value="Date" />

                        <Input
                            type="date"
                            name="date"
                            value={createdAt}
                            className="mt-1 block w-full"
                            handleChange={(e) => setCreatedAt(e.target.value)}
                        />
                    </div>

                    <div className="col-span-6 sm:col-span-3 mt-4">
                        <Combobox 
                            label="Brand" 
                            items={brands} 
                            initialSelectedItem={brand}
                            onChange={(item) => setBrand(item)}
                            displayInputValue={(item) => item ? `${item.name} (${item.category?.name ?? 'N/A'})` : ''}
                            displayOptionValue={(item) => item ? `${item.name} (${item.category?.name ?? 'N/A'})` : ''}
                            />
                    </div>

                    <div className="mt-4">
                        <Label forInput="note" value="Note (optional)" />

                        <Input
                            type="text"
                            name="note"
                            value={note}
                            className="mt-1 block w-full"
                            handleChange={(e) => setNote(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center justify-end mt-4">
                        <button onClick={update} className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                            Update
                        </button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }