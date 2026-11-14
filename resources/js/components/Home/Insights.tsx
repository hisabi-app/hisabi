import { Link } from '@inertiajs/react';
import { CaretRightIcon } from '@phosphor-icons/react';

import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';
import { money } from '@/components/Wallet/format';
import { shortDate } from './dates';
import type { Insight } from './types';

function describe(insight: Insight): { title: React.ReactNode; detail: string; href: string } {
    switch (insight.kind) {
        case 'top_brand':
            return {
                title: (
                    <>
                        Most spent at <b className="font-bold">{insight.brand.name}</b>
                    </>
                ),
                detail: `${money(insight.total)} across ${insight.count} purchases`,
                href: `/transactions?brand=${insight.brand.id}`,
            };
        case 'category_up':
            return {
                title: (
                    <>
                        <b className="font-bold">{insight.category?.name}</b> is up on last month
                    </>
                ),
                detail: `${money(insight.difference)} more than this time last month`,
                href: `/transactions?category=${insight.category?.id}`,
            };
        case 'saved':
            return {
                title: (
                    <>
                        You saved <b className="font-bold">{money(insight.total)}</b>
                    </>
                ),
                detail: insight.last_at ? `Last put aside on ${shortDate(insight.last_at)}` : 'Put aside this month',
                href: `/transactions?category=${insight.category?.id}`,
            };
    }
}

export default function Insights({ insights, index }: { insights: Insight[]; index: number }) {
    return (
        <Panel title="Insights" more={{ label: 'All', href: '/insights' }} index={index}>
            <div className="divide-y">
                {insights.map((insight) => {
                    const { title, detail, href } = describe(insight);

                    return (
                        <Link key={insight.kind} href={href} className="group grid grid-cols-[36px_minmax(0,1fr)_14px] items-center gap-x-3 py-2.5">
                            <Tile color={insight.category?.color} icon={insight.category?.icon ?? 'Wallet'} className="row-span-2" />
                            <p className="font-medium">{title}</p>
                            <p className="col-start-2 text-[12.5px] text-muted-foreground font-rounded tabular">{detail}</p>
                            <CaretRightIcon size={14} weight="bold" className="col-start-3 row-span-2 row-start-1 text-muted-foreground transition-transform duration-150 group-hover:translate-x-0.5" aria-hidden="true" />
                        </Link>
                    );
                })}
            </div>
        </Panel>
    );
}
