<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with(['creator', 'updater']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('supplier_code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('is_active', $request->status == 'active');
        }

        // Sort functionality
        $sortField = $request->get('sort', 'supplier_name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $suppliers = $query->paginate($request->get('per_page', 15));
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();
        $inactiveSuppliers = Supplier::where('is_active', false)->count();

        // For AJAX requests, return JSON
        if ($request->ajax()) {
            return response()->json([
                'suppliers' => $suppliers,
                'filters' => $request->all()
            ]);
        }

        // For regular requests, return view
        return view('ktvtc.cafeteria.suppliers.index', [
            'suppliers' => $suppliers,
            'totalSuppliers' => $totalSuppliers,
            'activeSuppliers' => $activeSuppliers,
            'inactiveSuppliers' => $inactiveSuppliers,
            'filters' => $request->all()
        ]);
    }

    public function create()
    {
        // Return the create modal content (if using modal)
        if (request()->ajax()) {
            return view('ktvtc.cafeteria.suppliers.modals.create');
        }

        return redirect()->route('suppliers.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_code' => 'required|string|unique:suppliers|max:50',
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['is_active'] = $data['is_active'] ?? true;

        $supplier = Supplier::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully',
                'supplier' => $supplier
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully');
    }

    public function show($id)
    {
        $supplier = Supplier::with(['creator', 'updater', 'purchaseOrders'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($supplier);
        }

        return view('ktvtc.cafeteria.suppliers.show', compact('supplier'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        if (request()->ajax()) {
            return view('ktvtc.cafeteria.suppliers.modals.edit', compact('supplier'));
        }

        return redirect()->route('suppliers.index');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'supplier_code' => 'sometimes|string|unique:suppliers,supplier_code,' . $id . '|max:50',
            'supplier_name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $supplier->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully',
                'supplier' => $supplier->fresh()->load(['creator', 'updater'])
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Check if supplier has associated records
        if ($supplier->purchaseOrders()->exists() || $supplier->goodsReceivedNotes()->exists()) {
            if (request()->ajax()) {
                return response()->json([
                    'error' => 'Cannot delete supplier with associated records'
                ], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete supplier with associated records');
        }

        $supplier->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Supplier deleted successfully']);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully');
    }

    public function restore($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->restore();

        if (request()->ajax()) {
            return response()->json(['message' => 'Supplier restored successfully']);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier restored successfully');
    }
}
