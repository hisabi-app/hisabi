import { Head } from '@inertiajs/react';

import Authenticated from '@/Layouts/Authenticated';
import Budgets from '@/components/Domain/Budgets';

export default function Index({ auth }: any) {
    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Budgets</h2>
        </div>
    );

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Budgets" />

            <div className="p-4">
                <div className="max-w-7xl mx-auto grid gap-4">
                    <Budgets />
                </div>
            </div>
        </Authenticated>
    );
}
