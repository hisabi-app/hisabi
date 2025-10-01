import { useEffect, useState } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
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
