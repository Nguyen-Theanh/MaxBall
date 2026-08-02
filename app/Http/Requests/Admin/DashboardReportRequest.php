<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $period = $this->input('period', 'this_month');

        $this->merge([
            'period' => $period,
            'chart_granularity' => $this->input(
                'chart_granularity',
                $period === 'this_year' ? 'month' : 'day'
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'period' => ['required', Rule::in(['today', 'last_7_days', 'this_month', 'this_year', 'custom'])],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
            'chart_granularity' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
        ];
    }

    public function messages(): array
    {
        return [
            'period.in' => 'Bộ lọc thời gian không hợp lệ.',
            'start_date.required_if' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required_if' => 'Vui lòng chọn ngày kết thúc.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
            'chart_granularity.in' => 'Kiểu nhóm biểu đồ không hợp lệ.',
        ];
    }
}
