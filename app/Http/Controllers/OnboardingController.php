<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OnboardingScreen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    /**
     * Display a listing of all onboarding slides.
     */
    public function AllOnboarding()
    {
        $onboardings = OnboardingScreen::ordered()->get();
        return view("admin.backend.onboarding.all_onboarding", compact("onboardings"));
    }

    /**
     * Show the form for creating a new onboarding slide.
     */
    public function AddOnboarding()
    {
        $nextOrder = (OnboardingScreen::max("sort_order") ?? 0) + 1;
        return view("admin.backend.onboarding.add_onboarding", compact("nextOrder"));
    }

    /**
     * Store a newly created onboarding slide in database.
     */
    public function StoreOnboarding(Request $request)
    {
        $request->validate([
            "title_en" => "required|string|max:255",
            "title_ar" => "nullable|string|max:255",
            "description_en" => "nullable|string|max:1000",
            "description_ar" => "nullable|string|max:1000",
            "sort_order" => "nullable|integer|min:1",
            "status" => "required|in:active,inactive",
            "image" => "nullable|file|mimes:jpeg,png,jpg,webp,svg|max:5120",
        ], [
            "title_en.required" => "Please enter the slide title in English.",
            "image.max" => "The onboarding image must not exceed 5MB.",
        ]);

        try {
            $onboarding = new OnboardingScreen();
            $onboarding->title_en = trim($request->title_en);
            $onboarding->title_ar = $request->title_ar ? trim($request->title_ar) : trim($request->title_en);
            $onboarding->description_en = $request->description_en ? trim($request->description_en) : null;
            $onboarding->description_ar = $request->description_ar ? trim($request->description_ar) : null;
            $onboarding->sort_order = $request->sort_order ? (int)$request->sort_order : ((OnboardingScreen::max("sort_order") ?? 0) + 1);
            $onboarding->status = $request->status ?? "active";
            $onboarding->is_active = ($request->status === "active");

            if ($request->hasFile("image")) {
                $image = $request->file("image");
                if (!$image->isValid()) {
                    return redirect()->back()->withInput()->with([
                        "message" => "Image upload failed: " . $image->getErrorMessage(),
                        "alert-type" => "error"
                    ]);
                }

                $destinationPath = public_path("upload/onboarding_images");
                if (!is_dir($destinationPath)) {
                    @mkdir($destinationPath, 0777, true);
                }

                $extension = $image->getClientOriginalExtension() ?: ($image->extension() ?: 'jpg');
                $filename = "onboarding_" . date("Ymd_His") . "_" . uniqid() . "." . $extension;

                $image->move($destinationPath, $filename);
                $onboarding->image = $filename;
            }

            $onboarding->save();

            $notification = [
                "message" => "Onboarding Slide Added Successfully!",
                "alert-type" => "success"
            ];

            return redirect()->route("all.onboarding")->with($notification);
        } catch (\Throwable $e) {
            Log::error("Onboarding store failed: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return redirect()->back()->withInput()->with([
                "message" => "Failed to save slide: " . $e->getMessage(),
                "alert-type" => "error"
            ]);
        }
    }

    /**
     * Show the form for editing the specified onboarding slide.
     */
    public function EditOnboarding($id)
    {
        $onboarding = OnboardingScreen::findOrFail($id);
        return view("admin.backend.onboarding.edit_onboarding", compact("onboarding"));
    }

    /**
     * Update the specified onboarding slide in database.
     */
    public function UpdateOnboarding(Request $request)
    {
        $id = $request->id;
        $onboarding = OnboardingScreen::findOrFail($id);

        $request->validate([
            "title_en" => "required|string|max:255",
            "title_ar" => "nullable|string|max:255",
            "description_en" => "nullable|string|max:1000",
            "description_ar" => "nullable|string|max:1000",
            "sort_order" => "nullable|integer|min:1",
            "status" => "required|in:active,inactive",
            "image" => "nullable|file|mimes:jpeg,png,jpg,webp,svg|max:5120",
        ], [
            "title_en.required" => "Please enter the slide title in English.",
            "image.max" => "The onboarding image must not exceed 5MB.",
        ]);

        try {
            $onboarding->title_en = trim($request->title_en);
            $onboarding->title_ar = $request->title_ar ? trim($request->title_ar) : trim($request->title_en);
            $onboarding->description_en = $request->description_en ? trim($request->description_en) : null;
            $onboarding->description_ar = $request->description_ar ? trim($request->description_ar) : null;
            $onboarding->sort_order = $request->sort_order ? (int)$request->sort_order : $onboarding->sort_order;
            $onboarding->status = $request->status ?? "active";
            $onboarding->is_active = ($request->status === "active");

            if ($request->hasFile("image")) {
                $image = $request->file("image");
                if (!$image->isValid()) {
                    return redirect()->back()->withInput()->with([
                        "message" => "Image upload failed: " . $image->getErrorMessage(),
                        "alert-type" => "error"
                    ]);
                }

                // Delete old image if exists
                if (!empty($onboarding->image)) {
                    $oldPath = public_path("upload/onboarding_images/" . $onboarding->image);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $destinationPath = public_path("upload/onboarding_images");
                if (!is_dir($destinationPath)) {
                    @mkdir($destinationPath, 0777, true);
                }

                $extension = $image->getClientOriginalExtension() ?: ($image->extension() ?: 'jpg');
                $filename = "onboarding_" . date("Ymd_His") . "_" . uniqid() . "." . $extension;

                $image->move($destinationPath, $filename);
                $onboarding->image = $filename;
            }

            $onboarding->save();

            $notification = [
                "message" => "Onboarding Slide Updated Successfully!",
                "alert-type" => "success"
            ];

            return redirect()->route("all.onboarding")->with($notification);
        } catch (\Throwable $e) {
            Log::error("Onboarding update failed: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return redirect()->back()->withInput()->with([
                "message" => "Failed to update slide: " . $e->getMessage(),
                "alert-type" => "error"
            ]);
        }
    }

    /**
     * Remove the specified onboarding slide from database and delete its image.
     */
    public function DeleteOnboarding($id)
    {
        try {
            $onboarding = OnboardingScreen::findOrFail($id);

            if (!empty($onboarding->image)) {
                $imagePath = public_path("upload/onboarding_images/" . $onboarding->image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }

            $onboarding->delete();

            $notification = [
                "message" => "Onboarding Slide Deleted Successfully!",
                "alert-type" => "success"
            ];

            return redirect()->back()->with($notification);
        } catch (\Throwable $e) {
            Log::error("Onboarding delete failed: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return redirect()->back()->with([
                "message" => "Failed to delete slide: " . $e->getMessage(),
                "alert-type" => "error"
            ]);
        }
    }

    /**
     * Change active/inactive status via instant AJAX call.
     */
    public function ChangeStatusAjax(Request $request)
    {
        $request->validate([
            "id" => "required|exists:onboarding_screens,id",
            "status" => "required|in:active,inactive"
        ]);

        $onboarding = OnboardingScreen::findOrFail($request->id);
        $onboarding->status = $request->status;
        $onboarding->is_active = ($request->status === "active");
        $onboarding->save();

        return response()->json([
            "success" => true,
            "message" => "Status updated successfully to " . ucfirst($request->status),
            "status" => $onboarding->status
        ]);
    }
}
