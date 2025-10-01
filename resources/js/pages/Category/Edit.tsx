import { useEffect, useState } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { LongPressButton } from '@/components/ui/long-press-button';
import { updateCategory, deleteResource } from "../../Api";
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';

export default function Edit({ category, onUpdate, onDelete, onClose }) {
    const [name, setName] = useState('');
    const [type, setType] = useState('');
    const [color, setColor] = useState('gray');

    useEffect(() => {
        if (!category) return;

        setName(category.name);
        setType(category.type);
        setColor(category.color);
    }, [category]);

    const handleUpdate = () => {
        if (!category) return;

        const categoryId = category.id;
        updateCategory({
            id: categoryId,
            name,
            type,
            color
        })
        .then(({ data }) => {
            onUpdate(data.updateCategory);
            onClose();
        })
        .catch(console.error);
    };

    const handleDelete = () => {
        if (!category) return;

        const categoryToDelete = category;
        deleteResource({ id: categoryToDelete.id, resource: 'Category' })
            .then(() => {
                onDelete(categoryToDelete);
                onClose();
            })
            .catch(console.error);
    };

    return (
        <Dialog open={!!category} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogTitle className="sr-only">Edit Category</DialogTitle>
                {category && (
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
                            <Label htmlFor="type">
                                Type
                            </Label>
                            <select
                                id="type"
                                name="type"
                                value={type}
                                onChange={(e) => setType(e.target.value)}
                                className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="EXPENSES">EXPENSES</option>
                                <option value="INCOME">INCOME</option>
                                <option value="SAVINGS">SAVINGS</option>
                                <option value="INVESTMENT">INVESTMENT</option>
                            </select>
                        </div>

                        <div>
                            <Label htmlFor="color">
                                Color
                            </Label>
                            <select
                                id="color"
                                name="color"
                                value={color}
                                onChange={(e) => setColor(e.target.value)}
                                className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="red">Red</option>
                                <option value="blue">Blue</option>
                                <option value="green">Green</option>
                                <option value="orange">Orange</option>
                                <option value="purple">Purple</option>
                                <option value="pink">Pink</option>
                                <option value="indigo">Indigo</option>
                                <option value="gray">Gray</option>
                            </select>
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
