@foreach($driverTrucks as $key => $item)
<tr>
    <td class="text-center fw-bold">{{ $key + 1 }}</td>

    <!-- Driver Info -->
    <td>
        <div class="d-flex align-items-center gap-2">
            <img src="{{ (!empty($item->driver->photo) && file_exists(public_path('upload/user_images/'.$item->driver->photo))) ? url('upload/user_images/'.$item->driver->photo) : url('upload/no_image.jpg') }}" 
                 class="rounded-circle border border-info shadow-sm media-zoomable cursor-pointer" 
                 data-title="{{ $item->driver->fname ?? 'Driver' }} - Photo"
                 style="width: 40px; height: 40px; object-fit: cover;" 
                 alt="Driver Avatar">
            <div>
                <span class="d-block fw-bold text-white fs-6">
                    {{ $item->driver->fname ?? 'N/A' }} {{ $item->driver->lname ?? '' }}
                </span>
                <small class="text-info fs-8">
                    <i class="bx bx-phone me-1"></i>{{ $item->driver->phone ?? 'No Phone' }}
                </small>
            </div>
        </div>
    </td>

    <!-- Main Truck Type -->
    <td>
        @if($item->truckType)
            <div class="d-flex align-items-center gap-2">
                <img src="{{ (!empty($item->truckType->photo) && file_exists(public_path('upload/truck_images/'.$item->truckType->photo))) ? url('upload/truck_images/'.$item->truckType->photo) : url('upload/no_image.jpg') }}" 
                     class="rounded-2 border border-secondary media-zoomable cursor-pointer" 
                     data-title="{{ $item->truckType->name_en }} - Photo"
                     style="width: 35px; height: 35px; object-fit: cover;" 
                     alt="Truck Photo">
                <span class="fw-bold">{{ $item->truckType->name_en }}</span>
            </div>
        @else
            <span class="text-slate-400">N/A</span>
        @endif
    </td>

    <!-- Truck Sub-Type -->
    <td>
        @if($item->truckSubType)
            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold">
                <i class="bx bx-subdirectory-right me-1"></i> {{ $item->truckSubType->name_en }}
            </span>
        @else
            <span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold">
                General (No Sub-type)
            </span>
        @endif
    </td>

    <!-- Plate Number -->
    <td>
        @if($item->plate_number)
            <span class="badge bg-dark text-warning border border-warning border-opacity-50 px-3 py-1.5 rounded-3 fw-mono fs-7" style="font-family: monospace; letter-spacing: 1px;">
                <i class="bx bx-id-card me-1"></i> {{ $item->plate_number }}
            </span>
        @else
            <span class="text-slate-400 fs-7">Not Specified</span>
        @endif
    </td>

    <!-- Default Active Truck AJAX Select -->
    <td style="min-width: 165px;">
        <select class="form-select driver-truck-default-select default-badge-{{ $item->is_default }}" 
                data-item-id="{{ $item->id }}" 
                title="Toggle Active Default Truck">
            <option value="1" {{ $item->is_default == 1 ? 'selected' : '' }}>Default Truck</option>
            <option value="0" {{ $item->is_default == 0 ? 'selected' : '' }}>Secondary</option>
        </select>
    </td>

    <!-- Verification Status AJAX Select -->
    <td style="min-width: 165px;">
        <select class="form-select driver-truck-verified-select verified-badge-{{ $item->is_verified }}" 
                data-item-id="{{ $item->id }}" 
                title="Toggle Verification Status">
            <option value="1" {{ $item->is_verified == 1 ? 'selected' : '' }}>Verified</option>
            <option value="0" {{ $item->is_verified == 0 ? 'selected' : '' }}>Unverified</option>
        </select>
    </td>

    <!-- Actions -->
    <td class="text-center">
        <div class="d-flex align-items-center justify-content-center gap-2">
            <a href="{{ route('edit.driver.truck', $item->id) }}" 
               class="btn btn-sm btn-outline-info rounded-3 p-2 d-inline-flex align-items-center justify-content-center"
               title="Edit Assignment">
                <i class="bx bx-edit-alt fs-5"></i>
            </a>
            <a href="{{ route('delete.driver.truck', $item->id) }}" 
               class="btn btn-sm btn-outline-danger rounded-3 p-2 d-inline-flex align-items-center justify-content-center delete-driver-truck-btn"
               title="Delete Record">
                <i class="bx bx-trash fs-5"></i>
            </a>
        </div>
    </td>
</tr>
@endforeach
