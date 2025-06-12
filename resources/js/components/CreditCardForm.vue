<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { CreditCard } from 'lucide-vue-next';
import { computed, reactive, watch } from 'vue';

interface Props {
    errors?: Record<string, string>;
}

interface Emits {
    (
        e: 'update:card-details',
        value: {
            card_number: string;
            card_expiry: string;
            card_cvc: string;
            card_holder_name: string;
        },
    ): void;
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
});

const emit = defineEmits<Emits>();

const cardForm = reactive({
    card_number: '',
    card_expiry: '',
    card_cvc: '',
    card_holder_name: '',
});

watch(
    cardForm,
    (newValue) => {
        emit('update:card-details', { ...newValue });
    },
    { deep: true },
);

const formatCardNumber = (event: Event) => {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    const formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    cardForm.card_number = formattedValue.substring(0, 19);

    input.value = cardForm.card_number;
};

const formatExpiry = (event: Event) => {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    cardForm.card_expiry = value;

    input.value = cardForm.card_expiry;
};

const formatCVC = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.replace(/[^0-9]/gi, '').substring(0, 4);
    cardForm.card_cvc = value;

    input.value = cardForm.card_cvc;
};

const getCardType = (cardNumber: string): string => {
    const number = cardNumber.replace(/\s/g, '');

    if (/^4/.test(number)) return 'visa';
    if (/^5[1-5]/.test(number)) return 'mastercard';
    if (/^3[47]/.test(number)) return 'amex';
    if (/^6/.test(number)) return 'discover';

    return 'generic';
};

const cardType = computed(() => getCardType(cardForm.card_number));
</script>

<template>
    <div class="space-y-4">
        <div class="mb-3 flex items-center gap-2">
            <CreditCard class="h-5 w-5" />
            <Label class="text-base font-semibold">Payment Information</Label>
        </div>

        <!-- Card Holder Name -->
        <div class="space-y-2">
            <Label for="card_holder_name">Cardholder Name</Label>
            <Input
                id="card_holder_name"
                v-model="cardForm.card_holder_name"
                type="text"
                placeholder="Name on card"
                :class="errors.card_holder_name ? 'border-red-500' : ''"
            />
            <p v-if="errors.card_holder_name" class="text-sm text-red-600">
                {{ errors.card_holder_name }}
            </p>
        </div>

        <!-- Card Number -->
        <div class="space-y-2">
            <Label for="card_number">Card Number</Label>
            <div class="relative">
                <Input
                    id="card_number"
                    v-model="cardForm.card_number"
                    type="text"
                    placeholder="1234 5678 9012 3456"
                    maxlength="19"
                    :class="errors.card_number ? 'border-red-500' : ''"
                    @input="formatCardNumber"
                />
                <!-- Card type indicator -->
                <div class="absolute top-1/2 right-3 -translate-y-1/2 transform">
                    <div class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 uppercase">
                        {{ cardType }}
                    </div>
                </div>
            </div>
            <p v-if="errors.card_number" class="text-sm text-red-600">
                {{ errors.card_number }}
            </p>
        </div>

        <!-- Expiry and CVC -->
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <Label for="card_expiry">Expiry Date</Label>
                <Input
                    id="card_expiry"
                    v-model="cardForm.card_expiry"
                    type="text"
                    placeholder="MM/YY"
                    maxlength="5"
                    :class="errors.card_expiry ? 'border-red-500' : ''"
                    @input="formatExpiry"
                />
                <p v-if="errors.card_expiry" class="text-sm text-red-600">
                    {{ errors.card_expiry }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="card_cvc">CVC</Label>
                <Input
                    id="card_cvc"
                    v-model="cardForm.card_cvc"
                    type="text"
                    placeholder="123"
                    maxlength="4"
                    :class="errors.card_cvc ? 'border-red-500' : ''"
                    @input="formatCVC"
                />
                <p v-if="errors.card_cvc" class="text-sm text-red-600">
                    {{ errors.card_cvc }}
                </p>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="rounded bg-gray-50 p-3 text-xs text-gray-500">
            <p class="flex items-center gap-1">🔒 Your payment information is secure and encrypted</p>
        </div>
    </div>
</template>
