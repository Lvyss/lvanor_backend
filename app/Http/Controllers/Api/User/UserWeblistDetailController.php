<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Weblist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserWeblistDetailController extends Controller
{
    public function update(Request $request, $id)
    {
        $weblist = Weblist::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string',
            'features' => 'required|array|min:1',
            'features.*' => 'string|max:255',
            'tech_stack' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'website_link' => 'nullable|url'
        ]);

        try {
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

            return response()->json([
                'success' => true,
                'message' => 'Detail Weblist berhasil diperbarui.',
                'data' => $weblist->fresh('weblistDetail')->weblistDetail
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Gagal update detail Weblist oleh user', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'weblist_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan detail.'
            ], 500);
        }
    }
}
