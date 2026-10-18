<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnboardingScreen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    /**
     * List onboarding screens for mobile app.
     */
    public function index(Request $request)
    {
        $query = OnboardingScreen::query();

        // Filter active only by default unless specified
        if ($request->has("all") && $request->all == "1") {
            // return all
        } else {
            $query->active();
        }

        $screens = $query->ordered()->get();

        return response()->json([
            "status"  => true,
            "message" => "Onboarding screens retrieved successfully",
            "count"   => $screens->count(),
            "data"    => $screens
        ], 200);
    }

    /**
     * Show single onboarding screen.
     */
    public function show($id)
    {
        $screen = OnboardingScreen::find($id);

        if (!$screen) {
            return response()->json([
                "status"  => false,
                "message" => "Onboarding screen not found"
            ], 404);
        }

        return response()->json([
            "status"  => true,
            "message" => "Onboarding screen retrieved successfully",
            "data"    => $screen
        ], 200);
    }

    /**
     * Store new onboarding screen via API.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title_en"       => "required|string|max:255",
            "title_ar"       => "nullable|string|max:255",
            "description_en" => "nullable|string|max:1000",
            "description_ar" => "nullable|string|max:1000",
            "sort_order"     => "nullable|integer|min:1",
            "status"         => "nullable|in:active,inactive",
            "image"          => "nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status"  => false,
                "message" => "Validation error",
                "errors"  => $validator->errors()
            ], 422);
        }

        $screen = new OnboardingScreen();
        $screen->title_en       = trim($request->title_en);
        $screen->title_ar       = $request->title_ar ? trim($request->title_ar) : trim($request->title_en);
        $screen->description_en = $request->description_en ? trim($request->description_en) : null;
        $screen->description_ar = $request->description_ar ? trim($request->description_ar) : null;
        $screen->sort_order     = $request->sort_order ? (int)$request->sort_order : ((OnboardingScreen::max("sort_order") ?? 0) + 1);
        $screen->status         = $request->status ?? "active";
        $screen->is_active      = ($screen->status === "active");

        if ($request->hasFile("image")) {
            $image = $request->file("image");
            $filename = "onboarding_" . date("Ymd_His") . "_" . uniqid() . "." . $image->getClientOriginalExtension();
            $destinationPath = public_path("upload/onboarding_images");

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $image->move($destinationPath, $filename);
            $screen->image = $filename;
        }

        $screen->save();

        return response()->json([
            "status"  => true,
            "message" => "Onboarding screen created successfully",
            "data"    => $screen
        ], 201);
    }

    /**
     * Update onboarding screen via API.
     */
    public function update(Request $request, $id)
    {
        $screen = OnboardingScreen::find($id);

        if (!$screen) {
            return response()->json([
                "status"  => false,
                "message" => "Onboarding screen not found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            "title_en"       => "required|string|max:255",
            "title_ar"       => "nullable|string|max:255",
            "description_en" => "nullable|string|max:1000",
            "description_ar" => "nullable|string|max:1000",
            "sort_order"     => "nullable|integer|min:1",
            "status"         => "nullable|in:active,inactive",
            "image"          => "nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status"  => false,
                "message" => "Validation error",
                "errors"  => $validator->errors()
            ], 422);
        }

        $screen->title_en       = trim($request->title_en);
        $screen->title_ar       = $request->title_ar ? trim($request->title_ar) : trim($request->title_en);
        $screen->description_en = $request->description_en ? trim($request->description_en) : null;
        $screen->description_ar = $request->description_ar ? trim($request->description_ar) : null;
        if ($request->has("sort_order")) {
            $screen->sort_order = (int)$request->sort_order;
        }
        if ($request->has("status")) {
            $screen->status    = $request->status;
            $screen->is_active = ($request->status === "active");
        }

        if ($request->hasFile("image")) {
            if (!empty($screen->image)) {
                $oldPath = public_path("upload/onboarding_images/" . $screen->image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $image = $request->file("image");
            $filename = "onboarding_" . date("Ymd_His") . "_" . uniqid() . "." . $image->getClientOriginalExtension();
            $destinationPath = public_path("upload/onboarding_images");

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $image->move($destinationPath, $filename);
            $screen->image = $filename;
        }

        $screen->save();

        return response()->json([
            "status"  => true,
            "message" => "Onboarding screen updated successfully",
            "data"    => $screen
        ], 200);
    }

    /**
     * Delete onboarding screen via API.
     */
    public function destroy($id)
    {
        $screen = OnboardingScreen::find($id);

        if (!$screen) {
            return response()->json([
                "status"  => false,
                "message" => "Onboarding screen not found"
            ], 404);
        }

        if (!empty($screen->image)) {
            $imagePath = public_path("upload/onboarding_images/" . $screen->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $screen->delete();

        return response()->json([
            "status"  => true,
            "message" => "Onboarding screen deleted successfully"
        ], 200);
    }
}
