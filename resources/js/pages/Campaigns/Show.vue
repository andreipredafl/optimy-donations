<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Campaign } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Calendar, Clock, DollarSign, Heart, Share2, Target, User } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    campaign: Campaign;
}>();

const getImageUrl = (featuredImage: string | null): string => {
    return featuredImage || 'https://placehold.co/800x400?text=Campaign+Image';
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

const formatAmount = (cents: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'EUR',
    }).format(cents / 100);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatRelativeDate = (date: string): string => {
    const now = new Date();
    const targetDate = new Date(date);
    const diffTime = targetDate.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return 'Campaign ended';
    } else if (diffDays === 0) {
        return 'Ends today';
    } else if (diffDays === 1) {
        return '1 day left';
    } else {
        return `${diffDays} days left`;
    }
};

const daysLeft = computed(() => {
    if (!props.campaign.end_date) return null;
    return formatRelativeDate(props.campaign.end_date);
});

const isActive = computed(() => props.campaign.status === 'active');

const calculateProgress = computed(() => {
    if (!props.campaign.goal_amount_cents || props.campaign.goal_amount_cents === 0) return 0;
    return (props.campaign.current_amount_cents / props.campaign.goal_amount_cents) * 100;
});

const navigateToDonate = () => {
    router.visit(`/campaigns/${props.campaign.id}/donate`);
};

const shareCampaign = () => {
    if (navigator.share) {
        navigator.share({
            title: props.campaign.title,
            text: props.campaign.description,
            url: window.location.href,
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Campaign URL copied to clipboard!');
    }
};
</script>

<template>
    <Head :title="campaign.title" />
    <AppLayout>
        <div class="mx-auto flex h-full max-w-6xl flex-1 flex-col gap-6 p-4">
            <!-- Campaign Header -->
            <div class="relative">
                <img :src="getImageUrl(campaign.featured_image_url)" :alt="campaign.title" class="h-64 w-full rounded-lg object-cover md:h-96" />
                <div class="absolute top-4 right-4 flex gap-2">
                    <span
                        :class="`inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold ${getStatusClasses(campaign.status)}`"
                    >
                        {{ campaign.status }}
                    </span>
                    <Button variant="outline" size="sm" @click="shareCampaign">
                        <Share2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Campaign Info -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="space-y-2">
                                    <CardTitle class="text-2xl md:text-3xl">{{ campaign.title }}</CardTitle>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <User class="h-4 w-4" />
                                            <span>by {{ campaign.creator.name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Target class="h-4 w-4" />
                                            <span>{{ campaign.category.name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-4 w-4" />
                                            <span>Created {{ formatDate(campaign.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="prose max-w-none">
                                <p class="text-lg leading-relaxed whitespace-pre-line">{{ campaign.description }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Donations -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Donations</CardTitle>
                            <CardDescription>Latest supporters of this campaign</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="campaign.donations && campaign.donations.length > 0" class="space-y-4">
                                <div
                                    v-for="donation in campaign.donations.slice(0, 10)"
                                    :key="donation.id"
                                    class="flex items-start justify-between border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
                                >
                                    <div class="flex items-start gap-3">
                                        <Avatar class="h-10 w-10">
                                            <AvatarImage
                                                :src="`https://ui-avatars.com/api/?name=${donation.donor_name || 'Anonymous'}&background=random`"
                                            />
                                            <AvatarFallback>
                                                {{ donation.anonymous ? 'A' : (donation.donor_name || 'Anonymous').charAt(0) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <p class="font-medium">
                                                {{ donation.anonymous ? 'Anonymous' : donation.donor_name || 'Anonymous' }}
                                            </p>
                                            <p v-if="donation.message" class="mt-1 text-sm text-muted-foreground">"{{ donation.message }}"</p>
                                            <p class="mt-1 text-xs text-muted-foreground">
                                                {{ formatDate(donation.created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-green-600">
                                            {{ formatAmount(donation.amount_cents) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="py-8 text-center text-muted-foreground">
                                <Heart class="mx-auto mb-4 h-12 w-12 opacity-50" />
                                <p>No donations yet. Be the first to support this campaign!</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Donation Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <DollarSign class="h-5 w-5" />
                                Support This Campaign
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Progress -->
                            <div class="space-y-3">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-2xl font-bold">{{ formatAmount(campaign.current_amount_cents) }}</span>
                                    <span class="text-sm text-muted-foreground"> of {{ formatAmount(campaign.goal_amount_cents) }} </span>
                                </div>
                                <Progress :value="calculateProgress" class="h-3" />
                                <div class="text-sm text-muted-foreground">{{ calculateProgress.toFixed(1) }}% funded</div>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-4 border-t border-b py-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold">{{ campaign.donations_count || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">donations</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold">{{ campaign.donors_count || 0 }}</div>
                                    <div class="text-sm text-muted-foreground">donors</div>
                                </div>
                            </div>

                            <!-- Time left -->
                            <div v-if="daysLeft && isActive" class="flex items-center gap-2 text-sm">
                                <Clock class="h-4 w-4" />
                                <span>{{ daysLeft }}</span>
                            </div>

                            <!-- Donation Button -->
                            <Button v-if="isActive" @click="navigateToDonate" class="w-full" size="lg">
                                <Heart class="mr-2 h-4 w-4" />
                                Donate Now
                            </Button>
                            <div v-else class="py-4 text-center text-muted-foreground">
                                <p>This campaign is no longer accepting donations.</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Creator Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Campaign Creator</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-start gap-3">
                                <Avatar class="h-12 w-12">
                                    <AvatarImage :src="campaign.creator.avatar" />
                                    <AvatarFallback>
                                        {{ campaign.creator.name.charAt(0) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p class="font-medium">{{ campaign.creator.name }}</p>
                                    <p class="text-sm text-muted-foreground">Campaign Organizer</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
