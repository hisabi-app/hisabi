import { useState, useEffect } from "react";
import { Button } from '@/components/ui/button';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogTitle,
    DialogHeader,
} from '@/components/ui/dialog';
import { availableIcons, getCategoryIcon } from '@/Utils/categoryIcons';

interface IconColorSelectorProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    selectedIcon: string;
    selectedColor: string;
    onIconChange: (icon: string) => void;
    onColorChange: (color: string) => void;
    onSave: () => void;
}

export function IconColorSelector({
    open,
    onOpenChange,
    selectedIcon,
    selectedColor,
    onIconChange,
    onColorChange,
    onSave,
}: IconColorSelectorProps) {
    const [tempIcon, setTempIcon] = useState(selectedIcon);
    const [tempColor, setTempColor] = useState(selectedColor);

    // Update temp values when props change or dialog opens
    useEffect(() => {
        if (open) {
            setTempIcon(selectedIcon);
            setTempColor(selectedColor);
        }
    }, [open, selectedIcon, selectedColor]);

    const handleSave = () => {
        onIconChange(tempIcon);
        onColorChange(tempColor);
        onSave();
        onOpenChange(false);
    };

    const handleCancel = () => {
        setTempIcon(selectedIcon);
        setTempColor(selectedColor);
        onOpenChange(false);
    };

    return (
        <Dialog open={open} onOpenChange={(open) => !open && handleCancel()}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Select Icon & Color</DialogTitle>
                </DialogHeader>
                <div className="space-y-6">
                    {/* Icon Preview */}
                    <div className="flex justify-center">
                        <div className={`size-16 rounded-full flex items-center justify-center badge badge-${tempColor}`}>
                            {(() => {
                                const IconComponent = getCategoryIcon(tempIcon);
                                return <IconComponent size={32} weight="regular" className="text-current" />;
                            })()}
                        </div>
                    </div>

                    {/* Icon Selection */}
                    <div className="space-y-3">
                        <Label className="text-sm font-medium">
                            Icon
                        </Label>
                        <div className="grid grid-cols-8 gap-2 max-h-48 overflow-y-auto p-3 border rounded-lg">
                            {availableIcons.map((iconOption) => {
                                const IconComponent = iconOption.component;
                                return (
                                    <button
                                        key={iconOption.name}
                                        type="button"
                                        onClick={() => setTempIcon(iconOption.name)}
                                        className={`p-2 rounded-md hover:bg-accent transition-colors ${
                                            tempIcon === iconOption.name ? 'bg-accent ring-2 ring-primary' : ''
                                        }`}
                                        title={iconOption.label}
                                    >
                                        <IconComponent size={20} weight={tempIcon === iconOption.name ? 'fill' : 'regular'} />
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Color Selection */}
                    <div className="space-y-3">
                        <Label className="text-sm font-medium">
                            Color
                        </Label>
                        <RadioGroup value={tempColor} onValueChange={setTempColor} className="flex gap-2 justify-center flex-wrap">
                            <RadioGroupItem
                                value="red"
                                aria-label="Red"
                                className="size-8 border-red-500 bg-red-500 shadow-none data-[state=checked]:border-red-500 data-[state=checked]:bg-red-500 data-[state=checked]:ring-2 data-[state=checked]:ring-red-300"
                            />
                            <RadioGroupItem
                                value="blue"
                                aria-label="Blue"
                                className="size-8 border-blue-500 bg-blue-500 shadow-none data-[state=checked]:border-blue-500 data-[state=checked]:bg-blue-500 data-[state=checked]:ring-2 data-[state=checked]:ring-blue-300"
                            />
                            <RadioGroupItem
                                value="green"
                                aria-label="Green"
                                className="size-8 border-green-500 bg-green-500 shadow-none data-[state=checked]:border-green-500 data-[state=checked]:bg-green-500 data-[state=checked]:ring-2 data-[state=checked]:ring-green-300"
                            />
                            <RadioGroupItem
                                value="orange"
                                aria-label="Orange"
                                className="size-8 border-orange-500 bg-orange-500 shadow-none data-[state=checked]:border-orange-500 data-[state=checked]:bg-orange-500 data-[state=checked]:ring-2 data-[state=checked]:ring-orange-300"
                            />
                            <RadioGroupItem
                                value="purple"
                                aria-label="Purple"
                                className="size-8 border-purple-500 bg-purple-500 shadow-none data-[state=checked]:border-purple-500 data-[state=checked]:bg-purple-500 data-[state=checked]:ring-2 data-[state=checked]:ring-purple-300"
                            />
                            <RadioGroupItem
                                value="pink"
                                aria-label="Pink"
                                className="size-8 border-pink-500 bg-pink-500 shadow-none data-[state=checked]:border-pink-500 data-[state=checked]:bg-pink-500 data-[state=checked]:ring-2 data-[state=checked]:ring-pink-300"
                            />
                            <RadioGroupItem
                                value="indigo"
                                aria-label="Indigo"
                                className="size-8 border-indigo-500 bg-indigo-500 shadow-none data-[state=checked]:border-indigo-500 data-[state=checked]:bg-indigo-500 data-[state=checked]:ring-2 data-[state=checked]:ring-indigo-300"
                            />
                            <RadioGroupItem
                                value="gray"
                                aria-label="Gray"
                                className="size-8 border-gray-500 bg-gray-500 shadow-none data-[state=checked]:border-gray-500 data-[state=checked]:bg-gray-500 data-[state=checked]:ring-2 data-[state=checked]:ring-gray-300"
                            />
                        </RadioGroup>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex items-center justify-end gap-2 pt-2">
                        <Button variant="outline" onClick={handleCancel}>
                            Cancel
                        </Button>
                        <Button onClick={handleSave}>
                            Save
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}