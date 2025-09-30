import { useEffect, useState } from "react";

import { updateTransaction, deleteResource } from "../../Api";
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { LongPressButton } from '@/components/ui/long-press-button';
import Combobox from "@/components/Global/Combobox";
import SidePanel from '@/components/Global/SidePanel';

export default function Edit({brands, transaction, onClose, onUpdate, onDelete}) {
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
                        <Label htmlFor="amount">
                            {`Amount (${AppCurrency})`}
                        </Label>

                        <Input
                            type="number"
                            name="amount"
                            value={amount}
                            className="mt-1 block w-full"
                            onChange={(e) => setAmount(e.target.value)}
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
                            initialSelectedItem={brand}
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

                    <div className="flex items-center justify-between mt-4">
                        <LongPressButton
                            onLongPress={() => {
                                deleteResource({id: transaction.id, resource: 'Transaction'})
                                    .then(() => {
                                        onDelete(transaction)
                                        onClose()
                                    })
                                    .catch(console.error);
                            }}
                        >
                            Hold to Delete
                        </LongPressButton>
                        <Button onClick={update}>
                            Update
                        </Button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }
