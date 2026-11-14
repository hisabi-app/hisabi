import { Skeleton } from '@/components/ui/skeleton';

// The home's shape while it loads: soft shimmer where the cards will be
export default function HomeSkeleton() {
    return (
        <div className="grid gap-3.5" aria-busy="true" aria-label="Loading">
            <div className="grid gap-2">
                <Skeleton className="h-3.5 w-20" />
                <Skeleton className="h-8 w-40" />
            </div>
            <div className="rounded-2xl bg-card p-[18px] shadow-card">
                <Skeleton className="h-4 w-24" />
                <Skeleton className="mt-4 h-9 w-full" />
                <Skeleton className="mt-3 h-9 w-full" />
            </div>
            <div className="grid gap-3.5 md:grid-cols-[1.25fr_1fr]">
                <div className="rounded-2xl bg-card p-[18px] shadow-card">
                    <Skeleton className="h-4 w-24" />
                    <Skeleton className="mt-3 h-10 w-44" />
                    <Skeleton className="mt-5 h-20 w-full" />
                </div>
                <div className="rounded-2xl bg-card p-[18px] shadow-card">
                    <Skeleton className="h-4 w-20" />
                    <Skeleton className="mt-4 h-9 w-full" />
                    <Skeleton className="mt-3 h-9 w-full" />
                    <Skeleton className="mt-3 h-9 w-full" />
                </div>
            </div>
        </div>
    );
}
