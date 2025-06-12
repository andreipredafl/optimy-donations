<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress/';
import { formatAmount } from '@/lib/utils';
import type { Campaign } from '@/types';
import { Clock, DollarSign, Heart } from 'lucide-vue-next';
import { defineProps } from 'vue';

const props = defineProps<{
    campaign: Campaign;
    calculateProgress: number;
    isActive: boolean;
    daysLeft: string | null;
    navigateToDonate: () => void;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <DollarSign class="h-5 w-5" />
                Support This Campaign
            </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="space-y-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-bold">{{ formatAmount(props.campaign.current_amount_cents) }}</span>
                    <span class="text-sm text-muted-foreground"> of {{ formatAmount(props.campaign.goal_amount_cents) }} </span>
                </div>
                <Progress :model-value="props.calculateProgress" class="h-3" />
                <div class="text-sm text-muted-foreground">{{ props.calculateProgress.toFixed(1) }}% funded</div>
            </div>

            <div class="grid grid-cols-2 gap-4 border-t border-b py-4">
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ props.campaign.donations_count || 0 }}</div>
                    <div class="text-sm text-muted-foreground">donations</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ props.campaign.donors_count || 0 }}</div>
                    <div class="text-sm text-muted-foreground">donors</div>
                </div>
            </div>

            <div v-if="props.daysLeft && props.isActive" class="flex items-center gap-2 text-sm">
                <Clock class="h-4 w-4" />
                <span>{{ props.daysLeft }}</span>
            </div>

            <Button v-if="props.isActive" @click="props.navigateToDonate" class="w-full" size="lg">
                <Heart class="mr-2 h-4 w-4" />
                Donate Now
            </Button>
            <div v-else class="py-4 text-center text-muted-foreground">
                <p>This campaign is no longer accepting donations.</p>
            </div>
        </CardContent>
    </Card>
</template>
