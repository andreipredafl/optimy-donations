<script setup lang="ts">
import DonationCard from '@/components/Donation/Card.vue';
import Creator from '@/components/Donation/Creator.vue';
import Modal from '@/components/Donation/Modal.vue';
import Recent from '@/components/Donation/Recent.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatRelativeDate } from '@/lib/utils';
import type { Campaign } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Calendar, Target, User } from 'lucide-vue-next';
import { computed, defineProps, ref } from 'vue';

const props = defineProps<{
    campaign: Campaign;
    payment_driver: string;
    auth: {
        user: {
            id: number;
        } | null;
    };
}>();

const isDialogOpen = ref(false);

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

const daysLeft = computed(() => {
    if (!props.campaign.end_date) return null;
    return formatRelativeDate(props.campaign.end_date);
});

const isActive = computed(() => props.campaign.status === 'active');

const navigateToDonate = () => {
    isDialogOpen.value = true;
};

const onDonationSuccess = () => {
    window.location.reload();
};

const calculateProgress = computed(() => {
    if (!props.campaign.goal_amount_cents || props.campaign.goal_amount_cents === 0) return 0;
    return (props.campaign.current_amount_cents / props.campaign.goal_amount_cents) * 100;
});
</script>

<template>
    <Head :title="campaign.title" />
    <AppLayout>
        <div class="mx-auto flex h-full max-w-6xl flex-1 flex-col gap-6 p-4">
            <!-- Campaign Header -->
            <div class="relative">
                <img
                    :src="getImageUrl(campaign.featured_image_url ?? null)"
                    :alt="campaign.title"
                    class="h-64 w-full rounded-lg object-cover md:h-96"
                />
                <div class="absolute top-4 right-4 flex gap-2">
                    <span
                        v-if="auth.user && campaign.creator_id === auth.user.id"
                        class="inline-flex items-center rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800"
                    >
                        your campaign
                    </span>
                    <span
                        :class="`inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold ${getStatusClasses(campaign.status)}`"
                    >
                        {{ campaign.status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
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
                    <Recent :donations="campaign.donations" />
                </div>

                <div class="space-y-6">
                    <!-- Donation Card -->
                    <DonationCard
                        :campaign="campaign"
                        :calculate-progress="calculateProgress"
                        :is-active="isActive"
                        :days-left="daysLeft"
                        :navigate-to-donate="navigateToDonate"
                    />

                    <!-- Creator Info -->
                    <Creator :creator="campaign.creator" />
                </div>
            </div>
        </div>

        <!-- Donation Modal Component -->
        <Modal
            :is-open="isDialogOpen"
            :campaign="{ id: campaign.id, title: campaign.title }"
            :payment-driver="props.payment_driver"
            @update:is-open="isDialogOpen = $event"
            @success="onDonationSuccess"
        />
    </AppLayout>
</template>
