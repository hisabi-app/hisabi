import Panel from '@/components/Wallet/Panel';
import TransactionRow from './TransactionRow';
import type { Transaction } from './types';

export default function Recent({ transactions, index, onChooseCategory }: { transactions: Transaction[]; index: number; onChooseCategory: () => void }) {
    return (
        <Panel title="Recent" more={{ label: 'All', href: '/transactions' }} index={index}>
            <div className="divide-y">
                {transactions.map((transaction) => (
                    <TransactionRow key={transaction.id} transaction={transaction} onChooseCategory={onChooseCategory} />
                ))}
            </div>
        </Panel>
    );
}
