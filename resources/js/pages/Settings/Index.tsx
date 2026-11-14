import { useEffect, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { CheckIcon, MonitorIcon, SignOutIcon } from '@phosphor-icons/react';

import Authenticated from '@/Layouts/Authenticated';
import PageHeader from '@/components/Wallet/PageHeader';
import Panel from '@/components/Wallet/Panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { updateUserProfile } from '@/Api/user';

interface User {
    id: number;
    name: string;
    email: string;
}

type Message = { kind: 'ok' | 'error'; text: string } | null;

// A short confirmation or error that clears itself after a few seconds
function useMessage(): [Message, (message: Message) => void] {
    const [message, setMessage] = useState<Message>(null);

    useEffect(() => {
        if (!message) return;

        const timer = window.setTimeout(() => setMessage(null), 5000);

        return () => window.clearTimeout(timer);
    }, [message]);

    return [message, setMessage];
}

function Feedback({ message }: { message: Message }) {
    if (!message) return null;

    return message.kind === 'ok' ? (
        <p className="flex items-center gap-1.5 text-[13px] font-semibold text-success" role="status" style={{ animation: 'fade-in 300ms var(--ease-out-quint)' }}>
            <CheckIcon size={14} weight="bold" aria-hidden="true" />
            {message.text}
        </p>
    ) : (
        <p className="text-[13px] text-destructive" role="alert">
            {message.text}
        </p>
    );
}

export default function Index({ auth }: { auth: { user: User } }) {
    const [name, setName] = useState(auth.user.name);
    const [email, setEmail] = useState(auth.user.email);
    const [currentPassword, setCurrentPassword] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [savingProfile, setSavingProfile] = useState(false);
    const [savingPassword, setSavingPassword] = useState(false);
    const [profileMessage, setProfileMessage] = useMessage();
    const [passwordMessage, setPasswordMessage] = useMessage();

    const saveProfile = (event: React.FormEvent) => {
        event.preventDefault();
        if (savingProfile) return;

        setSavingProfile(true);
        updateUserProfile({ name, email, currentPassword: undefined, password: undefined })
            .then(() => setProfileMessage({ kind: 'ok', text: 'Saved' }))
            .catch((error) => setProfileMessage({ kind: 'error', text: error.message || 'Couldn’t save your profile. Try again.' }))
            .finally(() => setSavingProfile(false));
    };

    const changePassword = (event: React.FormEvent) => {
        event.preventDefault();
        if (savingPassword) return;

        if (password !== confirmPassword) {
            setPasswordMessage({ kind: 'error', text: 'The new passwords don’t match.' });
            return;
        }

        setSavingPassword(true);
        updateUserProfile({ name, email, currentPassword, password })
            .then(() => {
                setPasswordMessage({ kind: 'ok', text: 'Password changed' });
                setCurrentPassword('');
                setPassword('');
                setConfirmPassword('');
            })
            .catch((error) => setPasswordMessage({ kind: 'error', text: error.message || 'Couldn’t change your password. Check the current one and try again.' }))
            .finally(() => setSavingPassword(false));
    };

    const profileValid = name.trim() !== '' && email.trim() !== '';
    const passwordValid = currentPassword !== '' && password.length >= 8 && confirmPassword !== '';

    return (
        <Authenticated auth={auth}>
            <Head title="Settings" />

            <div className="mx-auto grid w-full max-w-[720px] gap-3.5 px-4 pt-6 md:px-6 md:pt-7">
                <PageHeader title="Settings" />

                <Panel title="Profile" index={1}>
                    <form onSubmit={saveProfile} className="mt-3 grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="settings-name">Name</Label>
                            <Input id="settings-name" value={name} autoComplete="name" onChange={(event) => setName(event.target.value)} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="settings-email">Email</Label>
                            <Input id="settings-email" type="email" value={email} autoComplete="email" onChange={(event) => setEmail(event.target.value)} />
                        </div>
                        <div className="flex flex-wrap items-center gap-3">
                            <Button type="submit" disabled={!profileValid || savingProfile}>
                                {savingProfile ? 'Saving…' : 'Save'}
                            </Button>
                            <Feedback message={profileMessage} />
                        </div>
                    </form>
                </Panel>

                <Panel title="Password" index={2}>
                    <form onSubmit={changePassword} className="mt-3 grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="settings-current-password">Current password</Label>
                            <Input id="settings-current-password" type="password" value={currentPassword} autoComplete="current-password" onChange={(event) => setCurrentPassword(event.target.value)} />
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="settings-password">New password</Label>
                                <Input id="settings-password" type="password" value={password} autoComplete="new-password" onChange={(event) => setPassword(event.target.value)} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="settings-confirm-password">Repeat the new password</Label>
                                <Input id="settings-confirm-password" type="password" value={confirmPassword} autoComplete="new-password" onChange={(event) => setConfirmPassword(event.target.value)} />
                            </div>
                        </div>
                        <p className="text-[12.5px] text-muted-foreground">At least 8 characters.</p>
                        <div className="flex flex-wrap items-center gap-3">
                            <Button type="submit" disabled={!passwordValid || savingPassword}>
                                {savingPassword ? 'Changing…' : 'Change password'}
                            </Button>
                            <Feedback message={passwordMessage} />
                        </div>
                    </form>
                </Panel>

                <Panel title="Kiosk" index={3}>
                    <div className="flex flex-wrap items-center justify-between gap-3 py-1">
                        <p className="max-w-md text-muted-foreground">A full-screen view of this month and your budgets for a tablet on the wall. It goes dark at night.</p>
                        <Button variant="secondary" asChild>
                            <a href="/kiosk">
                                <MonitorIcon aria-hidden="true" />
                                Open kiosk
                            </a>
                        </Button>
                    </div>
                </Panel>

                <Panel index={4}>
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <p className="text-muted-foreground">
                            Signed in as <b className="font-semibold text-foreground">{auth.user.email}</b>
                        </p>
                        <Button variant="secondary" asChild>
                            <Link href="/logout" method="post" as="button">
                                <SignOutIcon aria-hidden="true" />
                                Log out
                            </Link>
                        </Button>
                    </div>
                </Panel>
            </div>
        </Authenticated>
    );
}
