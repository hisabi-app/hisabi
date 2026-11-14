import { useState } from 'react';

import Combobox from '@/components/Global/Combobox';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { createBrand } from '../../Api';

type Category = { id: number; name: string };

type Props = {
    categories: Category[];
    showCreate: boolean;
    onClose: () => void;
    onCreate: (brand: any) => void;
};

export default function Create({ categories, showCreate, onClose, onCreate }: Props) {
    const [name, setName] = useState('');
    const [category, setCategory] = useState<Category | null>(null);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(false);

    const valid = name.trim() !== '' && category !== null;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!category || !valid || saving) return;

        setSaving(true);
        setError(false);
        createBrand({ name: name.trim(), categoryId: category.id })
            .then(({ data }) => {
                setName('');
                setCategory(null);
                onCreate(data.createBrand);
            })
            .catch(() => setError(true))
            .finally(() => setSaving(false));
    };

    return (
        <Dialog open={showCreate} onOpenChange={(open) => !open && onClose()}>
            <DialogContent aria-describedby={undefined}>
                <DialogHeader>
                    <DialogTitle>New brand</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-5">
                    <div className="grid gap-2">
                        <Label htmlFor="brand-name">Name</Label>
                        <Input id="brand-name" autoFocus value={name} placeholder="e.g. Carrefour" onChange={(event) => setName(event.target.value)} />
                    </div>

                    <Combobox
                        label="Category"
                        className="grid gap-2"
                        items={categories}
                        initialSelectedItem={category}
                        onChange={(item) => setCategory(item)}
                        displayInputValue={(item) => item?.name ?? ''}
                    />

                    {error && <p className="text-sm text-red-700 dark:text-red-400">Couldn't create the brand. Try again.</p>}

                    <DialogFooter>
                        <Button type="button" variant="ghost" onClick={onClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={!valid || saving}>
                            {saving ? 'Creating…' : 'Create brand'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
