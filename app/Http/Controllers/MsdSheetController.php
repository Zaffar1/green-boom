<?php

namespace App\Http\Controllers;

use App\Models\MsdSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MsdSheetController extends Controller
{
    public function allMsdsSheets()
    {
        try {
            $all_msd = MsdSheet::orderBy('id', 'DESC')->get();
            return response()->json(['all_msds' => $all_msd]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function customerAllMsdsSheets()
    {
        try {
            $all_msd = MsdSheet::whereStatus('Active')->orderBy('id', 'DESC')->get();
            return response()->json(['all_msds' => $all_msd]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function msdSheetDetail(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        try {
            $msdSheet = MsdSheet::find($request->id);
            return response()->json(['sheet_detail' => $msdSheet]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function addMsdSheet(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required',
            // 'description' => 'required',
            'file' => 'required|mimes:pdf',
        ]);
        try {
            $validate['status'] = 'Active';
            $validate['file'] = $request->file('file')->store('public/msdSheets');
            MsdSheet::create($validate);
            return response()->json(["message" => "Video successfully added"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function updateMsdSheet(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'title' => 'required',
            'file' => 'required|mimes:pdf'
        ]);
        try {
            $msd = MsdSheet::find($request->id);
            if (!$msd) {
                return response()->json(["error" => "Msd Sheet not found"], 404);
            }
            $msd->title = $request->title;
            if ($request->hasFile('file')) {
                Storage::delete($msd->file);
                $msd->file = $request->file('file')->store('public/msdSheets');
            }
            $msd->save();
            return response()->json(['message' => 'Msd sheet successfully updated']);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }

    public function deleteMsdSheet($id)
    {
        try {
            $msd = MsdSheet::find($id);
            if (!$msd) {
                return response()->json(["error" => "MsdSheet not found"], 404);
            }
            $filePath = $msd->file;
            $msd->delete();
            Storage::delete($filePath);
            return response()->json(["message" => "MsdSheet successfully deleted"]);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 400);
        }
    }
}
