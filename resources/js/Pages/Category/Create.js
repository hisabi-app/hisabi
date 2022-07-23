import React, { useEffect, useState } from "react";

import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import SidePanel from '@/Components/Global/SidePanel';
import { createCategory } from "../../Api";

export default function Create({showCreate, onClose, onCreate}) {
    const [name, setName] = useState('')
    const [type, setType] = useState('EXPENSES')
    const [color, setColor] = useState('gray')
    const [isReady, setIsReady] = useState(false)
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(name == '' ? false : true);
    }, [name])

    const create = () => {
        if(loading || ! isReady) { return; }
        setLoading(true);

        createCategory({
            name,
            type,
            color
        })
        .then(({data}) => {
            onCreate(data.createCategory)
            setName('')
            setLoading(false);
        })
        .catch(console.error);
    }
    
    return (
        <SidePanel toggleOpen={showCreate} 
                    onClose={onClose} 
                    title={"Create Category"}>
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
                    <Label forInput="type" value="Type" />
                    
                    <select
                        id="type"
                        name="type"
                        value={type}
                        onChange={(e) => setType(e.target.value)}
                        className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        <option value="EXPENSES">EXPENSES</option>
                        <option value="INCOME">INCOME</option>
                        <option value="SAVINGS">SAVINGS</option>
                        <option value="INVESTMENT">INVESTMENT</option>
                    </select>
                </div>

                <div className="col-span-6 sm:col-span-3 mt-4">
                    <Label forInput="color" value="Color" />
                    
                    <select
                        id="color"
                        name="color"
                        value={color}
                        onChange={(e) => setColor(e.target.value)}
                        className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                        <option value="red">Red</option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="orange">Orange</option>
                        <option value="purple">Purple</option>
                        <option value="pink">Pink</option>
                        <option value="indigo">Indigo</option>
                        <option value="gray">Gray</option>
                    </select>
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