<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Added Log facade

class FileController extends Controller
{
    /**
     * Display a listing of the files.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $files = File::where('user_id', Auth::id())
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('detail', 'LIKE', '%' . $search . '%');
            })
            ->paginate(4);

        if ($request->ajax()) {
            return view('files.file-list', compact('files'))->render();
        }

        return view('files.index', compact('files'));
    }

    /**
     * Show the form for creating a new file.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('files.create');
    }

    /**
     * Store a newly created file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
            "detail" => "required",
            "updload_file" => "nullable|mimes:png,jpg,jpeg,webp,pdf,doc,docx|max:5120",
        ]);

        $name = $request->name;
        $originalName = $name;
        $counter = 1;

        while (File::where('name', $name)->where('user_id', Auth::id())->exists()) {
            $name = $originalName . ' (' . $counter . ')';
            $counter++;
        }

        $path = null;

        if ($request->hasFile('updload_file')) {
            $file = $request->file('updload_file');
            $extension = $file->getClientOriginalExtension();

            $filename = time() . '.' . $extension;
            $destinationPath = public_path('uploads/files');
            $file->move($destinationPath, $filename);

            $path = 'uploads/files/' . $filename;

            if (file_exists(public_path($path))) {
                Log::info('File stored successfully: ' . $path);
            } else {
                Log::error('File storage failed: ' . $path);
            }
        }

        try {
            File::create([
                "name" => $name,
                "detail" => $request->detail,
                "updload_file" => $path,
                "user_id" => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Database insertion failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to save file. Please check logs.']);
        }

        return redirect()->route('files.index')
            ->with('success', 'File added successfully.');
    }

    /**
     * Display the specified file.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($id)
    {
        $file = File::where('user_id', Auth::id())->findOrFail($id);
        return view('files.show', compact('file'));
    }

    /**
     * Show the form for editing the specified file.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($id)
    {
        $file = File::where('user_id', Auth::id())->findOrFail($id);
        return view('files.edit', compact('file'));
    }

    /**
     * Update the specified file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => "required",
            "detail" => "required",
            "updload_file" => "nullable|mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,webp"
        ]);

        $file = File::where('user_id', Auth::id())->findOrFail($id);

        if ($request->hasFile('updload_file')) {
            $uploadedFile = $request->file('updload_file');
            $extension = $uploadedFile->getClientOriginalExtension();

            $filename = time() . '.' . $extension;
            $destinationPath = public_path('uploads/files');
            $uploadedFile->move($destinationPath, $filename);

            if ($file->updload_file && file_exists(public_path($file->updload_file))) {
                unlink(public_path($file->updload_file));
            }

            $file->update([
                "updload_file" => 'uploads/files/' . $filename,
            ]);
        }

        $file->update([
            "name" => $request->name,
            "detail" => $request->detail,
        ]);

        return redirect()->route('files.index')
            ->with('success', 'File updated successfully.');
    }

    /**
     * Remove the specified file from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $file = File::where('user_id', Auth::id())->findOrFail($id);

        if ($file->updload_file && file_exists(public_path($file->updload_file))) {
            unlink(public_path($file->updload_file));
        }

        $file->delete();

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully.');
    }
}