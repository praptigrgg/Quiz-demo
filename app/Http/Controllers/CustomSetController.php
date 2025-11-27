<?php

namespace App\Http\Controllers;

use App\Models\CustomSet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomSetController extends Controller
{
    // List all custom sets
    public function index()
    {
        return view('pages.admin.custom_sets.index', [
            'customs' => CustomSet::latest()->paginate(20) // matches Blade
        ]);
    }

    // Show create form
    public function create()
    {
        return view('pages.admin.custom_sets.create');
    }

    // Store new custom set
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pricingType' => 'required|in:free,paid',
            'normal_price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_published'] = false;

        $custom = CustomSet::create($validated);

        return redirect()->route('admin.custom_sets.index', $custom->id)
                         ->with('success', 'Custom set created successfully.');
    }


    // Edit form
    public function edit($id)
    {
        return view('pages.admin.custom_sets.edit', [
            'custom' => CustomSet::findOrFail($id)
        ]);
    }

    // Update custom set
    public function update(Request $request, $id)
    {
        $custom = CustomSet::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pricingType' => 'required|in:free,paid',
            'normal_price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $custom->update($validated);

        return back()->with('success', 'Custom set updated successfully.');
    }

    // Delete custom set
    public function destroy($id)
    {
        CustomSet::findOrFail($id)->delete();

        return redirect()->route('admin.custom_sets.index')
                         ->with('success', 'Custom set deleted.');
    }

    // Publish/Unpublish custom set
    public function togglePublish($id)
    {
        $custom = CustomSet::findOrFail($id);
        $custom->is_published = !$custom->is_published;
        $custom->save();

        $status = $custom->is_published ? 'published' : 'unpublished';

        return redirect()->route('admin.custom_sets.index')
                         ->with('success', "Custom set $status successfully.");
    }

    // Assign custom set to a meeting
    public function assignToMeeting(Request $request, $id)
    {
        $custom = CustomSet::findOrFail($id);

        $request->validate([
            'meeting_id' => 'required|string|max:255'
        ]);

        // You might store assignment in pivot table or a field, adjust as needed
        $custom->assigned_meeting_id = $request->meeting_id;
        $custom->save();

        return response()->json([
            'success' => true,
            'message' => "Custom set assigned to meeting {$request->meeting_id}"
        ]);
    }
}
