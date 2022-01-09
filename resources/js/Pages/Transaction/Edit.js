import Input from "@/Components/Input";
import Label from "@/Components/Label";
import SidePanel from '@/Components/SidePanel';
import { useEffect, useState } from "react";

export default function Edit({transaction, onClose, onUpdate}) {
    const [brands, setBrands] = useState([])
    const [amount, setAmount] = useState(0)
    const [brand, setBrand] = useState(0)

    useEffect(() => {
        Api.getBrands()
            .then(({data}) => {
                setBrands(data.data.brands)
            })
            .catch(console.error);
    }, []);

    useEffect(() => {
        if(! transaction) return;

        setAmount(transaction.amount)
        setBrand(transaction.brand.id)
    }, [transaction])

    const update = () => {
        Api.updateTransaction({
            id: transaction.id,
            amount,
            brand
        })
        .then(({data}) => {
            onUpdate(data.data.updateTransaction)
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
                        <Label forInput="amount" value="Amount" />

                        <Input
                            type="text"
                            name="amount"
                            value={amount}
                            className="mt-1 block w-full"
                            handleChange={(e) => setAmount(e.target.value)}
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
                        {brands.map(brand => <option value={brand.id} key={brand.id}>{brand.name}</option>)}
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