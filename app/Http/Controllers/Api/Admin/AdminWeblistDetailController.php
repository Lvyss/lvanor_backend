<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Weblist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminWeblistDetailController extends Controller
{
    public function update(Request $request, $id)
    {
        try {
            $weblist = Weblist::with('weblistDetail')->findOrFail($id);

            $validated = $request->validate([
                'description' => 'required|string',
                'features' => 'required|array',
                'features.*' => 'string',
                'tech_stack' => 'required|string',
                'price' => 'nullable|numeric',
                'website_link' => 'nullable|url'
            ]);

            DB::transaction(function () use ($weblist, $validated) {
                $data = [
                    'description' => $validated['description'],
                    'features' => json_encode($validated['features']),
                    'tech_stack' => $validated['tech_stack'],
                    'price' => $validated['price'],
                    'website_link' => $validated['website_link']
                ];

                if ($weblist->weblistDetail) {
                    $weblist->weblistDetail->update($data);
                } else {
                    $weblist->weblistDetail()->create($data);
                }
            });

            return response()->json(['message' => 'Detail weblist berhasil diupdate.']);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gagal update detail weblist', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'weblist_id' => $id,
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan internal saat update detail.',
            ], 500);
        }
    }
}
