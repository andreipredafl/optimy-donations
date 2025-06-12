<script setup lang="ts">
import AppDatePicker from '@/components/AppDatePicker.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/';
import { Textarea } from '@/components/ui/textarea/';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Category } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarDate, type DateValue, getLocalTimeZone, today } from '@internationalized/date';
import { computed, ref } from 'vue';

defineProps<{
    categories: Category[];
}>();

const todayDate = today(getLocalTimeZone());
const oneWeekLater = todayDate.add({ days: 7 });

const startDate = ref<CalendarDate>(todayDate);
const endDate = ref<CalendarDate>(oneWeekLater);
const imagePreview = ref<string | null>(null);

const form = useForm({
    title: '',
    description: '',
    goal_amount: '',
    category_id: '',
    start_date: '',
    end_date: '',
    featured_image: null as File | null,
});

const formatDateForSubmit = (date: CalendarDate): string => {
    return `${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}`;
};

const minEndDate = computed(() => {
    return startDate.value.add({ days: 1 });
});

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }

        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file');
            return;
        }

        form.featured_image = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.featured_image = null;
    imagePreview.value = null;
    const fileInput = document.getElementById('featured_image') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

const handleStartDateChange = (newStartDate: DateValue | null) => {
    if (newStartDate && 'year' in newStartDate) {
        const calendarDate = newStartDate as CalendarDate;
        startDate.value = calendarDate;
        form.start_date = formatDateForSubmit(calendarDate);

        if (endDate.value.compare(calendarDate) <= 0) {
            const newEndDate = calendarDate.add({ days: 7 });
            endDate.value = newEndDate;
            form.end_date = formatDateForSubmit(newEndDate);
        }
    }
};

const handleEndDateChange = (newEndDate: DateValue | null) => {
    if (newEndDate && 'year' in newEndDate) {
        const calendarDate = newEndDate as CalendarDate;
        endDate.value = calendarDate;
        form.end_date = formatDateForSubmit(calendarDate);
    }
};

const submit = () => {
    form.start_date = formatDateForSubmit(new CalendarDate(startDate.value.year, startDate.value.month, startDate.value.day));
    form.end_date = formatDateForSubmit(new CalendarDate(endDate.value.year, endDate.value.month, endDate.value.day));

    form.post('/campaigns', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create New Campaign" />
    <AppLayout>
        <div class="mx-auto flex h-full max-w-4xl flex-1 flex-col gap-6 p-4">
            <div class="flex flex-col space-y-2">
                <h1 class="text-3xl font-bold tracking-tight">Create New Campaign</h1>
                <p class="text-muted-foreground">Launch a fundraising campaign to support your cause and reach your goals.</p>
            </div>

            <Card class="w-full shadow-sm">
                <CardContent class="w-full p-8">
                    <form @submit.prevent="submit" class="w-full min-w-0 space-y-6">
                        <div class="space-y-2">
                            <Label for="title" class="text-sm font-medium"> Campaign Title <span class="text-red-500">*</span> </Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                placeholder="Enter a compelling campaign title"
                                required
                                maxlength="255"
                                :class="{ 'border-red-500': form.errors.title }"
                            />
                            <p v-if="form.errors.title" class="text-sm text-red-600">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description" class="text-sm font-medium"> Campaign Description <span class="text-red-500">*</span> </Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Tell people about your campaign. What are you raising money for?"
                                rows="6"
                                required
                                maxlength="2000"
                                :class="{ 'border-red-500': form.errors.description }"
                            />
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <p v-if="form.errors.description" class="text-red-600">
                                    {{ form.errors.description }}
                                </p>
                                <p class="ml-auto">{{ form.description.length }}/2000 characters</p>
                            </div>
                        </div>

                        <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="min-w-0 space-y-2">
                                <Label for="goal_amount" class="text-sm font-medium"> Funding Goal (USD) <span class="text-red-500">*</span> </Label>
                                <div class="relative w-full">
                                    <span class="absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground">$</span>
                                    <Input
                                        id="goal_amount"
                                        v-model="form.goal_amount"
                                        type="number"
                                        min="1"
                                        max="1000000"
                                        step="0.01"
                                        placeholder="1000.00"
                                        class="w-full pl-8"
                                        required
                                        :class="{ 'border-red-500': form.errors.goal_amount }"
                                    />
                                </div>
                                <p v-if="form.errors.goal_amount" class="text-sm text-red-600">
                                    {{ form.errors.goal_amount }}
                                </p>
                            </div>

                            <div class="min-w-0 space-y-2">
                                <Label for="category" class="text-sm font-medium"> Campaign Category <span class="text-red-500">*</span> </Label>
                                <Select v-model="form.category_id" required>
                                    <SelectTrigger class="w-full" :class="{ 'border-red-500': form.errors.category_id }">
                                        <SelectValue placeholder="Choose a category" />
                                    </SelectTrigger>
                                    <SelectContent class="w-full">
                                        <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()" class="w-full">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.category_id" class="text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </p>
                            </div>
                        </div>

                        <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="min-w-0">
                                <AppDatePicker
                                    id="start_date"
                                    name="start_date"
                                    @update:model-value="handleStartDateChange"
                                    label="Campaign Start Date"
                                    placeholder="Select start date"
                                    :min-value="todayDate"
                                    :error="form.errors.start_date"
                                    date-format="long"
                                    required
                                    :model-value="startDate"
                                >
                                </AppDatePicker>
                            </div>

                            <div class="min-w-0">
                                <AppDatePicker
                                    id="end_date"
                                    name="end_date"
                                    @update:model-value="handleEndDateChange"
                                    label="Campaign End Date"
                                    placeholder="Select end date"
                                    :min-value="minEndDate"
                                    :error="form.errors.end_date"
                                    date-format="long"
                                    required
                                    :model-value="endDate"
                                />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <Label for="featured_image" class="text-sm font-medium"> Campaign Featured Image </Label>

                            <div v-if="imagePreview" class="relative inline-block">
                                <img :src="imagePreview" alt="Campaign preview" class="h-48 w-full max-w-md rounded-lg border object-cover" />
                                <Button type="button" variant="destructive" size="sm" class="absolute top-2 right-2" @click="removeImage">
                                    Remove
                                </Button>
                            </div>

                            <div class="flex w-full items-center justify-center">
                                <label
                                    for="featured_image"
                                    class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:border-gray-400 hover:bg-gray-100"
                                    :class="{ 'border-red-500': form.errors.featured_image }"
                                >
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-500">PNG, JPG, or JPEG (Max 5MB)</p>
                                    </div>
                                    <input
                                        id="featured_image"
                                        type="file"
                                        accept="image/png,image/jpeg,image/jpg"
                                        class="hidden"
                                        @change="handleImageUpload"
                                    />
                                </label>
                            </div>
                            <p v-if="form.errors.featured_image" class="text-sm text-red-600">
                                {{ form.errors.featured_image }}
                            </p>
                        </div>

                        <div class="flex flex-col-reverse gap-3 pt-6 sm:flex-row sm:justify-end">
                            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
