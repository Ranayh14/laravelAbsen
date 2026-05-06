<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminHelpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = AdminHelpRequest::query();
            
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }
            
            $requests = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'ok' => true, 
                'message' => 'Berhasil mengambil data bantuan.',
                'data' => $requests
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal mengambil data bantuan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $helpRequest = AdminHelpRequest::find($id);
            
            if (!$helpRequest) {
                return response()->json(['ok' => false, 'message' => 'Data bantuan tidak ditemukan.'], 404);
            }

            if ($request->user()->role !== 'admin' && $helpRequest->user_id !== $request->user()->id) {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            return response()->json([
                'ok' => true, 
                'message' => 'Berhasil mengambil detail data bantuan.',
                'data' => $helpRequest
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal mengambil detail data bantuan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ], [
                'subject.required' => 'Subjek wajib diisi.',
                'message.required' => 'Pesan wajib diisi.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = $request->user();
            $helpRequest = AdminHelpRequest::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'pending'
            ]);

            return response()->json([
                'ok' => true, 
                'message' => 'Permintaan bantuan berhasil dikirim.', 
                'data' => $helpRequest
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal mengirim permintaan bantuan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $helpRequest = AdminHelpRequest::find($id);
            
            if (!$helpRequest) {
                return response()->json(['ok' => false, 'message' => 'Data bantuan tidak ditemukan.'], 404);
            }

            if ($request->user()->role !== 'admin' && $helpRequest->user_id !== $request->user()->id) {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ], [
                'subject.required' => 'Subjek wajib diisi.',
                'message.required' => 'Pesan wajib diisi.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }

            $helpRequest->update([
                'subject' => $request->subject,
                'message' => $request->message
            ]);

            return response()->json([
                'ok' => true, 
                'message' => 'Permintaan bantuan berhasil diperbarui.',
                'data' => $helpRequest
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal memperbarui permintaan bantuan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $helpRequest = AdminHelpRequest::find($id);
            
            if (!$helpRequest) {
                return response()->json(['ok' => false, 'message' => 'Data bantuan tidak ditemukan.'], 404);
            }

            if ($request->user()->role !== 'admin' && $helpRequest->user_id !== $request->user()->id) {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            $helpRequest->delete();

            return response()->json([
                'ok' => true, 
                'message' => 'Data bantuan berhasil dihapus.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal menghapus data bantuan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat memperbarui status bantuan.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,solved,disapproved,approved',
                'admin_note' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }

            $helpRequest = AdminHelpRequest::find($id);
            if (!$helpRequest) {
                return response()->json(['ok' => false, 'message' => 'Permintaan tidak ditemukan.'], 404);
            }

            $helpRequest->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note ?? $helpRequest->admin_note
            ]);

            // Segarkan data dari database
            $helpRequest->refresh();

            return response()->json([
                'ok' => true, 
                'message' => 'Status bantuan berhasil diperbarui.',
                'data' => $helpRequest // <-- Data terbaru disertakan di sini
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal memperbarui status bantuan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
}
