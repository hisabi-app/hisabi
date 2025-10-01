import { useEffect, useState } from 'react';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import Combobox from "@/components/Global/Combobox";
import { createBrand } from '../../Api';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';

export default function Create({ categories, showCreate, onClose, onCreate }) {
    const [name, setName] = useState('');
    const [category, setCategory] = useState(null);
    const [isReady, setIsReady] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(name !== '' && category !== null);
    }, [name, category]);

    const handleCreate = () => {
        if (loading || !isReady || !category) return;
        
        setLoading(true);

        createBrand({
            name,
            categoryId: category.id
        })
        .then(({ data }) => {
            onCreate(data.createBrand);
            // Reset form
            setCategory(null);
            setName('');
            setLoading(false);
            onClose();
        })
        .catch(console.error);
    };

    return (
        <Dialog open={showCreate} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogTitle className="sr-only">Create Brand</DialogTitle>
                <div className="space-y-4">
                    <div>
                        <Label htmlFor="name">
                            Name
                        </Label>
                        <Input
                            type="text"
                            name="name"
                            value={name}
                            className="mt-1"
                            onChange={(e) => setName(e.target.value)}
                        />
                    </div>

                    <div>
                        <Combobox
                            label="Category"
                            items={categories}
                            initialSelectedItem={category}
                            onChange={(item) => setCategory(item)}
                            displayInputValue={(item) => item?.name ?? ''}
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
