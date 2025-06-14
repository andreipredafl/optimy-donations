<script setup lang="ts">
import AppEmptyState from '@/components/AppEmptyState.vue';
import AppPagination from '@/components/AppPagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Progress } from '@/components/ui/progress/';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Campaign, Category, PaginatedResponse } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Calendar, Heart, Pencil, Search, Target, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    campaigns: PaginatedResponse<Campaign>;
    categories: Category[];
    filters: {
        search?: string;
        category?: string;
    };
    auth: {
        user: {
            id: number;
        } | null;
    };
}>();

const page = usePage();
const can = computed(() => page.props.can ?? {});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

const applyFilters = () => {
    router.get(
        '/campaigns',
        {
            search: search.value,
            category: selectedCategory.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const getImageUrl = (featuredImage: string | null): string => {
    return featuredImage || 'https://placehold.co/600x400?text=Campaign+Image';
};

const getStatusClasses = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'completed':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'cancelled':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const calculateProgress = (current: number, goal: number): number => {
    if (goal === 0) return 0;
    return Math.min((current / goal) * 100, 100);
};

const formatAmount = (cents: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(cents / 100);
};

const formatDate = (date: string | null): string => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Campaigns" />
    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between">
                <div class="flex flex-col space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">Campaigns</h1>
                    <p class="text-muted-foreground">Discover and support meaningful campaigns in your community.</p>
                </div>
                <Link href="/campaigns/create" v-if="can['campaigns.create']">
                    <Button class="ml-4"> Create new campaign</Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex w-100 items-center gap-2">
                    <div class="relative flex-1">
                        <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" type="search" placeholder="Search campaigns..." class="pl-8" @keyup.enter="applyFilters" />
                    </div>
                    <Button @click="applyFilters" class="gap-2">
                        <Search class="h-4 w-4" />
                        Filter
                    </Button>
                </div>

                <Select v-model="selectedCategory" @update:model-value="applyFilters">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="All Categories" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="null">All Categories</SelectItem>
                        <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                            {{ category.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <template v-if="campaigns.data && campaigns.data.length">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
                    <Card v-for="campaign in campaigns.data" :key="campaign.id" class="overflow-hidden">
                        <div class="relative aspect-video w-full overflow-hidden">
                            <img
                                :src="getImageUrl(campaign.featured_image_url ?? null)"
                                :alt="campaign.title"
                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                            />
                            <div class="absolute top-2 right-2 flex gap-2">
                                <span
                                    v-if="auth.user && campaign.creator_id === auth.user.id"
                                    class="inline-flex items-center rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800"
                                >
                                    your campaign
                                </span>
                                <span
                                    :class="`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors ${getStatusClasses(campaign.status)}`"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>
                        </div>

                        <CardHeader class="pb-3">
                            <CardTitle class="line-clamp-2">{{ campaign.title }}</CardTitle>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Users class="h-4 w-4" />
                                <span>{{ campaign.donors_count }} donors</span>
                            </div>
                        </CardHeader>

                        <CardContent class="space-y-3 pb-3">
                            <CardDescription class="line-clamp-2">
                                {{ campaign.description }}
                            </CardDescription>

                            <!-- Progress -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium">{{ formatAmount(campaign.current_amount_cents) }}</span>
                                    <span class="text-muted-foreground">{{ formatAmount(campaign.goal_amount_cents) }}</span>
                                </div>

                                <Progress :model-value="calculateProgress(campaign.current_amount_cents, campaign.goal_amount_cents)" class="h-2" />

                                <div class="text-xs text-muted-foreground">
                                    {{ calculateProgress(campaign.current_amount_cents, campaign.goal_amount_cents).toFixed(1) }}% funded
                                </div>
                            </div>

                            <!-- Campaign info -->
                            <div class="flex items-center justify-between text-xs text-muted-foreground">
                                <div class="flex items-center gap-1">
                                    <Target class="h-3 w-3" />
                                    <span>{{ campaign.donations_count }} donations</span>
                                </div>
                                <div class="flex items-center gap-1" v-if="campaign.end_date">
                                    <Calendar class="h-3 w-3" />
                                    <span>Ends {{ formatDate(campaign.end_date) }}</span>
                                </div>
                            </div>
                        </CardContent>

                        <CardFooter class="flex flex-col gap-2">
                            <Link :href="`/campaigns/${campaign.id}`" class="w-full">
                                <Button class="w-full gap-2">
                                    <Heart class="h-4 w-4" />
                                    Support Campaign
                                </Button>
                            </Link>

                            <Link class="w-full" v-if="auth.user && (campaign.creator_id === auth.user.id || can['campaigns.update'])" :href="'/'">
                                <Button class="w-full gap-2" :disabled="!can['campaigns.update']" variant="outline">
                                    <Pencil class="h-4 w-4" />
                                    Edit Campaign - TODO
                                </Button>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>

                <AppPagination :links="campaigns.links" :current-page="campaigns.current_page" :last-page="campaigns.last_page">
                    Showing {{ campaigns.from }} to {{ campaigns.to }} of {{ campaigns.total }} campaigns
                </AppPagination>
            </template>

            <template v-else>
                <AppEmptyState
                    v-if="!campaigns.data || campaigns.data.length === 0"
                    title="No campaigns available"
                    message="Check back later for new campaigns to support"
                />
            </template>
        </div>
    </AppLayout>
</template>
