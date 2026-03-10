<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in-progress,done'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'project_id' => ['sometimes', 'exists:projects,id'],
            'assigned_to' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: pending, in-progress, done.',
            'due_date.after_or_equal' => 'Due date must be today or a future date.',
            'project_id.exists' => 'The project does not exist.',
            'assigned_to.exists' => 'The user does not exist.',
        ];
    }
}
