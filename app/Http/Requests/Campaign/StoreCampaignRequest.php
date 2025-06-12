<?php

namespace App\Http\Requests\Campaign;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'goal_amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'], // 10MB
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert goal_amount to float if it's a string
        if ($this->has('goal_amount') && is_string($this->goal_amount)) {
            $this->merge([
                'goal_amount' => (float) $this->goal_amount,
            ]);
        }

        // Ensure category_id is an integer
        if ($this->has('category_id') && is_string($this->category_id)) {
            $this->merge([
                'category_id' => (int) $this->category_id,
            ]);
        }
    }

    /**
     * Get the validated data with processed values.
     *
     * @return array<string, mixed>
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();

        if (isset($validated['goal_amount'])) {
            $validated['goal_amount_cents'] = (int) round($validated['goal_amount'] * 100);
            unset($validated['goal_amount']);
        }

        $validated['current_amount_cents'] = 0;
        $validated['donations_count'] = 0;
        $validated['donors_count'] = 0;
        $validated['status'] = Campaign::STATUS_ACTIVE;
        $validated['creator_id'] = auth()->id();

        return $validated;
    }
}
