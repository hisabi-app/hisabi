import Input from "@/Components/Input";
import Label from "@/Components/Label";
import SidePanel from '@/Components/SidePanel';
import { useEffect, useState } from "react";

export default function Edit({brand, onClose, onUpdate}) {
    const [categories, setCategories] = useState([])
    const [name, setName] = useState(0)
    const [category, setCategory] = useState(0)

    useEffect(() => {
        Api.getAllCategories()
            .then(({data}) => {
                setCategories(data.data.allCategories)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        if(! brand) return;

        setName(brand.name)
        if(brand.category) {
            setCategory(brand.category.id)
        }
    }, [brand])

    const update = () => {
        Api.updateBrand({
            id: brand.id,
            name,
            category
        })
        .then(({data}) => {
            onUpdate(data.data.updateBrand)
            setCategory(0)
        })
        .catch(console.error);
    }

    let isReady = name != '' && category != 0;
    
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
                      <label htmlFor="category" className="block text-sm font-medium text-gray-700">
                        Category
                      </label>
                      <select
                        id="category"
                        name="category"
                        value={category}
                        onChange={(e) => setCategory(e.target.value)}
                        className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      >
                          <option value={0}>Select one</option>
                        {categories.map(item => <option value={item.id} key={item.id}>{item.name}</option>)}
                      </select>
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