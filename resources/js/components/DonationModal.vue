<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import CreditCardForm from './CreditCardForm.vue';

interface Props {
    isOpen: boolean;
    campaign: {
        id: number;
        title: string;
    };
}

interface Emits {
    (e: 'update:isOpen', value: boolean): void;
    (e: 'success'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const donationForm = reactive({
    amount: '',
    anonymous: false,
    card_number: '',
    card_expiry: '',
    card_cvc: '',
    card_holder_name: '',
    processing: false,
    errors: {} as Record<string, string>,
});

const selectedAmount = ref<number | null>(null);
const quickAmounts = [10, 25, 50, 100, 250, 500];

const resetForm = () => {
    donationForm.amount = '';
    donationForm.anonymous = false;
    donationForm.card_number = '';
    donationForm.card_expiry = '';
    donationForm.card_cvc = '';
    donationForm.card_holder_name = '';
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
            anonymous: donationForm.anonymous,
            card_number: donationForm.card_number,
            card_expiry: donationForm.card_expiry,
            card_cvc: donationForm.card_cvc,
            card_holder_name: donationForm.card_holder_name,
        },
        {
            onSuccess: () => {
                resetForm();
                emit('update:isOpen', false);
                emit('success');
            },
            onError: (errors) => {
                donationForm.errors = errors;
                console.log('Donation errors:', errors);
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
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('update:isOpen', $event)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Make a Donation</DialogTitle>
                <DialogDescription> Support "{{ campaign.title }}" with your donation </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitDonation" class="space-y-6">
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

                <!-- Credit Card Form Component -->
                <div class="border-t pt-4">
                    <CreditCardForm :errors="donationForm.errors" @update:card-details="updateCardDetails" />
                </div>

                <!-- Anonymous Checkbox -->
                <div class="flex items-center space-x-2 border-t pt-4">
                    <Checkbox id="anonymous" v-model:checked="donationForm.anonymous" />
                    <Label for="anonymous" class="text-sm"> Make this donation anonymous </Label>
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
