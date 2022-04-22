import React, { useEffect, useState } from "react";

import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import SidePanel from '@/Components/Global/SidePanel';
import { updateTransaction } from "../../Api";

export default function Edit({brands, transaction, onClose, onUpdate}) {
    const [amount, setAmount] = useState(0)
    const [createdAt, setCreatedAt] = useState('')
    const [brand, setBrand] = useState(0)
    const [note, setNote] = useState('')

    useEffect(() => {
        if(! transaction) return;

        setAmount(transaction.amount)
        setBrand(transaction.brand.id)
        setCreatedAt(transaction.created_at)
        setNote(transaction.note ?? '')
    }, [transaction])

    const update = () => {
        updateTransaction({
            id: transaction.id,
            amount,
            brand,
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
                      <label htmlFor="brand" className="block text-sm font-medium text-gray-700">
                        Brand
                      </label>
                      <select
                        id="brand"
                        name="brand"
                        value={brand}
                        onChange={(e) => setBrand(e.target.value)}
                        className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      >
                        {brands.map(brand => <option value={brand.id} key={brand.id}>
                            {brand.name} 
                            {brand.category ? " ("+brand.category.name+")" : ''}
                            </option>)}
                      </select>
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