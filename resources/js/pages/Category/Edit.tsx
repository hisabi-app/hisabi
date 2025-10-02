import { useEffect, useState } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { LongPressButton } from '@/components/ui/long-press-button';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { updateCategory, deleteResource } from "../../Api";
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { availableIcons, getCategoryIcon } from '@/Utils/categoryIcons';

export default function Edit({ category, onUpdate, onDelete, onClose }) {
    const [name, setName] = useState('');
    const [type, setType] = useState('');
    const [color, setColor] = useState('gray');
    const [icon, setIcon] = useState('wallet');

    useEffect(() => {
        if (!category) return;

        setName(category.name);
        setType(category.type);
        setColor(category.color);
        setIcon(category.icon || 'wallet');
    }, [category]);

    const handleUpdate = () => {
        if (!category) return;

        const categoryId = category.id;
        updateCategory({
            id: categoryId,
            name,
            type,
            color,
            icon
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
                            <Tabs value={type} onValueChange={setType} className="mt-1">
                                <TabsList className="grid w-full grid-cols-4">
                                    <TabsTrigger value="EXPENSES">Expenses</TabsTrigger>
                                    <TabsTrigger value="INCOME">Income</TabsTrigger>
                                    <TabsTrigger value="SAVINGS">Savings</TabsTrigger>
                                    <TabsTrigger value="INVESTMENT">Investment</TabsTrigger>
                                </TabsList>
                            </Tabs>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-sm leading-none font-medium">
                                Icon
                            </Label>
                            <div className="grid grid-cols-8 gap-2 max-h-48 overflow-y-auto p-2 border rounded-lg">
                                {availableIcons.map((iconOption) => {
                                    const IconComponent = iconOption.component;
                                    return (
                                        <Button
                                            key={iconOption.name}
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => setIcon(iconOption.name)}
                                            className={`px-0 ${icon === iconOption.name ? 'ring-1' : ''
                                                }`}
                                            title={iconOption.label}
                                        >
                                            <IconComponent />
                                        </Button>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-sm leading-none font-medium">
                                Color
                            </Label>
                            <RadioGroup value={color} onValueChange={setColor} className="flex gap-1.5">
                                <RadioGroupItem
                                    value="red"
                                    aria-label="Red"
                                    className="size-6 border-red-500 bg-red-500 shadow-none data-[state=checked]:border-red-500 data-[state=checked]:bg-red-500"
                                />
                                <RadioGroupItem
                                    value="blue"
                                    aria-label="Blue"
                                    className="size-6 border-blue-500 bg-blue-500 shadow-none data-[state=checked]:border-blue-500 data-[state=checked]:bg-blue-500"
                                />
                                <RadioGroupItem
                                    value="green"
                                    aria-label="Green"
                                    className="size-6 border-green-500 bg-green-500 shadow-none data-[state=checked]:border-green-500 data-[state=checked]:bg-green-500"
                                />
                                <RadioGroupItem
                                    value="orange"
                                    aria-label="Orange"
                                    className="size-6 border-orange-500 bg-orange-500 shadow-none data-[state=checked]:border-orange-500 data-[state=checked]:bg-orange-500"
                                />
                                <RadioGroupItem
                                    value="purple"
                                    aria-label="Purple"
                                    className="size-6 border-purple-500 bg-purple-500 shadow-none data-[state=checked]:border-purple-500 data-[state=checked]:bg-purple-500"
                                />
                                <RadioGroupItem
                                    value="pink"
                                    aria-label="Pink"
                                    className="size-6 border-pink-500 bg-pink-500 shadow-none data-[state=checked]:border-pink-500 data-[state=checked]:bg-pink-500"
                                />
                                <RadioGroupItem
                                    value="indigo"
                                    aria-label="Indigo"
                                    className="size-6 border-indigo-500 bg-indigo-500 shadow-none data-[state=checked]:border-indigo-500 data-[state=checked]:bg-indigo-500"
                                />
                                <RadioGroupItem
                                    value="gray"
                                    aria-label="Gray"
                                    className="size-6 border-gray-500 bg-gray-500 shadow-none data-[state=checked]:border-gray-500 data-[state=checked]:bg-gray-500"
                                />
                            </RadioGroup>
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
