<script setup lang="ts">
interface Props {
    title?: string;
    message?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'No items found',
    message: 'No items are available at this time.',
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <h2 class="mt-4 text-xl font-semibold">{{ props.title }}</h2>
        <p class="mt-2 text-muted-foreground">{{ props.message }}</p>
    </div>
</template>
