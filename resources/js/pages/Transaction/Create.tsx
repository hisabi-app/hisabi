import { useEffect, useState } from "react";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { createTransaction } from "../../Api";
import Combobox from "@/components/Global/Combobox";
import SidePanel from '@/components/Global/SidePanel';
import { Button } from "@/components/ui/button";

export default function Create({brands, showCreate, onClose, onCreate}) {
    const [amount, setAmount] = useState(0);
    const [brand, setBrand] = useState(null);
    const [createdAt, setCreatedAt] = useState('');
    const [note, setNote] = useState('');
    const [isReady, setIsReady] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(amount != 0 && brand != null && createdAt != '' ? true : false);
    }, [amount, brand, createdAt])

    const create = () => {
        if(loading || ! isReady) { return; }
        setLoading(true);

        createTransaction({
            amount,
            brandId: brand.id,
            createdAt,
            note
        })
        .then(({data}) => {
            onCreate(data.createTransaction)
            setBrand(null)
            setAmount(0)
            setCreatedAt('')
            setNote('')
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
                    <Label htmlFor="amount">
                        {`Amount (${AppCurrency})`}
                    </Label>

                    <Input
                        type="number"
                        name="amount"
                        value={amount}
                        className="mt-1 block w-full"
                        onChange={(e) => setAmount(e.target.value > 0 ? e.target.value : 0)}
                    />
                </div>

                <div className="mt-4">
                    <Label htmlFor="date">
                        Date
                    </Label>

                    <Input
                        type="date"
                        name="date"
                        value={createdAt}
                        className="mt-1 block w-full"
                        onChange={(e) => setCreatedAt(e.target.value)}
                    />
                </div>

                <div className="col-span-6 sm:col-span-3 mt-4">
                        <Combobox
                            label="Brand"
                            items={brands}
                            onChange={(item) => setBrand(item)}
                            displayInputValue={(item) => item ? `${item.name} (${item.category?.name ?? 'N/A'})` : ''}
                            displayOptionValue={(item) => item ? `${item.name} (${item.category?.name ?? 'N/A'})` : ''}
                            />
                </div>

                <div className="mt-4">
                    <Label htmlFor="note">
                        Note (optional)
                    </Label>

                    <Input
                        type="text"
                        name="note"
                        value={note}
                        className="mt-1 block w-full"
                        onChange={(e) => setNote(e.target.value)}
                    />
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
