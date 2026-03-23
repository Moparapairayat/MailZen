<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class VatTaxController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.vat-tax.index', [
            'vat_enabled' => (bool) Setting::get('vat_enabled', false),
            'vat_rate' => Setting::get('vat_rate', null),
            'vat_number' => Setting::get('vat_number', null),
            'tax_notes' => Setting::get('tax_notes', null),
            'us_sales_tax_rates' => Setting::get('us_sales_tax_rates', []),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'vat_enabled' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'tax_notes' => ['nullable', 'string'],
            'us_sales_tax_rates' => ['nullable', 'string'],
        ]);

        Setting::set('vat_enabled', $request->boolean('vat_enabled'), 'tax', 'boolean');
        Setting::set('vat_rate', $validated['vat_rate'] ?? null, 'tax', 'string');
        Setting::set('vat_number', $validated['vat_number'] ?? null, 'tax', 'string');
        Setting::set('tax_notes', $validated['tax_notes'] ?? null, 'tax', 'string');

        $salesTaxRates = [];
        $raw = trim((string) ($validated['us_sales_tax_rates'] ?? ''));
        if ($raw !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
                $line = trim((string) $line);
                if ($line === '' || !str_contains($line, '=')) {
                    continue;
                }
                [$state, $rate] = array_pad(explode('=', $line, 2), 2, null);
                $state = strtoupper(trim((string) $state));
                $rate = trim((string) $rate);
                if ($state === '' || !is_numeric($rate)) {
                    continue;
                }
                $salesTaxRates[$state] = (float) $rate;
            }
        }

        Setting::set('us_sales_tax_rates', $salesTaxRates, 'tax', 'array');

        return redirect()
            ->route('admin.vat-tax.index')
            ->with('success', __('Vat/Tax settings updated.'));
    }
}
