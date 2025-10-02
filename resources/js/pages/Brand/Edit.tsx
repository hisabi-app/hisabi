import { useEffect, useState } from "react";

import { updateBrand, deleteResource } from "../../Api";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { LongPressButton } from '@/components/ui/long-press-button';
import Combobox from "@/components/Global/Combobox";
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';

export default function Edit({ categories, brand, onUpdate, onDelete, onClose }) {
    const [name, setName] = useState('');
    const [category, setCategory] = useState(null);

    useEffect(() => {
        if (!brand) return;

        setName(brand.name);
        if (brand.category) {
            setCategory(brand.category);
        }
    }, [brand]);

    const handleUpdate = () => {
        if (!brand || !category) return;

        const brandId = brand.id;
        updateBrand({
            id: brandId,
            name,
            categoryId: category.id
        })
        .then(({ data }) => {
            onUpdate(data.updateBrand);
            onClose();
        })
        .catch(console.error);
    };

    const handleDelete = () => {
        if (!brand) return;

        const brandToDelete = brand;
        deleteResource({ id: brandToDelete.id, resource: 'Brand' })
            .then(() => {
                onDelete(brandToDelete);
                onClose();
            })
            .catch(console.error);
    };

    const isReady = name !== '' && category !== null;

    return (
        <Dialog open={!!brand} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogTitle className="sr-only">Edit Brand</DialogTitle>
                {brand && (
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

                        <div className="flex items-center justify-end pt-2 gap-2">
                            <LongPressButton onLongPress={handleDelete}>
                                Hold to Delete
                            </LongPressButton>
                            <Button disabled={!isReady} onClick={handleUpdate}>
                                Update
                            </Button>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
