import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { UserCircleIcon, CaretLeftIcon, CaretDownIcon } from "@phosphor-icons/react";

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
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
} from "@/components/ui/sidebar";
import ApplicationLogo from "@/components/Global/ApplicationLogo";
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
    const [loadingProfile, setLoadingProfile] = useState(false);
    const [loadingPassword, setLoadingPassword] = useState(false);
    const [profileMessage, setProfileMessage] = useState('');
    const [passwordMessage, setPasswordMessage] = useState('');
    const [profileError, setProfileError] = useState('');
    const [passwordError, setPasswordError] = useState('');
    const [isProfileOpen, setIsProfileOpen] = useState(true);
    const [isPasswordOpen, setIsPasswordOpen] = useState(false);

    const handleSaveProfile = () => {
        setProfileError('');
        setProfileMessage('');

        if (loadingProfile) return;
        setLoadingProfile(true);

        updateUserProfile({ name, email })
            .then(({ data }) => {
                setProfileMessage('Profile updated successfully');
                setLoadingProfile(false);

                // Update the page after 1 second to reflect the changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch((err) => {
                setProfileError(err.message || 'Failed to update profile');
                setLoadingProfile(false);
            });
    };

    const handleChangePassword = () => {
        setPasswordError('');
        setPasswordMessage('');

        // Validate password match
        if (password !== confirmPassword) {
            setPasswordError('New passwords do not match');
            return;
        }

        // Require current password
        if (!currentPassword) {
            setPasswordError('Current password is required');
            return;
        }

        if (loadingPassword) return;
        setLoadingPassword(true);

        updateUserProfile({ name, email, currentPassword, password })
            .then(({ data }) => {
                setPasswordMessage('Password changed successfully');
                setCurrentPassword('');
                setPassword('');
                setConfirmPassword('');
                setLoadingPassword(false);
            })
            .catch((err) => {
                setPasswordError(err.message || 'Failed to change password');
                setLoadingPassword(false);
            });
    };

    const isProfileValid = name.trim() !== '' && email.trim() !== '';
    const isPasswordValid = currentPassword && password && confirmPassword && password === confirmPassword && password.length >= 8;

    return (
        <>
            <Head title="Settings" />
            <SidebarProvider>
                <Sidebar variant="inset">
                    <SidebarHeader>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <SidebarMenuButton size="lg" asChild>
                                    <Link href={route('dashboard')} className="flex items-center gap-2">
                                        <CaretLeftIcon size={20} />
                                        <ApplicationLogo />
                                    </Link>
                                </SidebarMenuButton>
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
                        <h2 className="text-lg font-semibold">Settings</h2>
                    </header>
                    <main className="flex flex-1 flex-col gap-4 p-4">
                        {activeTab === 'account' && (
                            <div className="max-w-2xl space-y-4">
                                {/* Profile Information Section */}
                                <Collapsible open={isProfileOpen} onOpenChange={setIsProfileOpen}>
                                    <Card>
                                        <CollapsibleTrigger className="w-full">
                                            <CardHeader className="cursor-pointer hover:bg-accent/50 transition-colors">
                                                <div className="flex items-center justify-between">
                                                    <div className="text-left">
                                                        <CardTitle>Profile Information</CardTitle>
                                                        <CardDescription>
                                                            Update your name and email address
                                                        </CardDescription>
                                                    </div>
                                                    <CaretDownIcon
                                                        size={20}
                                                        className={`transition-transform ${isProfileOpen ? 'rotate-180' : ''}`}
                                                    />
                                                </div>
                                            </CardHeader>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <CardContent className="space-y-4">
                                                {profileMessage && (
                                                    <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                                                        {profileMessage}
                                                    </div>
                                                )}

                                                {profileError && (
                                                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                                        {profileError}
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

                                                <div className="pt-2">
                                                    <Button
                                                        onClick={handleSaveProfile}
                                                        disabled={!isProfileValid || loadingProfile}
                                                        className="w-full sm:w-auto"
                                                    >
                                                        {loadingProfile ? 'Saving...' : 'Save Profile'}
                                                    </Button>
                                                </div>
                                            </CardContent>
                                        </CollapsibleContent>
                                    </Card>
                                </Collapsible>

                                {/* Change Password Section */}
                                <Collapsible open={isPasswordOpen} onOpenChange={setIsPasswordOpen}>
                                    <Card>
                                        <CollapsibleTrigger className="w-full">
                                            <CardHeader className="cursor-pointer hover:bg-accent/50 transition-colors">
                                                <div className="flex items-center justify-between">
                                                    <div className="text-left">
                                                        <CardTitle>Change Password</CardTitle>
                                                        <CardDescription>
                                                            Update your password
                                                        </CardDescription>
                                                    </div>
                                                    <CaretDownIcon
                                                        size={20}
                                                        className={`transition-transform ${isPasswordOpen ? 'rotate-180' : ''}`}
                                                    />
                                                </div>
                                            </CardHeader>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <CardContent className="space-y-4">
                                                {passwordMessage && (
                                                    <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                                                        {passwordMessage}
                                                    </div>
                                                )}

                                                {passwordError && (
                                                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                                        {passwordError}
                                                    </div>
                                                )}

                                                <div className="space-y-2">
                                                    <Label htmlFor="currentPassword">Current Password</Label>
                                                    <Input
                                                        id="currentPassword"
                                                        type="password"
                                                        value={currentPassword}
                                                        onChange={(e) => setCurrentPassword(e.target.value)}
                                                        placeholder="Enter current password"
                                                    />
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

                                                <div className="pt-2">
                                                    <Button
                                                        onClick={handleChangePassword}
                                                        disabled={!isPasswordValid || loadingPassword}
                                                        className="w-full sm:w-auto"
                                                    >
                                                        {loadingPassword ? 'Changing...' : 'Change Password'}
                                                    </Button>
                                                </div>
                                            </CardContent>
                                        </CollapsibleContent>
                                    </Card>
                                </Collapsible>
                            </div>
                        )}
                    </main>
                </SidebarInset>
            </SidebarProvider>
        </>
    );
}
