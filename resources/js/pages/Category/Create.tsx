import { useEffect, useState } from "react";

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { createCategory } from "../../Api";
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { getCategoryIcon } from '@/Utils/categoryIcons';
import { IconColorSelector } from '@/components/ui/icon-color-selector';
import { PencilSimple } from '@phosphor-icons/react';

export default function Create({ showCreate, onClose, onCreate }) {
    const [name, setName] = useState('');
    const [type, setType] = useState('EXPENSES');
    const [color, setColor] = useState('gray');
    const [icon, setIcon] = useState('wallet');
    const [isReady, setIsReady] = useState(false);
    const [loading, setLoading] = useState(false);
    const [showIconColorSelector, setShowIconColorSelector] = useState(false);

    useEffect(() => {
        setIsReady(name !== '');
    }, [name]);

    const handleCreate = () => {
        if (loading || !isReady) return;
        
        setLoading(true);

        createCategory({
            name,
            type,
            color,
            icon
        })
        .then(({ data }) => {
            onCreate(data.createCategory);
            // Reset form
            setName('');
            setType('EXPENSES');
            setColor('gray');
            setIcon('wallet');
            setLoading(false);
            onClose();
        })
        .catch(console.error);
    };

    return (
        <>
            <Dialog open={showCreate} onOpenChange={(open) => !open && onClose()}>
                <DialogContent>
                    <DialogTitle className="sr-only">Create Category</DialogTitle>
                    <div className="space-y-4">
                        <div>
                            <Label htmlFor="name">
                                Name
                            </Label>
                            <div className="relative mt-1">
                                <div className="absolute left-3 top-1/2 transform -translate-y-1/2 z-10">
                                    <div 
                                        className={`group size-8 rounded-full flex items-center justify-center badge badge-${color} cursor-pointer transition-all hover:ring-2 hover:ring-primary/50`}
                                        onClick={() => setShowIconColorSelector(true)}
                                    >
                                        {(() => {
                                            const IconComponent = getCategoryIcon(icon);
                                            return <IconComponent size={16} weight="regular" className="text-current" />;
                                        })()}
                                        <div className="absolute -top-1 -right-1 opacity-0 group-hover:opacity-100 transition-opacity bg-primary text-primary-foreground rounded-full p-1">
                                            <PencilSimple size={10} weight="bold" />
                                        </div>
                                    </div>
                                </div>
                                <Input
                                    type="text"
                                    name="name"
                                    value={name}
                                    className="pl-14"
                                    placeholder="Category name"
                                    onChange={(e) => setName(e.target.value)}
                                />
                            </div>
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

            <IconColorSelector
                open={showIconColorSelector}
                onOpenChange={setShowIconColorSelector}
                selectedIcon={icon}
                selectedColor={color}
                onIconChange={setIcon}
                onColorChange={setColor}
                onSave={() => {}}
            />
        </>
    );
}
