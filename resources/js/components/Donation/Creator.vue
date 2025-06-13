<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { User } from '@/types';
import { defineProps } from 'vue';

const props = defineProps<{
    creator: User;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Campaign Creator</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="flex items-start gap-3">
                <Avatar class="h-12 w-12">
                    <AvatarImage :src="props.creator.avatar ?? `https://ui-avatars.com/api/?name=${props.creator.name}&background=random`" />
                    <AvatarFallback>
                        {{ props.creator.name.charAt(0) }}
                    </AvatarFallback>
                </Avatar>
                <div>
                    <p class="font-medium">{{ props.creator.name }}</p>
                    <p class="text-sm text-muted-foreground">Campaign Organizer</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
