<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MediaVault - Manage Your Home Media Collections</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    {{-- Navigation --}}
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    <span class="ml-2 text-xl font-bold text-gray-900">MediaVault</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#features" class="text-gray-600 hover:text-gray-900 transition">Features</a>
                    <a href="#pricing" class="text-gray-600 hover:text-gray-900 transition">Pricing</a>
                    <a href="/app/login" class="text-gray-600 hover:text-gray-900 transition">Login</a>
                    <a href="/app/register" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
            <div class="text-center">
                <h1 class="text-4xl sm:text-6xl font-bold tracking-tight text-gray-900">
                    Your Media Collection,
                    <span class="text-indigo-600">Organized</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto">
                    Catalog your DVDs, vinyl records, cassettes, and more. Share with family, lend to friends, and never lose track of your collection again.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="/app/register" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Start Free
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="#features" class="text-gray-700 font-semibold hover:text-indigo-600 transition">
                        Learn more &darr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Media Types Banner --}}
    <section class="bg-white border-y border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-8 text-gray-500 text-sm font-medium">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">&#128191;</span> DVDs & Blu-rays
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">&#127926;</span> Vinyl Records
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">&#128191;</span> CDs
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">&#128252;</span> VHS Tapes
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">&#127932;</span> Cassettes
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">&#10024;</span> Custom Types
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Everything you need to manage your collection</h2>
                <p class="mt-4 text-lg text-gray-600">Powerful features designed for media enthusiasts and families.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Catalog Everything</h3>
                    <p class="mt-2 text-gray-600">Track DVDs, Blu-rays, vinyl records, CDs, cassettes, VHS, and any custom media type you can imagine.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Smart Collections</h3>
                    <p class="mt-2 text-gray-600">Organize media into custom collections. Group by genre, decade, favorites, or any way you like.</p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Family Sharing</h3>
                    <p class="mt-2 text-gray-600">Invite family members to your vault. Everyone can browse, check out, and return items with full tracking.</p>
                </div>

                {{-- Feature 4 --}}
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Checkout & Borrowing</h3>
                    <p class="mt-2 text-gray-600">Track who has what with due dates. Connect with other families to borrow and lend media across households.</p>
                </div>

                {{-- Feature 5 --}}
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Auto-Fill Metadata</h3>
                    <p class="mt-2 text-gray-600">Scan barcodes or search Discogs and OMDb to automatically populate titles, artists, directors, cover art, and more.</p>
                </div>

                {{-- Feature 6 --}}
                <div class="p-6 rounded-xl border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Dashboard & Stats</h3>
                    <p class="mt-2 text-gray-600">See your collection at a glance with charts, overdue alerts, recent activity, and pending borrow requests.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Simple, transparent pricing</h2>
                <p class="mt-4 text-lg text-gray-600">Start free. Upgrade when you need more.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Free Plan --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Free</h3>
                    <p class="mt-2 text-gray-500 text-sm">Perfect for getting started</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900">$0</span>
                        <span class="text-gray-500">/month</span>
                    </div>
                    <ul class="mt-8 space-y-3 text-sm text-gray-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Up to 50 media items
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            2 family members
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            3 collections
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Family checkout tracking
                        </li>
                    </ul>
                    <a href="/app/register" class="mt-8 block w-full text-center px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                        Get Started Free
                    </a>
                </div>

                {{-- Basic Plan --}}
                <div class="bg-white rounded-2xl border-2 border-indigo-600 p-8 relative shadow-lg">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                        Most Popular
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Basic</h3>
                    <p class="mt-2 text-gray-500 text-sm">For serious collectors</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900">$5</span>
                        <span class="text-gray-500">/month</span>
                    </div>
                    <ul class="mt-8 space-y-3 text-sm text-gray-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Up to 500 media items
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            5 family members
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            20 collections
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Cross-family sharing
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            10 API lookups/day
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Export to spreadsheet
                        </li>
                    </ul>
                    <a href="/app/register" class="mt-8 block w-full text-center px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        Start Basic Plan
                    </a>
                </div>

                {{-- Premium Plan --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-8">
                    <h3 class="text-lg font-semibold text-gray-900">Premium</h3>
                    <p class="mt-2 text-gray-500 text-sm">For power users & large families</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900">$12</span>
                        <span class="text-gray-500">/month</span>
                    </div>
                    <ul class="mt-8 space-y-3 text-sm text-gray-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Unlimited media items
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            15 family members
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Unlimited collections
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Cross-family sharing
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Unlimited API lookups
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Custom media types
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Export to spreadsheet
                        </li>
                    </ul>
                    <a href="/app/register" class="mt-8 block w-full text-center px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                        Start Premium Plan
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-16 bg-indigo-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white">Ready to organize your collection?</h2>
            <p class="mt-4 text-lg text-indigo-100">Join families who trust MediaVault to catalog, share, and track their media.</p>
            <a href="/app/register" class="mt-8 inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition">
                Get Started for Free
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    <span class="text-white font-semibold">MediaVault</span>
                </div>
                <p class="text-sm">&copy; {{ date('Y') }} MediaVault. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
