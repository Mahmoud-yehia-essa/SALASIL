@extends('admin.master_admin')
@section('admin')

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom border-secondary border-opacity-25 gap-3">
    <div>
        <h3 class="fw-bold header-title mb-1">
            <i class="bx bx-plus-circle text-info me-2"></i>Add New FAQ
        </h3>
        <p class="text-slate-400 mb-0" style="font-size: 0.95rem;">
            Create a new frequently asked question and answer for clients in the mobile app.
        </p>
    </div>
    <div>
        <a href="{{ route('all.faqs') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bx bx-arrow-back fs-5"></i>
            <span>Back to FAQs</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background-color: var(--card-bg, #1e293b);">
            <form method="POST" action="{{ route('store.faq') }}">
                @csrf

                <!-- Question -->
                <div class="mb-3">
                    <label class="form-label text-slate-300 fw-semibold">Question <span class="text-danger">*</span></label>
                    <input type="text" name="question" class="form-control rounded-3 @error('question') is-invalid @enderror" value="{{ old('question') }}" placeholder="e.g. How do I track my shipment?" required>
                    @error('question')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Category -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-slate-300 fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select rounded-3 @error('category') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-slate-300 fw-semibold">Sort Order</label>
                        <input type="number" name="order_sequence" class="form-control rounded-3" value="{{ old('order_sequence', $nextOrder) }}" min="0">
                        <small class="text-muted">Ascending display order in the app.</small>
                    </div>
                </div>

                <!-- Answer -->
                <div class="mb-4">
                    <label class="form-label text-slate-300 fw-semibold">Answer <span class="text-danger">*</span></label>
                    <textarea name="answer" rows="5" class="form-control rounded-3 @error('answer') is-invalid @enderror" placeholder="Write the detailed explanation..." required>{{ old('answer') }}</textarea>
                    @error('answer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status Switch -->
                <div class="mb-4">
                    <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                        <label class="form-check-label text-slate-300 fw-semibold order-1" for="status">Active (Visible in Mobile App)</label>
                        <input class="form-check-input m-0 order-0" type="checkbox" name="status" id="status" value="1" checked style="width: 2.8em; height: 1.5em; cursor: pointer;">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ route('all.faqs') }}" class="btn btn-secondary rounded-3 px-4 py-2">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow">
                        <i class="bx bx-check fs-5"></i>
                        <span>Save FAQ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
