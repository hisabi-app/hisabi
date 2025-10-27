import { useEffect, useState } from "react";

import { updateTransaction, deleteTransaction } from "../../Api";
import { Input } from '@/components/ui/input';
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { LongPressButton } from '@/components/ui/long-press-button';
import Combobox from "@/components/Global/Combobox";
import { getAppCurrency } from '@/Utils';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';

export default function Edit({ transaction, brands, onUpdate, onDelete, onClose }) {
    const [amount, setAmount] = useState(0);
    const [createdAt, setCreatedAt] = useState('');
    const [brand, setBrand] = useState(null);
    const [note, setNote] = useState('');

    useEffect(() => {
        if (!transaction) return;

        setAmount(transaction.amount);
        setBrand(transaction.brand);
        setCreatedAt(transaction.created_at);
        setNote(transaction.note ?? '');
    }, [transaction]);

    const handleUpdate = () => {
        if (!transaction || !brand) return;

        const transactionId = transaction.id;
        updateTransaction({
            id: transactionId,
            amount,
            brandId: brand.id,
            createdAt,
            note
        })
        .then(({ data }) => {
            onUpdate(data.transaction);
            onClose();
        })
        .catch(console.error);
    };

    const handleDelete = () => {
        if (!transaction) return;

        const transactionToDelete = transaction;
        deleteTransaction(transactionToDelete.id)
            .then(() => {
                onDelete(transactionToDelete);
                onClose();
            })
            .catch(console.error);
    };

    return (
        <Dialog open={!!transaction} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogTitle className="sr-only">Edit Transaction</DialogTitle>
                {transaction && (
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
                                onChange={(e) => setAmount(e.target.value)}
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

                        <div className="flex items-center justify-end pt-2 gap-2">
                            <LongPressButton onLongPress={handleDelete}>
                                Hold to Delete
                            </LongPressButton>
                            <Button onClick={handleUpdate}>
                                Update
                            </Button>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}

