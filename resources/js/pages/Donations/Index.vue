<script setup lang="ts">
import AppPagination from '@/components/AppPagination.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/utils';
import type { BreadcrumbItem, Donation, PaginatedResponse, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight, DollarSign, Euro, Heart, TrendingUp } from 'lucide-vue-next';

interface Props {
    donations?: PaginatedResponse<Donation>;
    sentDonations?: Donation[];
    receivedDonations?: Donation[];
    view_type: 'admin' | 'user';
    auth: {
        user: User;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Donations',
        href: '/donations',
    },
];

const formatAmount = (amountCents: number): string => {
    return `€${(amountCents / 100).toFixed(2)}`;
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'failed':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'refunded':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const calculateTotalAmount = (donations: Donation[]): number => {
    return donations.reduce((total, donation) => total + donation.amount_cents, 0);
};
</script>

<template>
    <Head title="Donations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Donations</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ view_type === 'admin' ? 'Manage all donations in the system' : 'View your donation activity' }}
                    </p>
                </div>
            </div>

            <!-- Admin View -->
            <div v-if="view_type === 'admin'" class="space-y-6">
                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Donations</CardTitle>
                            <Heart class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ donations?.total || 0 }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Amount</CardTitle>
                            <Euro class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatAmount(calculateTotalAmount(donations?.data || [])) }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Active Donations</CardTitle>
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ donations?.data.filter((d) => d.status === 'completed').length || 0 }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Donations Table -->
                <Card v-if="donations && donations.data.length > 0" class="overflow-hidden">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Donor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                            Campaign
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border bg-background">
                                    <tr v-for="donation in donations.data" :key="donation.id" class="transition-colors hover:bg-muted/50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <Avatar class="h-10 w-10 flex-shrink-0">
                                                    <AvatarFallback class="bg-primary text-primary-foreground">
                                                        {{ donation.is_anonymous ? 'A' : getInitials(donation.user?.name || 'Unknown') }}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-foreground">
                                                        {{ donation.is_anonymous ? 'Anonymous Donor' : donation.user?.name || 'Unknown' }}
                                                    </div>
                                                    <div class="text-sm text-muted-foreground">
                                                        {{ donation.user?.email || 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-foreground">
                                                {{ donation.campaign?.title || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-muted-foreground">by {{ donation.campaign?.creator?.name || 'Unknown' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-green-600">
                                                {{ formatAmount(donation.amount_cents) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Badge :class="getStatusColor(donation.status)" class="text-xs">
                                                {{ donation.status }}
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">
                                            {{ formatDate(donation.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pagination -->
                <AppPagination
                    v-if="donations && donations.last_page > 1"
                    :links="donations.links"
                    :current-page="donations.current_page"
                    :last-page="donations.last_page"
                >
                    Showing {{ donations.from }} to {{ donations.to }} of {{ donations.total }} donations
                </AppPagination>

                <!-- Empty State -->
                <Card v-if="!donations || donations.data.length === 0" class="flex items-center justify-center py-16">
                    <div class="text-center">
                        <Heart class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                        <h3 class="mb-2 text-lg font-medium text-foreground">No donations found</h3>
                        <p class="text-muted-foreground">There are no donations in the system yet.</p>
                    </div>
                </Card>
            </div>

            <!-- User View -->
            <div v-if="view_type === 'user'" class="space-y-6">
                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Sent</CardTitle>
                            <ArrowUpRight class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ sentDonations?.length || 0 }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Sent Amount</CardTitle>
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatAmount(calculateTotalAmount(sentDonations || [])) }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Received</CardTitle>
                            <ArrowDownLeft class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ receivedDonations?.length || 0 }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Received Amount</CardTitle>
                            <Euro class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatAmount(calculateTotalAmount(receivedDonations || [])) }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sent Donations Section -->
                <div class="space-y-4">
                    <Card v-if="sentDonations && sentDonations.length > 0" class="overflow-hidden">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ArrowUpRight class="h-5 w-5" />
                                Your Donations
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead class="bg-muted/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Campaign
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Amount
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border bg-background">
                                        <tr v-for="donation in sentDonations" :key="donation.id" class="transition-colors hover:bg-muted/50">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-foreground">
                                                    {{ donation.campaign?.title }}
                                                </div>
                                                <div class="text-sm text-muted-foreground">by {{ donation.campaign?.creator?.name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-green-600">
                                                    {{ formatAmount(donation.amount_cents) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">
                                                {{ formatDate(donation.created_at) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <Badge :class="getStatusColor(donation.status)" class="text-xs">
                                                    {{ donation.status }}
                                                </Badge>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                        <div class="text-center">LEFT TO DO: PAGINATION</div>
                    </Card>

                    <!-- Empty state for sent donations -->
                    <Card v-if="!sentDonations || sentDonations.length === 0" class="flex items-center justify-center py-16">
                        <div class="text-center">
                            <ArrowUpRight class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <h3 class="mb-2 text-lg font-medium text-foreground">No donations sent</h3>
                            <p class="text-muted-foreground">You haven't made any donations yet.</p>
                        </div>
                    </Card>
                </div>

                <!-- Received Donations Section -->
                <div class="space-y-4">
                    <Card v-if="receivedDonations && receivedDonations.length > 0" class="overflow-hidden">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ArrowDownLeft class="h-5 w-5" />
                                Donations to Your Campaigns
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead class="bg-muted/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Donor
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Campaign
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Amount
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                                Date
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border bg-background">
                                        <tr v-for="donation in receivedDonations" :key="donation.id" class="transition-colors hover:bg-muted/50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <Avatar class="h-8 w-8 flex-shrink-0">
                                                        <AvatarFallback class="bg-primary text-primary-foreground">
                                                            {{ donation.is_anonymous ? 'A' : getInitials(donation.user?.name || 'U') }}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-foreground">
                                                            {{ donation.is_anonymous ? 'Anonymous Donor' : donation.user?.name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-foreground">
                                                    {{ donation.campaign?.title }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-green-600">
                                                    {{ formatAmount(donation.amount_cents) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">
                                                {{ formatDate(donation.created_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                        <div class="text-center">LEFT TO DO: PAGINATION</div>
                    </Card>

                    <!-- Empty state for received donations -->
                    <Card v-if="!receivedDonations || receivedDonations.length === 0" class="flex items-center justify-center py-16">
                        <div class="text-center">
                            <ArrowDownLeft class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <h3 class="mb-2 text-lg font-medium text-foreground">No donations received</h3>
                            <p class="text-muted-foreground">Your campaigns haven't received any donations yet.</p>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
