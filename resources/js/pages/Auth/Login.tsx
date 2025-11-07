import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import Guest from '@/Layouts/Guest';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';

const DEMO_EMAIL = 'demo@hisabi.app';
const DEMO_PASSWORD = 'demo123';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: DEMO_EMAIL,
        password: DEMO_PASSWORD
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <Guest>
            <Head title="Log in" />

            <Alert className="mb-4 border-yellow-500 bg-yellow-50 dark:bg-yellow-950 dark:border-yellow-800">
                <AlertDescription>
                    <strong className="font-semibold">Demo Account:</strong> This is a demonstration account with sample data.
                    Data will be automatically refreshed every hour. You cannot transactions in demo mode. Please be respectful to other users.
                </AlertDescription>
            </Alert>

            <Card>
                <CardHeader className="text-center">
                    <CardTitle className="text-xl">Welcome back</CardTitle>
                    <CardDescription className="text-sm">Demo login credentials are pre-filled</CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit}>
                        <div className="grid gap-6">
                            <div className="grid gap-6">
                                <div className="grid gap-3">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        readOnly
                                        className="bg-gray-50 dark:bg-gray-900 cursor-not-allowed"
                                        name="email"
                                    />
                                    {errors.email && <p className="text-destructive text-sm">{errors.email}</p>}
                                </div>
                                <div className="grid gap-3">
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        readOnly
                                        className="bg-gray-50 dark:bg-gray-900 cursor-not-allowed"
                                        name="password"
                                    />
                                </div>
                                <Button type="submit" disabled={processing} className="w-full">
                                    Login to Demo
                                </Button>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </Guest>
    );
}
