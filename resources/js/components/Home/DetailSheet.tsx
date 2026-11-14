import { useEffect, useState } from 'react';

import { Sheet, SheetContent } from '@/components/ui/sheet';
import AssignBrands from './AssignBrands';
import BudgetDetail from './BudgetDetail';
import type { Detail, UncategorizedBrand } from './types';

interface DetailSheetProps {
    detail: Detail;
    brands: UncategorizedBrand[];
    onClose: () => void;
}

// Digging deeper: detail slides in from the right while the home stays put behind it
export default function DetailSheet({ detail, brands, onClose }: DetailSheetProps) {
    // Keep the content on screen while the sheet slides out
    const [shown, setShown] = useState<Detail>(detail);

    useEffect(() => {
        if (detail) setShown(detail);
    }, [detail]);

    return (
        <Sheet open={detail !== null} onOpenChange={(open) => !open && onClose()}>
            <SheetContent side="right" aria-describedby={undefined} className="gap-0 overflow-y-auto p-0 sm:max-w-[400px]">
                {shown?.kind === 'budget' && <BudgetDetail id={shown.id} />}
                {shown?.kind === 'brands' && <AssignBrands brands={brands} onClose={onClose} />}
            </SheetContent>
        </Sheet>
    );
}
