import { useState } from 'react';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FunnelIcon } from '@phosphor-icons/react';
import { DatePickerWithRange } from '@/components/ui/date-picker-with-range';
import { DateRange } from 'react-day-picker';
import Combobox from '@/components/Global/Combobox';

interface FilterProps {
    brands: any[];
    categories: any[];
    onApply: (filters: any) => void;
    activeFilters: any;
}

export default function TransactionFilters({ brands, categories, onApply, activeFilters }: FilterProps) {
    const [isOpen, setIsOpen] = useState(false);

    const handleBrandChange = (brand: any) => {
        const updatedFilters = { ...activeFilters, brandId: brand ? brand.id : '' };
        onApply(updatedFilters);
    };

    const handleCategoryChange = (category: any) => {
        const updatedFilters = { ...activeFilters, categoryId: category ? category.id : '' };
        onApply(updatedFilters);
    };

    const getActiveFilterCount = () => {
        let count = 0;
        if (activeFilters.brandId) count++;
        if (activeFilters.categoryId) count++;
        if (activeFilters.dateFrom && activeFilters.dateTo) count++;
        return count;
    };

    const filterCount = getActiveFilterCount();

    const handleDateChange = (dateRange: DateRange | undefined) => {
        if (dateRange?.from && dateRange?.to) {
            const updatedFilters = {
                ...activeFilters,
                dateFrom: dateRange.from.toISOString().split('T')[0],
                dateTo: dateRange.to.toISOString().split('T')[0],
            };
            onApply(updatedFilters);
        } else if (!dateRange) {
            const updatedFilters = {
                ...activeFilters,
                dateFrom: '',
                dateTo: '',
            };
            onApply(updatedFilters);
        }
    };

    const getInitialDateRange = (): DateRange | undefined => {
        if (activeFilters.dateFrom && activeFilters.dateTo) {
            return {
                from: new Date(activeFilters.dateFrom),
                to: new Date(activeFilters.dateTo),
            };
        }
        return undefined;
    };

    const getSelectedBrand = () => {
        if (!activeFilters.brandId) return undefined;
        return brands.find((b: any) => b.id == activeFilters.brandId);
    };

    const getSelectedCategory = () => {
        if (!activeFilters.categoryId) return undefined;
        return categories.find((c: any) => c.id == activeFilters.categoryId);
    };

    return (
        <Popover open={isOpen} onOpenChange={setIsOpen}>
            <PopoverTrigger asChild>
                <Button variant="outline" className="gap-2 relative bg-white">
                    <FunnelIcon className="h-4 w-4" />
                    Filters
                    {filterCount > 0 && (
                        <Badge variant="default" className="ml-1 h-5 min-w-5 rounded-full px-1.5 text-xs">
                            {filterCount}
                        </Badge>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-80" align="end">
                <div className="grid gap-4">
                    {/* Brand Filter */}
                    <Combobox
                        label="Brand"
                        items={brands}
                        initialSelectedItem={getSelectedBrand()}
                        onChange={handleBrandChange}
                    />

                    {/* Category Filter */}
                    <Combobox
                        label="Category"
                        items={categories}
                        initialSelectedItem={getSelectedCategory()}
                        onChange={handleCategoryChange}
                    />

                    {/* Date Filter */}
                    <div className="grid gap-2">
                        <DatePickerWithRange
                            onDateChange={handleDateChange}
                            initialDate={getInitialDateRange()}
                        />
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}

