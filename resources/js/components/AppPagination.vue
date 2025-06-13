<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        links: PaginationLink[];
        currentPage: number;
        lastPage: number;
        baseUrl?: string;
    }>(),
    {
        baseUrl: '',
    },
);

const isPreviousDisabled = computed(() => props.currentPage <= 1);
const isNextDisabled = computed(() => props.currentPage >= props.lastPage);

const getPageUrl = (page: number | string): string => {
    const url = props.baseUrl || window.location.pathname;
    return `${url}?page=${page}`;
};
</script>

<template>
    <div v-if="lastPage > 1" class="mt-8 flex justify-center">
        <div class="flex items-center gap-2">
            <!-- Previous -->
            <Link v-if="!isPreviousDisabled" :href="getPageUrl(currentPage - 1)" preserve-scroll>
                <Button variant="outline" size="sm" class="flex items-center gap-1">
                    <ChevronLeft class="h-4 w-4" />
                    Previous
                </Button>
            </Link>
            <Button v-else variant="outline" size="sm" disabled class="flex items-center gap-1">
                <ChevronLeft class="h-4 w-4" />
                Previous
            </Button>

            <!-- Buttons for pages -->
            <div class="flex gap-1">
                <template v-for="link in links" :key="link.label">
                    <Link
                        v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                        :href="link.url"
                        preserve-scroll
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md text-sm font-medium transition-colors"
                        :class="
                            link.active
                                ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                : 'border border-input bg-background hover:bg-accent hover:text-accent-foreground'
                        "
                    >
                        {{ link.label }}
                    </Link>
                </template>
            </div>

            <!-- Next -->
            <Link v-if="!isNextDisabled" :href="getPageUrl(currentPage + 1)" preserve-scroll>
                <Button variant="outline" size="sm" class="flex items-center gap-1">
                    Next
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </Link>
            <Button v-else variant="outline" size="sm" disabled class="flex items-center gap-1">
                Next
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>

    <div v-if="lastPage > 0" class="mt-2 text-center text-sm text-muted-foreground">
        <slot></slot>
    </div>
</template>
