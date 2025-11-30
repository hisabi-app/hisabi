import { useEffect, useState } from "react";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { createTransaction } from "@/Api";
import Combobox from "@/components/Global/Combobox";
import { Button } from "@/components/ui/button";
import { getAppCurrency } from '@/Utils';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';

export default function Create({ brands, showCreate, onClose, onCreate }) {
    const [amount, setAmount] = useState(0);
    const [brand, setBrand] = useState(null);
    const [createdAt, setCreatedAt] = useState('');
    const [note, setNote] = useState('');
    const [isReady, setIsReady] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(amount != 0 && brand != null && createdAt != '' ? true : false);
    }, [amount, brand, createdAt]);

    const handleCreate = () => {
        if (loading || !isReady || !brand) return;

        setLoading(true);

        createTransaction({
            amount,
            brandId: brand.id,
            createdAt,
            note
        })
            .then(({ data }) => {
                onCreate(data.transaction);
                // Reset form
                setBrand(null);
                setAmount(0);
                setCreatedAt('');
                setNote('');
                setLoading(false);
                onClose();
            })
            .catch(console.error);
    };

    return (
        <Dialog open={showCreate} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogTitle className="sr-only">Create Transaction</DialogTitle>
                <div className="space-y-4">
                    <div>
                        <Label htmlFor="amount">
                            {`Amount (${getAppCurrency()})`}
                        </Label>
                        <Input
                            type="number"
                            name="amount"
                            value={amount}
                            className="mt-1"
                            onChange={(e) => setAmount(e.target.value > 0 ? e.target.value : 0)}
                        />
                    </div>

                    <div>
                        <Label htmlFor="date">
                            Date
                        </Label>
                        <Input
                            type="date"
                            name="date"
                            value={createdAt}
                            className="mt-1"
                            onChange={(e) => setCreatedAt(e.target.value)}
                        />
                    </div>

                    <div>
                        <Combobox
                            label="Brand"
                            items={brands}
                            initialSelectedItem={brand}
                            onChange={(item) => setBrand(item)}
                            displayInputValue={(item) => item ? `${item.name} (${item.category?.name ?? 'N/A'})` : ''}
                            displayOptionValue={(item) => item ? `${item.name} (${item.category?.name ?? 'N/A'})` : ''}
                        />
                    </div>

                    <div>
                        <Label htmlFor="note">
                            Note (optional)
                        </Label>
                        <Input
                            type="text"
                            name="note"
                            value={note}
                            className="mt-1"
                            onChange={(e) => setNote(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center justify-end pt-2">
                        <Button
                            disabled={!isReady || loading}
                            onClick={handleCreate}
                        >
                            Create
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
