import React, { useEffect, useState } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import SidePanel from '@/components/Global/SidePanel';
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
                    <Label htmlFor="type">
                        Type
                    </Label>

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
                    <Label htmlFor="color">
                        Color
                    </Label>

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
                    <Button disabled={! isReady} onClick={create} className={`${isReady ? '' : 'disabled opacity-25'}`}>
                        Create
                    </Button>
                </div>
            </div>
        </SidePanel>
    )
  }
