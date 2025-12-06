import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { UserCircleIcon, ArrowLeftIcon } from "@phosphor-icons/react";

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarTrigger,
} from "@/components/ui/sidebar";
import { Separator } from "@/components/ui/separator";
import { updateUserProfile } from '@/Api/user';

interface User {
    id: number;
    name: string;
    email: string;
}

const settingsNavItems = [
  {
    title: "Account",
    value: "account",
    icon: UserCircleIcon,
  },
];

export default function Index({ auth }: { auth: { user: User } }) {
    const [activeTab, setActiveTab] = useState('account');
    const [name, setName] = useState(auth.user.name);
    const [email, setEmail] = useState(auth.user.email);
    const [currentPassword, setCurrentPassword] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');

    const handleSave = () => {
        setError('');
        setMessage('');

        // Validate password match if changing password
        if (password && password !== confirmPassword) {
            setError('New passwords do not match');
            return;
        }

        // If changing password, require current password
        if (password && !currentPassword) {
            setError('Current password is required to change password');
            return;
        }

        if (loading) return;
        setLoading(true);

        const updateData: { name: string; email: string; currentPassword?: string; password?: string } = {
            name,
            email
        };

        if (password) {
            updateData.currentPassword = currentPassword;
            updateData.password = password;
        }

        updateUserProfile(updateData)
            .then(({ data }) => {
                setMessage('Profile updated successfully');
                setCurrentPassword('');
                setPassword('');
                setConfirmPassword('');
                setLoading(false);

                // Update the page after 1 second to reflect the changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch((err) => {
                setError(err.message || 'Failed to update profile');
                setLoading(false);
            });
    };

    const isFormValid = name.trim() !== '' &&
                       email.trim() !== '' &&
                       (!password || (password === confirmPassword && password.length >= 8 && currentPassword));

    return (
        <>
            <Head title="Settings" />
            <SidebarProvider>
                <Sidebar>
                    <SidebarHeader>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <div className="flex items-center gap-2 px-2 py-2">
                                    <h2 className="text-lg font-semibold">Settings</h2>
                                </div>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarHeader>
                    <SidebarContent>
                        <SidebarGroup>
                            <SidebarGroupContent>
                                <SidebarMenu>
                                    {settingsNavItems.map((item) => (
                                        <SidebarMenuItem key={item.value}>
                                            <SidebarMenuButton
                                                onClick={() => setActiveTab(item.value)}
                                                isActive={activeTab === item.value}
                                            >
                                                <item.icon />
                                                <span>{item.title}</span>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>
                                    ))}
                                </SidebarMenu>
                            </SidebarGroupContent>
                        </SidebarGroup>
                    </SidebarContent>
                </Sidebar>
                <SidebarInset>
                    <header className="flex h-16 shrink-0 items-center gap-2 border-b px-4">
                        <div className="flex items-center gap-2">
                            <SidebarTrigger className="-ml-1" />
                            <Separator orientation="vertical" className="mr-2 h-4" />
                            <Link href={route('dashboard')} className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors">
                                <ArrowLeftIcon size={16} />
                                Back to Dashboard
                            </Link>
                        </div>
                    </header>
                    <main className="flex flex-1 flex-col gap-4 p-4">
                        {activeTab === 'account' && (
                            <div className="max-w-2xl">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Account Settings</CardTitle>
                                        <CardDescription>
                                            Update your account information and password
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        {message && (
                                            <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                                                {message}
                                            </div>
                                        )}

                                        {error && (
                                            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                                {error}
                                            </div>
                                        )}

                                        <div className="space-y-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                value={name}
                                                onChange={(e) => setName(e.target.value)}
                                                placeholder="Enter your name"
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email">Email</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                placeholder="Enter your email"
                                            />
                                        </div>

                                        <div className="border-t pt-4 mt-4">
                                            <h3 className="text-sm font-semibold mb-3">Change Password</h3>
                                            <div className="space-y-4">
                                                <div className="space-y-2">
                                                    <Label htmlFor="currentPassword">Current Password</Label>
                                                    <Input
                                                        id="currentPassword"
                                                        type="password"
                                                        value={currentPassword}
                                                        onChange={(e) => setCurrentPassword(e.target.value)}
                                                        placeholder="Enter current password"
                                                    />
                                                    <p className="text-xs text-muted-foreground">
                                                        Required if changing password
                                                    </p>
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor="password">New Password</Label>
                                                    <Input
                                                        id="password"
                                                        type="password"
                                                        value={password}
                                                        onChange={(e) => setPassword(e.target.value)}
                                                        placeholder="Enter new password (min. 8 characters)"
                                                    />
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor="confirmPassword">Confirm New Password</Label>
                                                    <Input
                                                        id="confirmPassword"
                                                        type="password"
                                                        value={confirmPassword}
                                                        onChange={(e) => setConfirmPassword(e.target.value)}
                                                        placeholder="Confirm new password"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div className="pt-4">
                                            <Button
                                                onClick={handleSave}
                                                disabled={!isFormValid || loading}
                                                className="w-full sm:w-auto"
                                            >
                                                {loading ? 'Saving...' : 'Save Changes'}
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        )}
                    </main>
                </SidebarInset>
            </SidebarProvider>
        </>
    );
}
