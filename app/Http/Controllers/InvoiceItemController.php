<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InvoiceItemController extends Controller
{
    /**
     * Display a listing of invoice items for a specific invoice.
     */
    public function index(Request $request, Invoice $invoice)
    {
        $items = $invoice->items()->orderBy('created_at')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        }

        return view('ktvtc.admin.invoices.items.index', compact('invoice', 'items'));
    }

    /**
     * Show the form for creating a new invoice item.
     */
    public function create(Invoice $invoice)
    {
        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('error', 'Cannot add items to a ' . $invoice->status . ' invoice.');
        }

        return view('ktvtc.admin.invoices.items.create', compact('invoice'));
    }

    /**
     * Store a newly created invoice item.
     */
    public function store(Request $request, Invoice $invoice)
    {
        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cannot add items to a ' . $invoice->status . ' invoice.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot add items to a ' . $invoice->status . ' invoice.');
        }

        $validator = Validator::make($request->all(), [
            'enrollment_fee_item_id' => 'nullable|exists:enrollment_fee_items,id',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $quantity = $request->quantity;
            $unitPrice = $request->unit_price;
            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;

            $subtotal = $unitPrice * $quantity;
            $total = $subtotal - $discount + $tax;

            // Create invoice item
            $item = $invoice->items()->create([
                'enrollment_fee_item_id' => $request->enrollment_fee_item_id,
                'description' => $request->description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
            ]);

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice item added successfully.',
                    'item' => $item
                ]);
            }

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice item added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to add invoice item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified invoice item.
     */
    public function show(Invoice $invoice, InvoiceItem $item)
    {
        // Ensure item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        $item->load(['enrollmentFeeItem.feeCategory']);

        return view('ktvtc.admin.invoices.items.show', compact('invoice', 'item'));
    }

    /**
     * Show the form for editing the specified invoice item.
     */
    public function edit(Invoice $invoice, InvoiceItem $item)
    {
        // Ensure item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('error', 'Cannot edit items of a ' . $invoice->status . ' invoice.');
        }

        return view('ktvtc.admin.invoices.items.edit', compact('invoice', 'item'));
    }

    /**
     * Update the specified invoice item.
     */
    public function update(Request $request, Invoice $invoice, InvoiceItem $item)
    {
        // Ensure item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cannot edit items of a ' . $invoice->status . ' invoice.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot edit items of a ' . $invoice->status . ' invoice.');
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $quantity = $request->quantity;
            $unitPrice = $request->unit_price;
            $discount = $request->discount ?? 0;
            $tax = $request->tax ?? 0;

            $subtotal = $unitPrice * $quantity;
            $total = $subtotal - $discount + $tax;

            // Update invoice item
            $item->update([
                'description' => $request->description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
            ]);

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice item updated successfully.',
                    'item' => $item
                ]);
            }

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice item updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update invoice item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified invoice item.
     */
    public function destroy(Invoice $invoice, InvoiceItem $item)
    {
        // Ensure item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete items from a ' . $invoice->status . ' invoice.');
        }

        DB::beginTransaction();

        try {
            $item->delete();

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice item deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete invoice item: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update item quantities/prices.
     */
    public function bulkUpdate(Request $request, Invoice $invoice)
    {
        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot edit items of a ' . $invoice->status . ' invoice.');
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            foreach ($request->items as $itemData) {
                $item = InvoiceItem::where('invoice_id', $invoice->id)
                    ->where('id', $itemData['id'])
                    ->firstOrFail();

                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $discount = $itemData['discount'] ?? 0;
                $tax = $itemData['tax'] ?? 0;

                $subtotal = $unitPrice * $quantity;
                $total = $subtotal - $discount + $tax;

                $item->update([
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice items updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update invoice items: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get items for API.
     */
    public function getItems(Invoice $invoice)
    {
        $items = $invoice->items()->get()->map(function($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'formatted_unit_price' => 'KES ' . number_format($item->unit_price, 2),
                'discount' => $item->discount,
                'formatted_discount' => 'KES ' . number_format($item->discount, 2),
                'tax' => $item->tax,
                'formatted_tax' => 'KES ' . number_format($item->tax, 2),
                'total' => $item->total,
                'formatted_total' => 'KES ' . number_format($item->total, 2),
                'fee_item_id' => $item->enrollment_fee_item_id,
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items,
            'summary' => [
                'subtotal' => $items->sum('total'),
                'total_items' => $items->count(),
            ]
        ]);
    }

    /**
     * Recalculate invoice totals.
     */
    private function recalculateInvoiceTotals(Invoice $invoice)
    {
        $subtotal = $invoice->items()->sum('total');

        // Sum discounts and taxes separately if needed
        $totalDiscount = $invoice->items()->sum('discount');
        $totalTax = $invoice->items()->sum('tax');

        $totalAmount = $subtotal;

        $invoice->update([
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
            'balance' => $totalAmount - $invoice->amount_paid,
        ]);

        // Update payment status
        $invoice->updatePaymentStatus();

        return $invoice;
    }

    /**
     * Duplicate an item.
     */
    public function duplicate(Invoice $invoice, InvoiceItem $item)
    {
        // Ensure item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        // Check if invoice can be edited
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Cannot duplicate items in a ' . $invoice->status . ' invoice.');
        }

        DB::beginTransaction();

        try {
            $newItem = $item->replicate();
            $newItem->save();

            // Recalculate invoice totals
            $this->recalculateInvoiceTotals($invoice);

            DB::commit();

            return redirect()->route('admin.tvet.invoices.show', $invoice)
                ->with('success', 'Invoice item duplicated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to duplicate invoice item: ' . $e->getMessage());
        }
    }
}
