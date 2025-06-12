<script setup lang="ts">
import CreditCardForm from '@/components/Payment/Mock/CreditCardForm.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';
import { CheckCircle, Heart } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref } from 'vue';

interface Props {
    isOpen: boolean;
    campaign: {
        id: number;
        title: string;
    };
    paymentDriver: string;
}

interface Emits {
    (e: 'update:isOpen', value: boolean): void;
    (e: 'success'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const donationForm = reactive({
    amount: '',
    is_anonymous: false,
    card_number: '',
    card_expiry: '',
    card_cvc: '',
    card_holder_name: '',
    processing: false,
    success: false,
    errors: {} as Record<string, string>,
});

const selectedAmount = ref<number | null>(null);
const quickAmounts = [10, 25, 50, 100, 250, 500];

const resetForm = () => {
    donationForm.amount = '';
    donationForm.is_anonymous = false;
    donationForm.card_number = '';
    donationForm.card_expiry = '';
    donationForm.card_cvc = '';
    donationForm.card_holder_name = '';
    donationForm.processing = false;
    donationForm.success = false;
    donationForm.errors = {};
    selectedAmount.value = null;
};

const selectQuickAmount = (amount: number) => {
    selectedAmount.value = amount;
    donationForm.amount = amount.toString();
};

const updateCardDetails = (cardData: any) => {
    donationForm.card_number = cardData.card_number;
    donationForm.card_expiry = cardData.card_expiry;
    donationForm.card_cvc = cardData.card_cvc;
    donationForm.card_holder_name = cardData.card_holder_name;
};

const submitDonation = () => {
    donationForm.processing = true;
    donationForm.errors = {};

    router.post(
        `/campaigns/${props.campaign.id}/donate`,
        {
            amount: donationForm.amount,
            is_anonymous: donationForm.is_anonymous,
            card_number: donationForm.card_number,
            card_expiry: donationForm.card_expiry,
            card_cvc: donationForm.card_cvc,
            card_holder_name: donationForm.card_holder_name,
        },
        {
            onSuccess: () => {
                donationForm.success = true;
                donationForm.processing = false;

                setTimeout(() => {
                    resetForm();
                    emit('update:isOpen', false);
                    emit('success');
                }, 3000);
            },
            onError: (errors) => {
                donationForm.errors = errors;
            },
            onFinish: () => {
                donationForm.processing = false;
            },
        },
    );
};

const closeModal = () => {
    emit('update:isOpen', false);
    resetForm();
};

const isFormValid = computed(() => {
    return donationForm.amount && donationForm.card_number && donationForm.card_expiry && donationForm.card_cvc && donationForm.card_holder_name;
});

const paymentDriver = computed(() => props.paymentDriver || 'mock');

const StripeCreditCardForm = ref<Component | null>(null);

onMounted(async () => {
    if (paymentDriver.value === 'stripe') {
        const module = await import('@/components/Payment/Stripe/CreditCardForm.vue');
        StripeCreditCardForm.value = module.default;
    }
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('update:isOpen', $event)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ donationForm.success ? 'Donation Successful!' : 'Make a Donation' }}</DialogTitle>
                <DialogDescription>
                    {{ donationForm.success ? 'Thank you for your generous donation!' : `Support "${campaign.title}" with your donation` }}
                </DialogDescription>
            </DialogHeader>

            <!-- Success Message Display -->
            <div v-if="donationForm.success" class="rounded-lg border border-green-200 bg-green-50 p-6 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <CheckCircle class="h-6 w-6 text-green-600" />
                </div>
                <h3 class="mb-2 text-lg font-medium text-green-900">Payment Successful!</h3>
                <p class="mb-4 text-sm text-green-700">
                    Your donation of €{{ donationForm.amount }} has been processed successfully. Thank you for supporting "{{ campaign.title }}"!
                </p>
                <p class="text-xs text-green-600">This window will close automatically in a few seconds...</p>
            </div>

            <form v-if="!donationForm.success" @submit.prevent="submitDonation" class="space-y-6">
                <!-- Quick Amount Selection -->
                <div class="space-y-3">
                    <Label class="text-base font-semibold">Select Amount (EUR)</Label>
                    <div class="grid grid-cols-3 gap-2">
                        <Button
                            v-for="amount in quickAmounts"
                            :key="amount"
                            type="button"
                            :variant="selectedAmount === amount ? 'default' : 'outline'"
                            size="sm"
                            @click="selectQuickAmount(amount)"
                        >
                            €{{ amount }}
                        </Button>
                    </div>
                </div>

                <!-- Custom Amount -->
                <div class="space-y-2">
                    <Label for="amount">Custom Amount (EUR)</Label>
                    <Input
                        id="amount"
                        v-model="donationForm.amount"
                        type="number"
                        min="1"
                        step="0.01"
                        placeholder="Enter amount"
                        :class="donationForm.errors.amount ? 'border-red-500' : ''"
                    />
                    <p v-if="donationForm.errors.amount" class="text-sm text-red-600">
                        {{ donationForm.errors.amount }}
                    </p>
                </div>

                <div class="border-t pt-4">
                    <CreditCardForm v-if="paymentDriver === 'mock'" :errors="donationForm.errors" @update:card-details="updateCardDetails" />
                    <component
                        v-else-if="paymentDriver === 'stripe' && StripeCreditCardForm"
                        :is="StripeCreditCardForm"
                        :errors="donationForm.errors"
                        @update:card-details="updateCardDetails"
                    />
                </div>

                <div class="flex items-center space-x-2 border-t pt-4">
                    <Checkbox id="is_anonymous" v-model:checked="donationForm.is_anonymous" />
                    <Label for="is_anonymous" class="text-sm"> Make this donation anonymous </Label>
                </div>

                <!-- Payment Error Display -->
                <div v-if="donationForm.errors.payment" class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center">
                        <div class="text-sm font-medium text-red-600">Payment Failed</div>
                    </div>
                    <p class="mt-1 text-sm text-red-600">
                        {{ donationForm.errors.payment }}
                    </p>
                </div>

                <!-- General Error Display -->
                <div v-if="donationForm.errors.general" class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center">
                        <div class="text-sm font-medium text-red-600">Error</div>
                    </div>
                    <p class="mt-1 text-sm text-red-600">
                        {{ donationForm.errors.general }}
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-2 border-t pt-4">
                    <Button type="button" variant="outline" class="flex-1" @click="closeModal"> Cancel </Button>
                    <Button type="submit" class="flex-1" :disabled="donationForm.processing || !isFormValid">
                        <Heart class="mr-2 h-4 w-4" />
                        {{ donationForm.processing ? 'Processing...' : `Donate €${donationForm.amount || '0'}` }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
