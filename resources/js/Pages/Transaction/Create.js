import React, { useEffect, useState } from "react";

import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import { createTransaction } from "../../Api";
import SidePanel from '@/Components/Global/SidePanel';

export default function Create({brands, showCreate, onClose, onCreate}) {
    const [amount, setAmount] = useState(0);
    const [brandId, setBrandId] = useState(0);
    const [createdAt, setCreatedAt] = useState('');
    const [note, setNote] = useState('');
    const [isReady, setIsReady] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(amount != 0 && brandId != 0 && createdAt != '' ? true : false);
    }, [amount, brandId, createdAt])

    const create = () => {
        if(loading || ! isReady) { return; }
        setLoading(true);

        createTransaction({
            amount,
            brandId,
            createdAt,
            note
        })
        .then(({data}) => {
            onCreate(data.createTransaction)
            setBrandId(0)
            setAmount(0)
            setCreatedAt('')
            setLoading(false);
        })
        .catch(console.error);
    }
    
    return (
        <SidePanel toggleOpen={showCreate} 
                    onClose={onClose} 
                    title={"Create Transaction"}>
            <div>
                <div>
                    <Label forInput="amount" value={`Amount (${AppCurrency})`} />

                    <Input
                        type="text"
                        name="amount"
                        value={amount}
                        className="mt-1 block w-full"
                        handleChange={(e) => setAmount(e.target.value > 0 ? e.target.value : 0)}
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
                    <Label forInput="brand" value="Brand" />

                    <select
                        id="brand"
                        name="brand"
                        value={brandId}
                        onChange={(e) => setBrandId(e.target.value)}
                        className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        <option value={0}>Select one</option>
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
                    <button onClick={create} className={`inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150 ${isReady ? '' : 'disabled opacity-25'}`}>
                        {loading && <svg xmlns="http://www.w3.org/2000/svg" className="mr-2 animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>}
                        Create
                    </button>
                </div>
            </div>
        </SidePanel>
    )
  }