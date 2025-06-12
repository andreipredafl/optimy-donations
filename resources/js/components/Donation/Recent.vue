<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatAmount, formatDate } from '@/lib/utils';
import type { Donation } from '@/types';
import { Heart } from 'lucide-vue-next';
import { defineProps } from 'vue';

const props = defineProps<{
    donations: Donation[];
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Recent Donations</CardTitle>
            <CardDescription>Latest supporters of this campaign</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="props.donations && props.donations.length > 0" class="space-y-4">
                <div
                    v-for="donation in props.donations.slice(0, 10)"
                    :key="donation.id"
                    class="flex items-start justify-between border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
                >
                    <div class="flex items-start gap-3">
                        <Avatar class="h-10 w-10">
                            <AvatarImage
                                :src="`https://ui-avatars.com/api/?name=${donation.is_anonymous ? 'Anonymous' : 'Donor'}&background=random`"
                            />
                            <AvatarFallback>
                                {{ donation.is_anonymous ? 'A' : 'D' }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <p class="font-medium">{{ donation.is_anonymous ? 'Anonymous Donor' : donation?.user?.name }}</p>
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
</template>
