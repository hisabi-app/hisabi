import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { UserCircleIcon } from "@phosphor-icons/react";

import Authenticated from '@/Layouts/Authenticated';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { updateUserProfile } from '@/Api/user';

interface User {
    id: number;
    name: string;
    email: string;
}

export default function Index({ auth }: { auth: { user: User } }) {
    const [name, setName] = useState(auth.user.name);
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');

    const handleSave = () => {
        setError('');
        setMessage('');

        // Validate password match
        if (password && password !== confirmPassword) {
            setError('Passwords do not match');
            return;
        }

        if (loading) return;
        setLoading(true);

        const updateData: { name: string; password?: string } = { name };
        if (password) {
            updateData.password = password;
        }

        updateUserProfile(updateData)
            .then(({ data }) => {
                setMessage('Profile updated successfully');
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

    const isFormValid = name.trim() !== '' && (!password || (password === confirmPassword && password.length >= 8));

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Settings</h2>
        </div>
    );

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Settings" />

            <div className="p-4">
                <div className="max-w-4xl mx-auto">
                    <Tabs defaultValue="account" className="w-full">
                        <TabsList className="grid w-full max-w-md grid-cols-1">
                            <TabsTrigger value="account" className="flex items-center gap-2">
                                <UserCircleIcon size={20} />
                                Account
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="account" className="mt-4">
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
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={auth.user.email}
                                            disabled
                                            className="bg-gray-50"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Email cannot be changed
                                        </p>
                                    </div>

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

                                    <div className="border-t pt-4 mt-4">
                                        <h3 className="text-sm font-semibold mb-3">Change Password</h3>
                                        <div className="space-y-4">
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
                                                <Label htmlFor="confirmPassword">Confirm Password</Label>
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
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </Authenticated>
    );
}
