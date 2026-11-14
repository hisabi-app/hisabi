import {
    ChartLineUpIcon,
    GearSixIcon,
    HouseIcon,
    ReceiptIcon,
    StorefrontIcon,
    TagIcon,
    WalletIcon,
    type Icon,
} from '@phosphor-icons/react';

export interface Navigation {
    // new: no transactions yet; early: less than a month of them; full: everything unlocked
    stage: 'new' | 'early' | 'full';
    has_budgets: boolean;
    counts: { budgets: number; brands: number };
}

export interface NavItem {
    key: string;
    label: string;
    href: string;
    // Ziggy route name, used to tell which item is current
    route: string;
    icon: Icon;
    count?: keyof Navigation['counts'];
    visible?: (nav: Navigation) => boolean;
}

export const MAIN_ITEMS: NavItem[] = [
    { key: 'home', label: 'Home', href: '/', route: 'home', icon: HouseIcon },
    { key: 'transactions', label: 'Transactions', href: '/transactions', route: 'transactions', icon: ReceiptIcon },
    // Budgets appear with the first transactions, Insights once there's a month to look back on
    { key: 'budgets', label: 'Budgets', href: '/budgets', route: 'budgets', icon: WalletIcon, count: 'budgets', visible: (nav) => nav.stage !== 'new' || nav.has_budgets },
    { key: 'insights', label: 'Insights', href: '/insights', route: 'insights', icon: ChartLineUpIcon, visible: (nav) => nav.stage === 'full' },
];

export const MANAGE_ITEMS: NavItem[] = [
    { key: 'brands', label: 'Brands', href: '/brands', route: 'brands', icon: StorefrontIcon, count: 'brands' },
    { key: 'categories', label: 'Categories', href: '/categories', route: 'categories', icon: TagIcon },
    { key: 'settings', label: 'Settings', href: '/settings', route: 'settings', icon: GearSixIcon },
];

export const FALLBACK_NAV: Navigation = { stage: 'full', has_budgets: true, counts: { budgets: 0, brands: 0 } };

export const isVisible = (item: NavItem, nav: Navigation) => (item.visible ? item.visible(nav) : true);

// Ziggy's global route() helper, injected by @routes
type ZiggyRoute = () => { current: (name: string) => boolean };

export const isCurrent = (item: NavItem) => {
    try {
        return Boolean(((window as any).route as ZiggyRoute)().current(item.route));
    } catch {
        return false;
    }
};

// A newly unlocked section gets a dot until it's opened once (per browser)
const seenKey = (key: string) => `hisabi.seen.${key}`;

export const hasSeen = (key: string): boolean => {
    try {
        return window.localStorage.getItem(seenKey(key)) === '1';
    } catch {
        return true;
    }
};

export const markSeen = (key: string) => {
    try {
        window.localStorage.setItem(seenKey(key), '1');
    } catch {
        // Storage can be unavailable (private mode); the dot just stays
    }
};
