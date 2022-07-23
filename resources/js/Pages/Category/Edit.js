import React, { useEffect, useState } from "react";

import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import SidePanel from '@/Components/Global/SidePanel';
import { updateCategory } from "../../Api";

export default function Edit({category, onClose, onUpdate}) {
    const [name, setName] = useState('')
    const [type, setType] = useState('')
    const [color, setColor] = useState('gray')

    useEffect(() => {
        if(! category) return;

        setName(category.name)
        setType(category.type)
        setColor(category.color)
    }, [category])

    const update = () => {
        updateCategory({
            id: category.id,
            name,
            type,
            color
        })
        .then(({data}) => {
            onUpdate(data.updateCategory)
        })
        .catch(console.error);
    }
    
    return (
        <SidePanel toggleOpen={! category ? false : true} 
                    onClose={onClose} 
                    title={"Edit Category"}>
            {
                category &&
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
                      <label htmlFor="type" className="block text-sm font-medium text-gray-700">
                        Type
                      </label>
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
                    <label htmlFor="type" className="block text-sm font-medium text-gray-700">
                        Type
                      </label>
                    
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
                        <button onClick={update} className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                            Update
                        </button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }