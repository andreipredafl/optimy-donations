<?php

namespace App\Http\Requests\Donation;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:10000',
                'decimal:0,2',
            ],
            'is_anonymous' => [
                'boolean',
            ],
            // I know, is not the best way to validate card details, but for the sake of this example (mock payment processing)
            'card_number' => [
                'required',
                'string',
                'regex:/^[\d\s]+$/',
                'min:13',
                'max:19',
            ],
            'card_expiry' => [
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            ],
            'card_cvc' => [
                'required',
                'string',
                'regex:/^\d{3,4}$/',
            ],
            'card_holder_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter a donation amount.',
            'amount.numeric' => 'The donation amount must be a valid number.',
            'amount.min' => 'The minimum donation amount is €1.00.',
            'amount.max' => 'The maximum donation amount is €10,000.00.',
            'amount.decimal' => 'The donation amount can have at most 2 decimal places.',

            'card_number.required' => 'Please enter your card number.',
            'card_number.regex' => 'Please enter a valid card number.',
            'card_number.min' => 'Card number is too short.',
            'card_number.max' => 'Card number is too long.',

            'card_expiry.required' => 'Please enter the card expiry date.',
            'card_expiry.regex' => 'Please enter expiry date in MM/YY format.',

            'card_cvc.required' => 'Please enter the card CVC.',
            'card_cvc.regex' => 'CVC must be 3 or 4 digits.',

            'card_holder_name.required' => 'Please enter the cardholder name.',
            'card_holder_name.min' => 'Cardholder name must be at least 2 characters.',
            'card_holder_name.max' => 'Cardholder name cannot exceed 100 characters.',
            'card_holder_name.regex' => 'Cardholder name contains invalid characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean card number (remove spaces for validation)
        if ($this->has('card_number')) {
            $this->merge([
                'card_number' => str_replace(' ', '', $this->input('card_number')),
            ]);
        }
        if ($this->has('is_anonymous')) {
            $this->merge([
                'is_anonymous' => filter_var($this->input('is_anonymous'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            if ($this->has('card_number')) {
                $cardNumber = $this->input('card_number');

                $validTestCards = [
                    '4532123456789010', // Successful donation
                    '4532123456789011', // Payment declined
                    '4532123456789012', // Insufficient funds
                ];

                if (! in_array($cardNumber, $validTestCards)) {
                    $validator->errors()->add('card_number', 'Please use one of the test card numbers: 4532 1234 5678 9010, 4532 1234 5678 9011, or 4532 1234 5678 9012');
                }
            }

            // Expiry date validation
            if ($this->has('card_expiry')) {
                $expiry = $this->input('card_expiry');
                if (preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $matches)) {
                    $month = (int) $matches[1];
                    $year = 2000 + (int) $matches[2];
                    $expiryDate = \DateTime::createFromFormat('Y-m-d', "$year-$month-01");
                    $now = new \DateTime;

                    if ($expiryDate < $now) {
                        $validator->errors()->add('card_expiry', 'Card has expired.');
                    }
                }
            }
        });
    }
}
