import { cn } from '@/lib/utils';

// The Hisabi logo: the wordmark with three green bars (public/images/logo.svg)
export default function Logo({ className }: { className?: string }) {
    return <img src="/images/logo.svg" alt="hisabi" className={cn('block h-10 w-auto', className)} />;
}
