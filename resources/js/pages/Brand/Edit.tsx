import { useEffect, useState } from 'react';

import Combobox from '@/components/Global/Combobox';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LongPressButton } from '@/components/ui/long-press-button';
import { deleteBrand, updateBrand } from '../../Api';

type Category = { id: number; name: string };

type Props = {
    categories: Category[];
    brand: any;
    onUpdate: (brand: any) => void;
    onDelete: (brand: any) => void;
    onClose: () => void;
};

export default function Edit({ categories, brand, onUpdate, onDelete, onClose }: Props) {
    const [name, setName] = useState('');
    const [category, setCategory] = useState<Category | null>(null);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        // Closing keeps the last brand's fields on screen while the dialog fades out
        if (!brand) return;

        setName(brand.name);
        setCategory(brand.category ?? null);
        setError(null);
    }, [brand]);

    const valid = name.trim() !== '' && category !== null;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!brand || !category || !valid || saving) return;

        setSaving(true);
        setError(null);
        updateBrand({ id: brand.id, name: name.trim(), categoryId: category.id })
            .then(({ data }) => {
                onUpdate(data.updateBrand);
                onClose();
            })
            .catch(() => setError("Couldn't save the brand. Try again."))
            .finally(() => setSaving(false));
    };

    const handleDelete = () => {
        if (!brand) return;

        const deleted = brand;
        setError(null);
        deleteBrand(deleted.id)
            .then(() => {
                onDelete(deleted);
                onClose();
            })
            .catch(() => setError("Couldn't delete the brand. Try again."));
    };

    return (
        <Dialog open={!!brand} onOpenChange={(open) => !open && onClose()}>
            <DialogContent aria-describedby={undefined}>
                <DialogHeader>
                    <DialogTitle>Edit brand</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-5">
                    <div className="grid gap-2">
                        <Label htmlFor="brand-name">Name</Label>
                        <Input id="brand-name" value={name} onChange={(event) => setName(event.target.value)} />
                    </div>

                    <Combobox
                        label="Category"
                        className="grid gap-2"
                        items={categories}
                        initialSelectedItem={category}
                        onChange={(item) => setCategory(item)}
                        displayInputValue={(item) => item?.name ?? ''}
                    />

                    {error && <p className="text-sm text-red-700 dark:text-red-400">{error}</p>}

                    <DialogFooter className="sm:justify-between">
                        <LongPressButton type="button" onLongPress={handleDelete}>
                            Hold to delete
                        </LongPressButton>
                        <Button type="submit" disabled={!valid || saving}>
                            {saving ? 'Saving…' : 'Save'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
