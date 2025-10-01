import { useEffect, useState } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { createCategory } from "../../Api";
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';

export default function Create({ showCreate, onClose, onCreate }) {
    const [name, setName] = useState('');
    const [type, setType] = useState('EXPENSES');
    const [color, setColor] = useState('gray');
    const [isReady, setIsReady] = useState(false);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setIsReady(name !== '');
    }, [name]);

    const handleCreate = () => {
        if (loading || !isReady) return;
        
        setLoading(true);

        createCategory({
            name,
            type,
            color
        })
        .then(({ data }) => {
            onCreate(data.createCategory);
            // Reset form
            setName('');
            setType('EXPENSES');
            setColor('gray');
            setLoading(false);
            onClose();
        })
        .catch(console.error);
    };

    return (
        <Dialog open={showCreate} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogTitle className="sr-only">Create Category</DialogTitle>
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
