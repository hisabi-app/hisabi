import { useEffect, useState } from 'react';
import { router, usePage } from '@inertiajs/react';

import CreateTransaction from '@/components/Domain/CreateTransaction';
import SmsParser from '@/components/Global/SmsParser';
import Dock from '@/components/Shell/Dock';
import Sidebar from '@/components/Shell/Sidebar';
import { FALLBACK_NAV, type Navigation } from '@/components/Shell/navigation';
import { getAllBrands } from '@/Api';
import { notifyDataChanged, useAddRequests, type AddKind } from '@/hooks/useDataChanged';

interface AuthenticatedProps {
    auth?: any;
    // Older pages pass a title row; newer ones render a PageHeader themselves
    header?: React.ReactNode;
    children: React.ReactNode;
}

export default function Authenticated({ auth, header, children }: AuthenticatedProps) {
    const { nav } = usePage<{ nav?: Navigation | null }>().props;
    const [adding, setAdding] = useState<AddKind | null>(null);
    const [brands, setBrands] = useState<any[]>([]);
    const user = auth?.user;

    // Pages can open the same dialogs, e.g. Needs you's "Review" for unread SMS
    useAddRequests(setAdding);

    useEffect(() => {
        if (adding !== 'manual') return;

        getAllBrands()
            .then(({ data }) => setBrands(data.allBrands))
            .catch(console.error);
    }, [adding]);

    // Pages refetch their own data; the sidebar counts come with the next props
    const changed = () => {
        notifyDataChanged();
        router.reload({ only: ['nav'] });
    };

    return (
        <div className="min-h-dvh bg-background">
            <Sidebar nav={nav ?? FALLBACK_NAV} user={user} />

            <main className="pb-28 md:pb-10 md:pl-[248px]">
                {header && (
                    <div className="mx-auto flex w-full max-w-7xl flex-wrap items-center gap-3 px-4 pt-6 md:pt-7 [&_h2]:text-[28px] [&_h2]:leading-tight [&_h2]:font-bold [&_h2]:tracking-[-0.028em]">
                        {header}
                    </div>
                )}
                {children}
            </main>

            <Dock nav={nav ?? FALLBACK_NAV} onAdd={setAdding} />

            <SmsParser open={adding === 'sms'} onOpenChange={(open) => !open && setAdding(null)} onParsed={changed} />
            <CreateTransaction showCreate={adding === 'manual'} brands={brands} onCreate={changed} onClose={() => setAdding(null)} />
        </div>
    );
}
