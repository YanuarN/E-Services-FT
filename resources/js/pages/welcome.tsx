import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />
            <main className="flex min-h-screen items-center justify-center bg-slate-50 px-6 text-slate-900">
                <div className="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h1 className="text-2xl font-semibold">E-Services Frontend</h1>
                    <p className="mt-3 text-sm text-slate-600">
                        Starter kit Inertia React aktif dengan file app, main, ssr, dan wayfinder.
                    </p>
                </div>
            </main>
        </>
    );
}
