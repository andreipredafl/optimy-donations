<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { type DateValue, getLocalTimeZone, today } from '@internationalized/date';
import { Calendar as CalendarIcon, X } from 'lucide-vue-next';
import { computed } from 'vue';

export interface DatePickerProps {
    modelValue?: DateValue | null;
    label?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    error?: string;
    helperText?: string;
    minValue?: DateValue;
    maxValue?: DateValue;
    id?: string;
    name?: string;
    clearable?: boolean;
    dateFormat?: 'short' | 'long' | 'full';
    size?: 'sm' | 'md' | 'lg';
    variant?: 'default' | 'outline' | 'ghost';
}

const props = withDefaults(defineProps<DatePickerProps>(), {
    placeholder: 'Select date',
    required: false,
    disabled: false,
    clearable: false,
    dateFormat: 'long',
    size: 'md',
    variant: 'outline',
    minValue: () => today(getLocalTimeZone()),
});

const emit = defineEmits<{
    'update:modelValue': [value: DateValue | null];
    change: [value: DateValue | null];
    clear: [];
}>();

const formatDateForDisplay = (date: DateValue | null): string => {
    if (!date) return '';

    const formatOptions: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: props.dateFormat === 'short' ? 'short' : 'long',
        day: 'numeric',
    };

    if (props.dateFormat === 'full') {
        formatOptions.weekday = 'long';
    } else if (props.dateFormat === 'long') {
        formatOptions.weekday = 'short';
    }

    return new Intl.DateTimeFormat('en-US', formatOptions).format(date.toDate(getLocalTimeZone()));
};

const buttonClasses = computed(() => {
    const baseClasses = 'justify-start text-left font-normal transition-colors';
    const sizeClasses = {
        sm: 'h-8 px-2 text-xs',
        md: 'h-10 px-3 text-sm',
        lg: 'h-12 px-4 text-base',
    };

    const stateClasses = {
        default: !props.modelValue ? 'text-muted-foreground' : '',
        error: props.error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '',
        disabled: props.disabled ? 'opacity-50 cursor-not-allowed' : '',
    };

    return cn(baseClasses, sizeClasses[props.size], stateClasses.default, stateClasses.error, stateClasses.disabled, 'w-full');
});

const iconSize = computed(() => {
    return {
        sm: 'h-3 w-3',
        md: 'h-4 w-4',
        lg: 'h-5 w-5',
    }[props.size];
});

const handleDateSelect = (date: DateValue | undefined) => {
    const newValue = date || null;
    emit('update:modelValue', newValue);
    emit('change', newValue);
};

const clearDate = () => {
    emit('update:modelValue', null);
    emit('change', null);
    emit('clear');
};

const labelId = computed(() => (props.id ? `${props.id}-label` : undefined));
const describedBy = computed(() => {
    const ids = [];
    if (props.error && props.id) ids.push(`${props.id}-error`);
    if (props.helperText && props.id) ids.push(`${props.id}-helper`);
    return ids.length > 0 ? ids.join(' ') : undefined;
});
</script>

<template>
    <div class="space-y-2">
        <!-- Label -->
        <Label v-if="label" :for="id" :id="labelId" class="text-sm font-medium">
            {{ label }}
            <span v-if="required" class="ml-1 text-red-500">*</span>
        </Label>

        <!-- Date Picker -->
        <div class="relative">
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        :variant="variant"
                        :disabled="disabled"
                        :class="buttonClasses"
                        :id="id"
                        :name="name"
                        :aria-labelledby="labelId"
                        :aria-describedby="describedBy"
                        :aria-required="required"
                        :aria-invalid="!!error"
                    >
                        <CalendarIcon :class="cn('mr-2', iconSize)" />
                        <span class="flex-1 truncate">
                            {{ modelValue ? formatDateForDisplay(modelValue) : placeholder }}
                        </span>
                    </Button>
                </PopoverTrigger>

                <PopoverContent class="w-auto p-0" align="start">
                    <Calendar
                        :model-value="modelValue ?? undefined"
                        @update:model-value="handleDateSelect"
                        :min-value="minValue"
                        :max-value="maxValue"
                        :disabled="disabled"
                        :weekday-format="'short'"
                        class="rounded-md border"
                        initial-focus
                    />
                </PopoverContent>
            </Popover>

            <!-- Clear Button -->
            <Button
                v-if="clearable && modelValue && !disabled"
                type="button"
                variant="ghost"
                size="sm"
                class="absolute top-1/2 right-1 h-6 w-6 -translate-y-1/2 p-0 hover:bg-gray-100 dark:hover:bg-gray-800"
                @click="clearDate"
                :aria-label="'Clear date'"
            >
                <X :class="cn('h-3 w-3')" />
            </Button>
        </div>

        <!-- Helper Text -->
        <p v-if="helperText && !error" :id="id ? `${id}-helper` : undefined" class="text-xs text-muted-foreground">
            {{ helperText }}
        </p>

        <!-- Error Message -->
        <p v-if="error" :id="id ? `${id}-error` : undefined" class="text-sm text-red-600" role="alert">
            {{ error }}
        </p>
    </div>
</template>

<style scoped></style>
